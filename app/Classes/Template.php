<?php

namespace App\Classes;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Template
{
	private const LOGKEY = 'T--';

	private(set) string $file;
	private array $template;
	private(set) bool $invalid = FALSE;
	private(set) string $reason = '';
	private Collection $on_change_target;
	private Collection $on_change_attribute;
	private bool $on_change_processed = FALSE;

	public function __construct(string $file)
	{
		$td = Storage::disk(config('pla.template.dir'));
		$this->on_change_attribute = collect();
		$this->on_change_target = collect();

		$this->file = $file;

		try {
			// @todo Load in the proper attribute objects and objectclass objects
			// @todo Make sure we have a structural objectclass, or make the template invalid
			$this->template = json_decode($td->get($file),null,512,JSON_OBJECT_AS_ARRAY|JSON_THROW_ON_ERROR);

		} catch (\JsonException $e) {
			$this->invalid = TRUE;
			$this->reason = $e->getMessage();
		}
	}

	public function __get(string $key): mixed
	{
		return match ($key) {
			'attributes' => collect(Arr::get($this->template,$key))->keys(),
			'enabled' => Arr::get($this->template,$key,FALSE) && (! $this->invalid),
			'icon','regexp','title' => Arr::get($this->template,$key),
			'name' => Str::replaceEnd('.json','',$this->file),
			'objectclasses' => collect(Arr::get($this->template,$key)),
			'order' => collect(Arr::get($this->template,'attributes'))->map(fn($item)=>$item['order']),

			default => throw new \Exception('Unknown key: '.$key),
		};
	}

	public function __isset(string $key): bool
	{
		return array_key_exists($key,$this->template);
	}

	/**
	 * If the attribute has been marked as read-only
	 *
	 * @param string $attribute
	 * @return bool
	 */
	public function attributeReadOnly(string $attribute): bool
	{
		return ($x=Arr::get($this->template,'attributes.'.$attribute.'.readonly')) && $x;
	}

	/**
	 * Return the title we should use for an attribute
	 *
	 * @param string $attribute
	 * @return string|NULL
	 */
	public function attributeTitle(string $attribute): string|NULL
	{
		return Arr::get($this->template,'attributes.'.$attribute.'.display');
	}

	/**
	 * Return the onChange JavaScript for an attribute
	 *
	 * @param string $attribute
	 * @return Collection|NULL
	 */
	public function onChange(string $attribute): Collection|NULL
	{
		if (! $this->on_change_processed)
			$this->onChangeProcessing();

		return $this->on_change_attribute
			->get(strtolower($attribute));
	}

	/**
	 * Is this attribute's value populated by any onChange processing rules
	 *
	 * @param string $attribute
	 * @return bool
	 */
	public function onChangeAttribute(string $attribute): bool
	{
		if (! $this->on_change_processed)
			$this->onChangeProcessing();

		return $this->on_change_attribute
			->has(strtolower($attribute));
	}

	/**
	 * Process the attributes for onChange JavaScript
	 */
	/**
	 * Return the onchange JavaScript for attribute
	 *
	 * @return Collection
	 */
	private function onChangeProcessing(): void
	{
		foreach (Arr::get($this->template,'attributes',[]) as $attribute => $detail) {
			$result = collect();

			foreach (Arr::get($detail,'onchange',[]) as $item) {
				list($command,$args) = preg_split('/^=([a-zA-Z]+)\((.+)\)$/',$item,-1,PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);

				switch ($command) {
					case 'autoFill':
						$result->push($this->autofill($args));
						break;
				}
			}

			if ($result->count())
				$this->on_change_attribute->put(strtolower($attribute),$result);
		}

		$this->on_change_processed = TRUE;
	}

	/**
	 * Is this attribute's value populated by any onChange processing rules
	 *
	 * @param string $attribute
	 * @return bool
	 */
	public function onChangeTarget(string $attribute): bool
	{
		if (! $this->on_change_processed)
			$this->onChangeProcessing();

		return $this->on_change_target
			->has(strtolower($attribute));
	}

	/**
	 * autoFill - javascript to have one attribute fill the value of another
	 *
	 * args: is a literal string, with two parts, delimited by a semi-colon ;
	 *	+ The first part is the attribute that will be populated
	 *	+ The second part may contain many fields like %attr|start-end/flags|additionalcontrolchar%
	 *	  to substitute values read from other fields.
	 *	  + |start-end is optional, but must be present if the k flag is used
	 *	  + /flags is optional
	 *	  + |additionalcontrolchar is optional, and specific to a flag being used
	 *
	 *	+ flags may be:
	 *		T:(?)	Read display text from selection item (drop-down list), otherwise, read the value of the field
	 *			For fields that aren't selection items, /T shouldn't be used, and the field value will always be read
	 *		k:(?)	Tokenize:
	 *			If the "k" flag is not given:
	 *			+ A |start-end instruction will perform a sub-string operation upon the value of the attr, passing
	 *				character positions start-end through
	 *				+ start can be 0 for first character, or any other integer
	 *				+ end can be 0 for last character, or any other integer for a specific position
	 *			If the "k" flag is given:
	 *				+ The string read will be split into fields, using : as a delimiter
	 *				+ start indicates which field number to pass through
	 *
	 *		    If additionalcontrolchar is given, it will be used as delimiter (e.g. this allows for splitting
	 *			e-mail addresses into domain and domain-local part)
	 *		l:	Make the result lower case
	 *		U:	Make the result upper case
	 *		A:(?)	Remap special characters to their corresponding ASCII value
	 *
	 * @note Attributes rendered on the page are lowercase, eg: <attribute id="gidnumber"> for gidNumber
	 * @note JavaScript generated here depends on js/template.js
	 * (?) = to test
	 */
	private function autofill(string $arg): string
	{
		if (! preg_match('/;/',$arg)) {
			Log::alert(sprintf('%s:Invalid argument given to autofill [%s]',self::LOGKEY,$arg));
			return '';
		}

		$result = '';

		// $attr has our attribute to update, $string is the format to use when updating it
		list($attr,$string) = preg_split('(([^,]+);(.*))',$arg,-1,PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
		$this->on_change_target->put(strtolower($attr),$string);

		$output = $string;
		//$result .= sprintf("\n// %s\n",$arg);

		$m = [];
		// MATCH : 0 = highlevel match, 1 = attr, 2 = subst, 3 = mod, 4 = delimiter
		preg_match_all('/%(\w+)(?:\|([0-9]*-[0-9])+)?(?:\/([klTUA]+))?(?:\|(.)?)?%/U',$string,$m);

		foreach ($m[0] as $index => $null) {
			$match_attr = strtolower($m[1][$index]);
			$match_subst = $m[2][$index];
			$match_mod = $m[3][$index];
			$match_delim = $m[4][$index];

			$substrarray = [];

			$result .= sprintf("var %s;\n",$match_attr);

			if (str_contains($match_mod,'k')) {
				preg_match_all('/([0-9]+)/',trim($match_subst),$substrarray);

				$delimiter = ($match_delim === '') ? ' ' : preg_quote($match_delim);
				$result .= sprintf("   %s = %s.split('%s')[%s];\n",$match_attr,$match_attr,$delimiter,$substrarray[1][0] ?? '0');

			} else {
				// Work out the start and end chars needed from this value if we have a range specifier
				preg_match_all('/([0-9]*)-([0-9]+)/',$match_subst,$substrarray);
				if ((isset($substrarray[1][0]) && $substrarray[1][0]) || (isset($substrarray[2][0]) && $substrarray[2][0])) {
					$result .= sprintf("%s = get_attribute('%s',%d,%s);\n",
						$match_attr,$match_attr,
						$substrarray[1][0] ?? '0',
						$substrarray[2][0] ?: sprintf('%s.length',$match_attr));
				} else {
					$result .= sprintf("%s = get_attribute('%s');\n",$match_attr,$match_attr);
				}
			}

			if (str_contains($match_mod,'l'))
				$result .= sprintf("%s = %s.toLowerCase();\n",$match_attr,$match_attr);

			if (str_contains($match_mod,'U'))
				$result .= sprintf("%s = %s.toUpperCase();\n",$match_attr,$match_attr);

			if (str_contains($match_mod,'A'))
				$result .= sprintf("%s = toAscii(%s);\n",$match_attr,$match_attr);

			// For debugging
			//$result .= sprintf("console.log('%s will return:'+%s);\n",$match_attr,$match_attr);

			// Reformat out output into JS variables
			$output = preg_replace('/'.preg_quote($m[0][$index],'/').'/','\'+'.$match_attr.'+\'',$output);
		}

		$result .= sprintf("put_attribute('%s','%s');\n",strtolower($attr),$output);
		$result .= "\n";

		return $result;
	}
}