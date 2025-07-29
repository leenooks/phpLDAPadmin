<?php

namespace App\Classes\LDAP\Schema;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

use App\Classes\LDAP\Attribute;
use App\Exceptions\InvalidUsage;
use App\Ldap\Entry;

/**
 * Represents an LDAP AttributeType
 */
final class AttributeType extends Base
{
	private const LOGKEY = 'SAT';

	// An array of AttributeTypes which inherit from this one
	private(set) Collection $children;

	// The equality rule used
	private(set) ?string $equality = NULL;

	// This attribute has been forced a MAY attribute by the configuration.
	private(set) bool $forced_as_may = FALSE;

	// boolean: is collective?
	private(set) bool $is_collective = FALSE;

	// Is this a must attribute
	private(set) bool $is_must = FALSE;

	// boolean: can use modify?
	private(set) bool $is_no_user_modification = FALSE;

	// boolean: is single valued only?
	private(set) bool $is_single_value = FALSE;

	// The max number of characters this attribute can be
	private(set) ?int $max_length = NULL;

	// An array of names (including aliases) that this attribute is known by
	private(set) Collection $names;

	// The ordering of the attributeType
	private(set) ?string $ordering = NULL;

	// A list of object class names that require this attribute type.
	private(set) Collection $required_by_object_classes;

	// Which objectclass is defining this attribute for an Entry
	public ?string $source = NULL;

	// Supports substring matching?
	private(set) ?string $sub_str_rule = NULL;

	// The attribute from which this attribute inherits (if any)
	private(set) ?string $sup_attribute = NULL;

	// The full syntax string, ie 1.2.3.4{16}
	private(set) ?string $syntax = NULL;
	private(set) ?string $syntax_oid = NULL;

	// The usage string set by the LDAP schema
	private(set) ?string $usage = NULL;

	// An array of objectClasses which use this attributeType (must be set by caller)
	private(set) Collection $used_in_object_classes;

	public function __get(string $key): mixed
	{
		return match ($key) {
			'names_lc' => $this->names->map('strtolower'),
			default => parent::__get($key)
		};
	}

	/**
	 * Children of this attribute type that inherit from this one
	 *
	 * @param string $child
	 * @return void
	 */
	public function addChild(string $child): void
	{
		$this->children
			->push($child);
	}

	/**
	 * Adds an objectClass name to this attribute's list of "required by" objectClasses,
	 * that is the list of objectClasses which must have this attribute.
	 *
	 * @param string $name The name of the objectClass to add.
	 * @param bool $structural
	 */
	public function addRequiredByObjectClass(string $name,bool $structural): void
	{
		if (! $this->required_by_object_classes->has($name))
			$this->required_by_object_classes->put($name,$structural);
	}

	/**
	 * Adds an objectClass name to this attribute's list of "used in" objectClasses,
	 * that is the list of objectClasses which provide this attribute.
	 *
	 * @param string $name The name of the objectClass to add.
	 * @param bool $structural
	 */
	public function addUsedInObjectClass(string $name,bool $structural): void
	{
		if (! $this->used_in_object_classes->has($name))
			$this->used_in_object_classes->put($name,$structural);
	}

	private function factory(): Attribute
	{
		return Attribute\Factory::create(
			dn: '',
			attribute: $this->name);
	}

	/**
	 * For a list of object classes return all parent object classes as well
	 *
	 * @param Collection $ocs
	 * @return Collection
	 */
	private function heirachy(Collection $ocs): Collection
	{
		$result = collect();

		foreach ($ocs as $oc) {
			$item = config('server')
				->schema('objectclasses',$oc);

			$result = $result
				->merge($item
					->getParents(TRUE)
					->pluck('name'))
				->push($item->name);
		}

		return $result;
	}

	/**
	 * Creates a new AttributeType object from a raw LDAP AttributeType string.
	 *
	 * eg: ( 2.5.4.0 NAME 'objectClass' DESC 'RFC4512: object classes of the entity' EQUALITY objectIdentifierMatch SYNTAX 1.3.6.1.4.1.1466.115.121.1.38 )
	 */
	protected function parse(string $line): void
	{
		Log::debug(sprintf('%s:Parsing AttributeType [%s]',self::LOGKEY,$line));

		// Init
		$this->names = collect();
		$this->children = collect();
		$this->used_in_object_classes = collect();
		$this->required_by_object_classes = collect();

		parent::parse($line);
	}

	protected function parse_chunk(array $strings,int &$i): void
	{
		switch ($strings[$i]) {
			case 'NAME':
				$name = '';

				// @note Some schema's return a (' instead of a ( '
				// @note This attribute format has no aliases
				if ($strings[$i+1] !== '(' && ! preg_match('/^\(/',$strings[$i+1])) {
					do {
						$name .= ($name ? ' ' : '').$strings[++$i];

					} while (! preg_match("/\'$/s",$strings[$i]));

				} else {
					$i++;

					do {
						// In case we came here because of a ('
						if (preg_match('/^\(/',$strings[$i]))
							$strings[$i] = preg_replace('/^\(/','',$strings[$i]);
						else
							$i++;

						$name .= ($name ? ' ' : '').$strings[++$i];

					} while (! preg_match("/\'$/s",$strings[$i]));

					// Add alias names for this attribute
					while ($strings[++$i] !== ')') {
						$alias = preg_replace("/^\'(.*)\'$/",'$1',$strings[$i]);
						$this->names->push($alias);
					}
				}

				$this->names = $this->names->push(preg_replace("/^\'(.*)\'$/",'$1',$name))->sort();
				$this->forced_as_may = $this->names_lc
					->intersect(array_map('strtolower',config('pla.force_may',[])))
					->count() > 0;

				if (static::DEBUG_VERBOSE)
					Log::debug(sprintf('%s:- Case NAME returned (%s)',self::LOGKEY,$this->name),['names'=>$this->names]);
				break;

			case 'SUP':
				$this->sup_attribute = preg_replace("/^\'(.*)\'$/",'$1',$strings[++$i]);

				if (static::DEBUG_VERBOSE)
					Log::debug(sprintf('%s:- Case SUP returned (%s)',self::LOGKEY,$this->sup_attribute));
				break;

			case 'EQUALITY':
				$this->equality = $strings[++$i];

				if (static::DEBUG_VERBOSE)
					Log::debug(sprintf('%s:- Case EQUALITY returned (%s)',self::LOGKEY,$this->equality));
				break;

			case 'ORDERING':
				$this->ordering = $strings[++$i];

				if (static::DEBUG_VERBOSE)
					Log::debug(sprintf('%s:- Case ORDERING returned (%s)',self::LOGKEY,$this->ordering));
				break;

			case 'SUBSTR':
				$this->sub_str_rule = $strings[++$i];

				if (static::DEBUG_VERBOSE)
					Log::debug(sprintf('%s:- Case SUBSTR returned (%s)',self::LOGKEY,$this->sub_str_rule));
				break;

			case 'SYNTAX':
				$this->syntax = preg_replace("/^\'(.*)\'$/",'$1',$strings[++$i]);
				$this->syntax_oid = preg_replace("/^\'?(.*){\d+}\'?$/",'$1',$this->syntax);

				if (static::DEBUG_VERBOSE)
					Log::debug(sprintf('%s:/ Evaluating SYNTAX returned (%s) [%s]',self::LOGKEY,$this->syntax,$this->syntax_oid));

				// Does this SYNTAX string specify a max length (ie, 1.2.3.4{16})
				$m = [];
				$this->max_length = preg_match('/{(\d+)}$/',$this->syntax,$m)
					? $m[1]
					: NULL;

				if (static::DEBUG_VERBOSE)
					Log::debug(sprintf('%s:- Case SYNTAX returned (%s) [%s] {%d}',self::LOGKEY,$this->syntax,$this->syntax_oid,$this->max_length));
				break;

			case 'SINGLE-VALUE':
				$this->is_single_value = TRUE;

				if (static::DEBUG_VERBOSE)
					Log::debug(sprintf('%s:- Case SINGLE-VALUE returned (%s)',self::LOGKEY,$this->is_single_value));
				break;

			case 'COLLECTIVE':
				$this->is_collective = TRUE;

				if (static::DEBUG_VERBOSE)
					Log::debug(sprintf('%s:- Case COLLECTIVE returned (%s)',self::LOGKEY,$this->is_collective));
				break;

			case 'NO-USER-MODIFICATION':
				$this->is_no_user_modification = TRUE;

				if (static::DEBUG_VERBOSE)
					Log::debug(sprintf('%s:- Case NO-USER-MODIFICATION returned (%s)',self::LOGKEY,$this->is_no_user_modification));
				break;

			case 'USAGE':
				$this->usage = $strings[++$i];

				if (static::DEBUG_VERBOSE)
					Log::debug(sprintf('%s:- Case USAGE returned (%s)',self::LOGKEY,$this->usage));
				break;

			default:
				parent::parse_chunk($strings,$i);
		}
	}

	/**
	 * Sets whether this attribute is single-valued.
	 *
	 * @param boolean $is
	 */
	public function setIsSingleValue(bool $is): void
	{
		$this->is_single_value = $is;
	}

	/**
	 * If this is a MUST attribute to the objectclass that defines it
	 *
	 * @return void
	 */
	public function setMust(): void
	{
		$this->is_must = TRUE;
	}

	/**
	 * Sets this attribute's name.
	 *
	 * @param string $name The new name to give this attribute.
	 * @throws InvalidUsage
	 */
	public function setName(string $name): void
	{
		// Quick validation
		if ($this->names_lc->count() && (! $this->names_lc->contains(strtolower($name))))
			throw new InvalidUsage(sprintf('Cannot set attribute name to [%s], its not an alias for [%s]',$name,$this->names->join(',')));

		$this->name = $name;
	}

	/**
	 * Return Request validation array
	 *
	 * This will merge configured validation with schema required attributes
	 *
	 * @param array $array
	 * @return array|null
	 */
	public function validation(array $array): ?array
	{
		// For each item in array, we need to get the OC hierarchy
		$heirachy = $this->heirachy(collect($array)
			->flatten()
			->filter());

		// Get any config validation
		$validation = collect(Arr::get(config('ldap.validation'),$this->name_lc,[]));

		$nolangtag = sprintf('%s.%s.0',$this->name_lc,Entry::TAG_NOTAG);

		// Add in schema required by conditions
		if (($heirachy->intersect($this->required_by_object_classes->keys())->count() > 0)
			&& (! collect($validation->get($this->name_lc))->contains('required'))) {
			$validation
				->prepend(array_merge(['required','min:1'],$validation->get($nolangtag,[])),$nolangtag)
				->prepend(array_merge(['required','array','min:1',($this->factory()->no_attr_tags ? 'max:1' : NULL)],$validation->get($this->name_lc,[])),$this->name_lc);
		}

		return $validation->toArray();
	}
}