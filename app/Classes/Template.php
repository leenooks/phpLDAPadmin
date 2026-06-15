<?php

namespace App\Classes;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use App\Ldap\Entry;

class Template
{
	private const LOGKEY = 'T--';
	private const LOCK_TIME = 600;
	/**
	 * Template value regex, used to determine LDAP values that are transformed before being used or returned
	 * + Arg 1 - Attribute Name, eg: firstName
	 * + Arg 2 - Char delimiter, eg: |n (return first n chars), |n- (return chars from n to end), |n-m (only return chars to n-m)
	 * + Arg 3 - Slash Option, eg: l (lower case), U (upper case),
	 * + Arg 4 - Delimiter char, used to tokenise values
	 */
	private const VALUE_REGEX = '/%(\w+)(?:\|((?:\d+-)?\d*))?(?:\/([klTCUA]+))?(?:\|(.)?)?%/U';

	private(set) string $file;
	private Collection $template;
	private ?string $container = NULL;
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
			$this->template = collect(json_decode($td->get($file),null,512,JSON_OBJECT_AS_ARRAY|JSON_THROW_ON_ERROR));

			// Test all objectclasses exist, and one is structural
			if ($this->template->has('objectclasses')) {
				$havestructural = FALSE;
				$ocs = collect($this->template->get('objectclasses'));

				foreach (clone $ocs as $key => $oc) {
					$soc = config('server')->schema('objectclasses',$oc);

					if (! $soc) {
						Log::alert(sprintf('%s:Ignoring objectclass [%s] in template [%s], the server doesnt know about it',self::LOGKEY,$oc,$this->file));

						$ocs->forget($key);
						continue;
					}

					if ($soc->isStructural())
						$havestructural = TRUE;
				}

				$this->template->put('objectclasses',$ocs);

				if (! $havestructural) {
					Log::alert(sprintf('%s:Invalidating template [%s], the no structurual objectclasses defined',self::LOGKEY,$this->file));

					$this->invalid = TRUE;
					$this->reason = 'No STRUCTURAL objectclass';
				}

			} else {
				Log::alert(sprintf('%s:Invalidating template [%s], the no objectclasses defined',self::LOGKEY,$this->file));

				$this->invalid = TRUE;
				$this->reason = 'No objectclasses';
			}

			// Also test the regex is valid.
			if ((! $this->invalid) && $this->template->has('regexp'))
				preg_match($this->regexp,'');

		} catch (\ErrorException|\JsonException $e) {
			Log::alert(sprintf('%s:Invalidating template [%s], an error was thrown with message [%s]',self::LOGKEY,$this->file,$e->getMessage()));

			$this->invalid = TRUE;
			$this->reason = $e->getMessage();
		}
	}

	public function __get(string $key): mixed
	{
		return match ($key) {
			'attributes','objectclasses' => collect($this->template->get($key)),
			'enabled' => $this->template->get($key,FALSE) && (! $this->invalid),
			'icon','rdn','regexp','title' => $this->template->get($key),
			'name' => Str::replaceEnd('.json','',$this->file),
			'order' => $this->attributes->flatMap(fn($item,$key)=>[strtolower($key)=>Arr::get($item,'order')]),

			default => throw new \Exception('Unknown key: '.$key),
		};
	}

	public function __set(string $key,mixed $value): void
	{
		switch ($key) {
			case 'container':
				$this->container = $value;
				break;

			default:
				throw new \Exception('Unknown key: '.$key);
		}
	}

	public function __isset(string $key): bool
	{
		return $this->template->has($key);
	}

	/**
	 * Return the configuration for an attribute
	 *
	 * @param string $attribute
	 * @return array|NULL
	 */
	public function attribute(string $attribute): Collection|NULL
	{
		$key = $this->attributes
			->search(fn($item,$key)=>! strcasecmp($key,$attribute));

		return collect($this->attributes->get($key));
	}

	/**
	 * Return a template attributes select options
	 *
	 * @param string $attribute
	 * @return Collection|NULL
	 */
	public function attributeOptions(string $attribute,mixed $value=NULL): Collection|NULL
	{
		if ($x=$this->attribute($attribute)?->get('options')) {
			if (is_array($x))
				return collect($x)->map(fn($item,$key)=>['id'=>$key,'value'=>$item])->when($value,fn($item)=>$item->prepend(['id'=>$value,'value'=>$value]));

			// Check if this is a pick list
			elseif (preg_match('/^=([a-zA-Z]+)\((.+)\)$/',$x)) {
				list($command,$args) = preg_split('/^=([a-zA-Z]+)\((.+)\)$/',$x,-1,PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);

				return match ($command) {
					'getSelectList' => $this->getSelectList($args)->when($value,fn($item)=>$item->prepend(['id'=>$value,'value'=>$value])),
					default => NULL,
				};
			}
		}

		return NULL;
	}

	/**
	 * If the attribute has been marked as read-only
	 *
	 * @param string $attribute
	 * @return bool
	 */
	public function attributeReadOnly(string $attribute): bool
	{
		return ($x=$this->attribute($attribute)?->get('readonly')) && $x;
	}

	/**
	 * Return the title we should use for an attribute
	 *
	 * @param string $attribute
	 * @return string|NULL
	 */
	public function attributeTitle(string $attribute): string|NULL
	{
		return $this->attribute($attribute)?->get('display');
	}

	/**
	 * Return the title we should use for an attribute
	 *
	 * @param string $attribute
	 * @return string|NULL
	 */
	public function attributeType(string $attribute): string|NULL
	{
		return $this->attribute($attribute)?->get('type');
	}

	public function attributeValue(string $attribute): string|NULL
	{
		if ($x=$this->attribute($attribute)->get('value')) {
			if (preg_match('/^=([a-zA-Z]+)\((.+)\)$/',$x)) {
				list($command,$args) = preg_split('/^=([a-zA-Z]+)\((.+)\)$/',$x,-1,PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);

			} else {
				$command = 'value';
				$args = $x;
			}

			return match ($command) {
				'getNextNumber' => $this->getNextNumber($args),
				'value' => $x,
				default => NULL,
			};
		}

		return NULL;
	}

	/**
	 * Get next number for an attribute
	 *
	 * As part of getting the next number, we'll use a lock to avoid any potential clashes. The lock is obtained by
	 * two lock files:
	 * a: Read a session lock (our session id), use that number if it exists, otherwise,
	 * b: Query the ldap server for the attribute, sort by number
	 * c: Read a system lock, if it exists, and use that as our start base (otherwise use a config() base)
	 * d: Starting at base, find the next free number
	 * e: When number identified, put it in the system lock with our session id
	 * f: Put the number in our session lock, with a timeout
	 * g: Read the system lock, make sure our session id is still in it, if not, go to (d) with our number as the base
	 * h: Remove our session id from the system lock (our number is unique)
	 *
	 * When using the number to create an entry:
	 * + Read our session lock, confirm the number is still in it, if not fail validation and bounce back
	 * + Create the entry
	 * + Delete our session lock
	 *
	 * @param string $arg Semicolon delimited with 2 values start;attr, where:
	 * 					  + start which branch of the LDAP tree we start looking for attr values
	 * 					  + attr is the LDAP attribute we are looking at
	 * @return int|NULL
	 */
	private function getNextNumber(string $arg): int|NULL
	{
		if (! preg_match('/;/',$arg)) {
			Log::alert(sprintf('%s:Invalid argument given to getNextNumber [%s]',self::LOGKEY,$arg));
			return NULL;
		}

		list($start,$attr) = preg_split('(([^,]+);(\w+))',$arg,-1,PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
		$attr = strtolower($attr);

		// If we recently got a number, return it
		if ($number=Cache::get($attr.':'.Session::id()))
			return $number;

		$cache = Cache::get($attr.':system');
		Log::debug(sprintf('%s:System Cache has',self::LOGKEY),['cache'=>$cache]);

		if (! Arr::get($cache,'number'))
			$number = config('pla.template.getnextnumber.'.$attr,0);
		else
			$number = Arr::get($cache,'number')+1;

		Log::debug(sprintf('%s:Starting with [%d] for [%s]',self::LOGKEY,$number,$attr));

		$o = config('server');
		$bases = ($start === '/') ? $o->baseDNs() : collect($start);

		$result = collect();
		$complete = [];

		do {
			$sizelimit = FALSE;

			// Get the current numbers
			foreach ($bases as $base) {
				if (Arr::get($complete,$dn=$base->getDN()))
					continue;

				$query = Entry::query()
					->setDN($base)
					->select([$attr])
					->where($attr,'*')
					->notFilter(fn($q)=>$q->where($attr,'<=',$number-1));

				if ($result->count())
					$query->notFilter(fn($q)=>$q->where($attr,'>=',$result->min()));

				$result = $result->merge(($x=$query
					->search()
					->orderBy($attr)
					->get())
					->pluck($attr)
					->flatten());

				// If we hit a sizelimit on this run
				$base_sizelimit = $query->getModel()->hasMore();
				Log::debug(sprintf('%s:Query in [%s] returned [%d] entries and has more [%s]',self::LOGKEY,$base,$x->count(),$base_sizelimit ? 'TRUE' : 'FALSE'));

				if (! $base_sizelimit)
					$complete[$dn] = TRUE;
				else
					Log::info(sprintf('%s:Size Limit alert for [%s]',self::LOGKEY,$dn));

				$sizelimit = $sizelimit || $base_sizelimit;
			}

			$result = $result
				->sort()
				->unique();

			Log::debug(sprintf('%s:Result has [%s]',self::LOGKEY,$result->join('|')));

			if ($result->count())
				foreach ($result as $item) {
					Log::debug(sprintf('%s:Checking [%d] against [%s]',self::LOGKEY,$number,$item));
					if ($number < $item)
						break;

					$number += 1;
				}

			else
				$number += 1;

			// Remove redundant entries
			$result = $result->filter(fn($item)=>$item>$number);

			if ($sizelimit)
				Log::debug(sprintf('%s:We got a sizelimit.',self::LOGKEY),['number'=>$number,'result_min'=>$result->min(),'result_count'=>$result->count()]);

			/*
			 * @todo This might need some additional work:
			 * EG: if sizelimit is 5
			 * if result has 1,2,3,4,20 [size limit]
			 * we re-enquire (4=>20) and get 7,8,9,10,11 [size limit]
			 * we re-enquire (4=>7) and get 5,6 [no size limit]
			 * we calculate 12, and accept it because no size limit, but we didnt test for 12
			 */
		} while ($sizelimit);

		// We found our number
		Log::debug(sprintf('%s:Autovalue for Attribute [%s] in Session [%s] Storing [%d]',self::LOGKEY,$attr,Session::id(),$number));
		Cache::put($attr.':system',['number'=>$number,'session'=>Session::id(),self::LOCK_TIME*2]);
		Cache::put($attr.':'.Session::id(),$number,self::LOCK_TIME);
		sleep(1);

		// If the session still has our session ID, then our number is ours
		return (Arr::get(Cache::get($attr.':system'),'session') === Session::id())
			? $number
			: NULL;
	}

	/**
	 * Search the directory and return a list of options, based on existing entries
	 *
	 * @param string $arg Semicolon delimited with 9 arguments:
	 *  Mandatory Arguments:
	 *  * arg 0
	 *    - container, to query from current position
	 *    - "/",".",".." => get container parent as usual
	 *
	 *  * arg 1
	 *    - LDAP filter. May include '%attr%', it will be expanded.
	 *
	 *  * arg2
	 *    - list attribute key
	 *
	 *  Optional Arguments:
	 *  * arg 3
	 *    - select display (plus modifier /C: Capitalize, /l to convert to lowercase, |{1,x} to cut chars)
	 *    - replaced by %arg 2% if not given
	 *
	 *  * arg 4
	 *    - the value furnished in output - must include attribute id. replaced by arg 2 if not given
	 *
	 *  * arg 5 (for MultiList)
	 *    - size of displayed list (default: 10 lines)
	 *
	 * Example:
	 * + getSelectList(/;(objectClass=posixAccount);uid)
	 *   Generate a list of all uid's
	 *
	 * + getSelectList(/;(&(objectClass=posixAccount)(uid=groupA*));uid;%cn/U% (%gidNumber%);%cn/U% (%gidNumber%);
	 *   Find all uids that start with groupA,
	 *   present a list of keys with an uppercase common name and gidnumber in parentheses
	 *   with values of an uppercase common name and gidnumber in parentheses
	 *
	 * @return Collection|NULL
	 *
	 * Examples:
	 * + PickList(/;(&(objectClass=posixGroup));gidNumber;%git%)
	 * + PickList(/;(&(objectClass=posixAccount));loginShell;%loginShell%)
	 */
	private function getSelectList(string $arg): Collection|NULL
	{
		if (! preg_match('/;/',$arg)) {
			Log::alert(sprintf('%s:! Invalid argument given to getSelectList [%s]',self::LOGKEY,$arg));

			return NULL;
		}

		$args = explode(';',$arg);
		if ((count($args) < 3) || (count($args) > 8)) {
			Log::alert(sprintf('%s:! Invalid number of arguments given to getSelectList [%d]',self::LOGKEY,count($args)));

			return NULL;
		}

		// Default to search the whole directory if no container is provided
		$container = match ($x=Arr::get($args,0,$this->container)) {
			'/' => config('server')->baseDNs(FALSE),
			'.' => [$this->container],
			'..' => [config('server')->parent($this->container)],
			default => [$x],
		};

		// Process filter (arg 1), eventually replace %attr% by its value set in a previous page.
		$m = [];
		$query = Arr::get($args,1);
		preg_match_all(self::VALUE_REGEX,$query,$m);

		if (Arr::get($m,1))
			foreach ($m[1] as $arg) {
				$value = Arr::get($this->template->get('attributes'),$arg.'.value');

				// $m[2] is the chars limiter
				foreach (Arr::get($m,2,[]) as $arg)
					$value = $this->substring($value,$arg);

				// $m[3] is the char modifier
				foreach (Arr::get($m,3,[]) as $arg)
					$value = $this->modstring($value,$arg);

				$query = preg_replace('#'.preg_quote($m[0][0]).'#',$value,$query);
			}

		$result = collect();

		// Display format
		$displayargs = [];
		$display = empty($args[3]) ? "%{$args[2]}%" : $args[3];
		preg_match_all(self::VALUE_REGEX,$display,$displayargs);

		// Value format
		$valueargs = [];
		$value = empty($args[4]) ? "%{$args[3]}%" : $args[4];
		preg_match_all(self::VALUE_REGEX,$value,$valueargs);

		// Search for values
		foreach ($container as $base) {
			$search = (new Entry)
				->in($base)
				->rawFilter($query);

			$result = $result->merge($search->get(collect($displayargs[1])->merge($valueargs[1])->unique()->toArray()));
		}

		// Process the result
		return $result
			->flatMap(function($item) use ($args,$display,$displayargs,$value,$valueargs) {
				// Go through each display argument and resolve it
				foreach ($displayargs[1] as $k=>$arg) {
					if (! ($x=$item->getFirstAttribute($arg)))
						return NULL;

					// $displayargs[2] is the chars limiter
					if ($displayargs[2][$k])
						$x = $this->substring($x,$displayargs[2][$k]);

					// $displayargs[3] is the char modifier
					if ($displayargs[3][$k])
						$x = $this->modstring($x,$displayargs[3][$k]);

					$display = preg_replace('#'.preg_quote($displayargs[0][$k]).'#',$x,$display);
				}

				// Go through each display argument and resolve it
				foreach ($valueargs[1] as $k=>$arg) {
					if (! ($x=$item->getFirstAttribute($arg)))
						continue;

					// $displayargs[2] is the chars limiter
					if ($valueargs[2][$k])
						$x = $this->substring($x,$valueargs[2][$k]);

					// $displayargs[3] is the char modifier
					if ($valueargs[3][$k])
						$x = $this->modstring($x,$valueargs[3][$k]);

					$value = preg_replace('#'.preg_quote($valueargs[0][$k]).'#',$x,$value);
				}

				return [$display=>['id'=>$display,'value'=>$value]];
			})
			->filter()
			->unique()
			->sortKeys();
	}

	/**
	 * Return if an attribute is automatically calculated
	 *
	 * @param $attribute
	 * @return bool
	 */
	public function isAttributeCalculated($attribute): bool
	{
		return preg_match('/^=([a-zA-Z]+)/',$this->attribute($attribute)->get('value'));
	}

	/**
	 * Return a modified string
	 *
	 * @param string $string
	 * @param string $mod
	 * @return string
	 */
	private function modstring(string $string,string $mod): string
	{
		return match ($mod) {
			'l' => mb_strtolower($string),
			'U' => mb_strtoupper($string),
			'C' => mb_ucfirst($string),

			default => $string,
		};
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
	 * Return the onchange JavaScript for attribute
	 *
	 * @return void
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
	 * Handle returning a substring
	 *
	 * @param string $string Raw string to process
	 * @param string $limit Which chars to return
	 * @return string
	 */
	private function substring(string $string,string $limit): string
	{
		// If limit is n-, we'll return the remaining chars starting from offset n
		if (Str::endsWith($limit,'-'))
			return Str::substr($string,substr($limit,0,-1));

		// Otherwise return the chars between n-m, or the first n chars.
		$m = [];
		preg_match_all('/(\d+)-?(\d+)?/',$limit,$m);

		return $m[2][0]
			? Str::substr($string,$m[1][0],$m[2][0]-$m[1][0])
			: Str::substr($string,0,$m[1][0]);
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

		$m = [];
		preg_match_all(self::VALUE_REGEX,$string,$m);

		foreach ($m[0] as $index => $null) {
			$match_attr = strtolower($m[1][$index]);
			$match_subst = $m[2][$index];
			$match_mod = $m[3][$index];
			$match_delim = $m[4][$index];

			$substrarray = [];

			$result .= sprintf("var %s;\n",$match_attr);

			if (str_contains($match_mod,'k')) {
				preg_match_all('/(\d+)/',trim($match_subst),$substrarray);

				$delimiter = ($match_delim === '') ? ' ' : preg_quote($match_delim);
				$result .= sprintf("%s = get_attribute('%s');\n",$match_attr,$match_attr);
				$result .= sprintf("   %s = %s.split('%s')[%s];\n",$match_attr,$match_attr,$delimiter,$substrarray[1][0] ?? '0');

			} else {
				// Work out the start and end chars needed from this value if we have a range specifier
				preg_match_all('/(\d*)-(\d+)/',$match_subst,$substrarray);
				if ((isset($substrarray[1][0]) && $substrarray[1][0]) || (isset($substrarray[2][0]) && $substrarray[2][0])) {
					$result .= sprintf("%s = get_attribute('%s',%d,%s);\n",
						$match_attr,$match_attr,
						$substrarray[1][0] ?? '0',
						$substrarray[2][0] ?: sprintf('%s.length',$match_attr));
				} else {
					$result .= sprintf("%s = get_attribute('%s');\n",$match_attr,$match_attr);
				}

				$result .= sprintf("if (%s === undefined) %s = '';",$match_attr,$match_attr);
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