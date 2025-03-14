<?php

namespace App\Classes\LDAP\Schema;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Represents an LDAP AttributeType
 *
 * @package phpLDAPadmin
 * @subpackage Schema
 */
final class AttributeType extends Base {
	// The attribute from which this attribute inherits (if any)
	private ?string $sup_attribute = NULL;

	// Array of AttributeTypes which inherit from this one
	private Collection $children;

	// The equality rule used
	private ?string $equality = NULL;

	// The ordering of the attributeType
	private ?string $ordering = NULL;

	// Supports substring matching?
	private ?string $sub_str_rule = NULL;

	// The full syntax string, ie 1.2.3.4{16}
	private ?string $syntax = NULL;
	private ?string $syntax_oid = NULL;

	// boolean: is single valued only?
	private bool $is_single_value = FALSE;

	// boolean: is collective?
	private bool $is_collective = FALSE;

	// boolean: can use modify?
	private bool $is_no_user_modification = FALSE;

	// The usage string set by the LDAP schema
	private ?string $usage = NULL;

	// An array of alias attribute names, strings
	private Collection $aliases;

	// The max number of characters this attribute can be
	private ?int $max_length = NULL;

	// A string description of the syntax type (taken from the LDAPSyntaxes)
	/**
	 * @deprecated - reference syntaxes directly if possible
	 * @var string
	 */
	private ?string $type = NULL;

	// An array of objectClasses which use this attributeType (must be set by caller)
	private Collection $used_in_object_classes;

	// A list of object class names that require this attribute type.
	private Collection $required_by_object_classes;

	// This attribute has been forced a MAY attribute by the configuration.
	private bool $forced_as_may = FALSE;

	/**
	 * Creates a new AttributeType object from a raw LDAP AttributeType string.
	 *
	 * eg: ( 2.5.4.0 NAME 'objectClass' DESC 'RFC4512: object classes of the entity' EQUALITY objectIdentifierMatch SYNTAX 1.3.6.1.4.1.1466.115.121.1.38 )
	 */
	public function __construct(string $line) {
		if (static::DEBUG_VERBOSE)
			Log::debug(sprintf('Parsing AttributeType [%s]',$line));

		parent::__construct($line);

		$strings = preg_split('/[\s,]+/',$line,-1,PREG_SPLIT_DELIM_CAPTURE);

		// Init
		$this->children = collect();
		$this->aliases = collect();
		$this->used_in_object_classes = collect();
		$this->required_by_object_classes = collect();

		for ($i=0; $i < count($strings); $i++) {
			switch ($strings[$i]) {
				case '(':
				case ')':
					break;

				case 'NAME':
					// @note Some schema's return a (' instead of a ( '
					if ($strings[$i+1] != '(' && ! preg_match('/^\(/',$strings[$i+1])) {
						do {
							$this->name .= ($this->name ? ' ' : '').$strings[++$i];

						} while (! preg_match("/\'$/s",$strings[$i]));

						// This attribute has no aliases
						//$this->aliases = collect();

					} else {
						$i++;

						do {
							// In case we came here becaues of a ('
							if (preg_match('/^\(/',$strings[$i]))
								$strings[$i] = preg_replace('/^\(/','',$strings[$i]);
							else
								$i++;

							$this->name .= ($this->name ? ' ' : '').$strings[++$i];

						} while (! preg_match("/\'$/s",$strings[$i]));

						// Add alias names for this attribute
						while ($strings[++$i] != ')') {
							$alias = $strings[$i];
							$alias = preg_replace("/^\'(.*)\'$/",'$1',$alias);
							$this->addAlias($alias);
						}
					}

					$this->name = preg_replace("/^\'(.*)\'$/",'$1',$this->name);

					if (static::DEBUG_VERBOSE)
						Log::debug(sprintf('- Case NAME returned (%s)',$this->name),['aliases'=>$this->aliases]);
					break;

				case 'DESC':
					do {
						$this->description .= ($this->description ? ' ' : '').$strings[++$i];

					} while (! preg_match("/\'$/s",$strings[$i]));

					$this->description = preg_replace("/^\'(.*)\'$/",'$1',$this->description);

					if (static::DEBUG_VERBOSE)
						Log::debug(sprintf('- Case DESC returned (%s)',$this->description));
					break;

				case 'OBSOLETE':
					$this->is_obsolete = TRUE;

					if (static::DEBUG_VERBOSE)
						Log::debug(sprintf('- Case OBSOLETE returned (%s)',$this->is_obsolete));
					break;

				case 'SUP':
					$i++;
					$this->sup_attribute = preg_replace("/^\'(.*)\'$/",'$1',$strings[$i]);

					if (static::DEBUG_VERBOSE)
						Log::debug(sprintf('- Case SUP returned (%s)',$this->sup_attribute));
					break;

				case 'EQUALITY':
					$this->equality = $strings[++$i];

					if (static::DEBUG_VERBOSE)
						Log::debug(sprintf('- Case EQUALITY returned (%s)',$this->equality));
					break;

				case 'ORDERING':
					$this->ordering = $strings[++$i];

					if (static::DEBUG_VERBOSE)
						Log::debug(sprintf('- Case ORDERING returned (%s)',$this->ordering));
					break;

				case 'SUBSTR':
					$this->sub_str_rule = $strings[++$i];

					if (static::DEBUG_VERBOSE)
						Log::debug(sprintf('- Case SUBSTR returned (%s)',$this->sub_str_rule));
					break;

				case 'SYNTAX':
					$this->syntax = $strings[++$i];
					$this->syntax_oid = preg_replace('/{\d+}$/','',$this->syntax);
					if (static::DEBUG_VERBOSE)
						Log::debug(sprintf('/ Evaluating SYNTAX returned (%s) [%s]',$this->syntax,$this->syntax_oid));

					// Does this SYNTAX string specify a max length (ie, 1.2.3.4{16})
					$m = [];
					if (preg_match('/{(\d+)}$/',$this->syntax,$m))
						$this->max_length = $m[1];
					else
						$this->max_length = NULL;

					if ($i < count($strings) - 1 && $strings[$i+1] == '{')
						do {
							$this->name .= ' '.$strings[++$i];
						} while ($strings[$i] != '}');

					$this->syntax = preg_replace("/^\'(.*)\'$/",'$1',$this->syntax);
					$this->syntax_oid = preg_replace("/^\'(.*)\'$/",'$1',$this->syntax_oid);

					if (static::DEBUG_VERBOSE)
						Log::debug(sprintf('- Case SYNTAX returned (%s) [%s] {%d}',$this->syntax,$this->syntax_oid,$this->max_length));
					break;

				case 'SINGLE-VALUE':
					$this->is_single_value = TRUE;

					if (static::DEBUG_VERBOSE)
						Log::debug(sprintf('- Case SINGLE-VALUE returned (%s)',$this->is_single_value));
					break;

				case 'COLLECTIVE':
					$this->is_collective = TRUE;

					if (static::DEBUG_VERBOSE)
						Log::debug(sprintf('- Case COLLECTIVE returned (%s)',$this->is_collective));
					break;

				case 'NO-USER-MODIFICATION':
					$this->is_no_user_modification = TRUE;

					if (static::DEBUG_VERBOSE)
						Log::debug(sprintf('- Case NO-USER-MODIFICATION returned (%s)',$this->is_no_user_modification));
					break;

				case 'USAGE':
					$this->usage = $strings[++$i];

					if (static::DEBUG_VERBOSE)
						Log::debug(sprintf('- Case USAGE returned (%s)',$this->usage));
					break;

				// @note currently not captured
				case 'X-ORDERED':
					if (static::DEBUG_VERBOSE)
						Log::error(sprintf('- Case X-ORDERED returned (%s)',$strings[++$i]));
					break;

				// @note currently not captured
				case 'X-ORIGIN':
					$value = '';

					do {
						$value .= ($value ? ' ' : '').$strings[++$i];

					} while (! preg_match("/\'$/s",$strings[$i]));

					if (static::DEBUG_VERBOSE)
						Log::error(sprintf('- Case X-ORIGIN returned (%s)',$value));
					break;

				default:
					if (preg_match('/[\d\.]+/i',$strings[$i]) && ($i === 1)) {
						$this->oid = $strings[$i];
						if (static::DEBUG_VERBOSE)
							Log::debug(sprintf('- Case default returned (%s)',$this->oid));

					} elseif ($strings[$i])
						Log::alert(sprintf('! Case default discovered a value NOT parsed (%s)',$strings[$i]),['line'=>$line]);
			}
		}
	}

	public function __clone()
	{
		// When we clone, we need to break the reference too
		$this->aliases = clone $this->aliases;
	}

	public function __get(string $key): mixed
	{
		switch ($key) {
			case 'aliases': return $this->aliases;
			case 'children': return $this->children;
			case 'forced_as_may': return $this->forced_as_may;
			case 'is_collective': return $this->is_collective;
			case 'is_editable': return ! $this->is_no_user_modification;
			case 'is_no_user_modification': return $this->is_no_user_modification;
			case 'is_single_value': return $this->is_single_value;
			case 'equality': return $this->equality;
			case 'max_length': return $this->max_length;
			case 'ordering': return $this->ordering;
			case 'required_by_object_classes': return $this->required_by_object_classes;
			case 'sub_str_rule': return $this->sub_str_rule;
			case 'sup_attribute': return $this->sup_attribute;
			case 'syntax': return $this->syntax;
			case 'syntax_oid': return $this->syntax_oid;
			case 'type': return $this->type;
			case 'usage': return $this->usage;
			case 'used_in_object_classes': return $this->used_in_object_classes;

			default: return parent::__get($key);
		}
	}

	/**
	 * Adds an attribute name to the alias array.
	 *
	 * @param string $alias The name of a new attribute to add to this attribute's list of aliases.
	 */
	public function addAlias(string $alias): void
	{
		$this->aliases->push($alias);
	}

	/**
	 * Children of this attribute type that inherit from this one
	 *
	 * @param string $child
	 * @return void
	 */
	public function addChild(string $child): void
	{
		$this->children->push($child);
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

	/**
	 * Gets the names of attributes that are an alias for this attribute (if any).
	 *
	 * @return Collection An array of names of attributes which alias this attribute or
	 *          an empty array if no attribute aliases this object.
	 * @deprecated use class->aliases
	 */
	public function getAliases(): Collection
	{
		return $this->aliases;
	}

	/**
	 * Gets this attribute's equality string
	 *
	 * @return string
	 * @deprecated use $this->equality
	 */
	public function getEquality()
	{
		return $this->equality;
	}

	/**
	 * Gets whether this attribute is collective.
	 *
	 * @return boolean Returns TRUE if this attribute is collective and FALSE otherwise.
	 * @deprecated use $this->is_collective
	 */
	public function getIsCollective(): bool
	{
		return $this->is_collective;
	}

	/**
	 * Gets whether this attribute is not modifiable by users.
	 *
	 * @return boolean Returns TRUE if this attribute is not modifiable by users.
	 * @deprecated use $this->is_no_user_modification
	 */
	public function getIsNoUserModification(): bool
	{
		return $this->is_no_user_modification;
	}

	/**
	 * Gets whether this attribute is single-valued. If this attribute only supports single values, TRUE
	 * is returned. If this attribute supports multiple values, FALSE is returned.
	 *
	 * @return boolean Returns TRUE if this attribute is single-valued or FALSE otherwise.
	 * @deprecated use class->is_single_value
	 */
	public function getIsSingleValue(): bool
	{
		return $this->is_single_value;
	}

	/**
	 * Gets this attribute's the maximum length. If no maximum is defined by the LDAP server, NULL is returned.
	 *
	 * @return int The maximum length (in characters) of this attribute or NULL if no maximum is specified.
	 * @deprecated use $this->max_length;
	 */
	public function getMaxLength()
	{
		return $this->max_length;
	}

	/**
	 * Gets this attribute's ordering specification.
	 *
	 * @return string
	 * @deprecated use $this->ordering
	 */
	public function getOrdering(): string
	{
		return $this->ordering;
	}

	/**
	 * Gets this attribute's substring matching specification
	 *
	 * @return string
	 * @deprecated use $this->sub_str_rule;
	 */
	public function getSubstr() {
		return $this->sub_str_rule;
	}

	/**
	 * Gets this attribute's parent attribute (if any). If this attribute does not
	 * inherit from another attribute, NULL is returned.
	 *
	 * @return string
	 * @deprecated use $class->sup_attribute directly
	 */
	public function getSupAttribute() {
		return $this->sup_attribute;
	}

	/**
	 * Gets this attribute's syntax OID. Differs from getSyntaxString() in that this
	 * function only returns the actual OID with any length specification removed.
	 * Ie, if the syntax string is "1.2.3.4{16}", this function only retruns
	 * "1.2.3.4".
	 *
	 * @return string The syntax OID string.
	 * @deprecated use $this->syntax_oid;
	 */
	public function getSyntaxOID()
	{
		return $this->syntax_oid;
	}

	/**
	 * Gets this attribute's usage string as defined by the LDAP server
	 *
	 * @return string
	 * @deprecated use $this->usage
	 */
	public function getUsage()
	{
		return $this->usage;
	}

	/**
	 * Gets the list of "used in" objectClasses, that is the list of objectClasses
	 * which provide this attribute.
	 *
	 * @return Collection An array of names of objectclasses (strings) which provide this attribute
	 * @deprecated use $this->used_in_object_classes
	 */
	public function getUsedInObjectClasses(): Collection
	{
		return $this->used_in_object_classes;
	}

	/**
	 * @return bool
	 * @deprecated use $this->forced_as_may
	 */
	public function isForceMay(): bool
	{
		return $this->forced_as_may;
	}

	/**
	 * Removes an attribute name from this attribute's alias array.
	 *
	 * @param string $alias The name of the attribute to remove.
	 */
	public function removeAlias(string $alias): void
	{
		if (($x=$this->aliases->search($alias)) !== FALSE)
			$this->aliases->forget($x);
	}

	/**
	 * Sets this attribute's list of aliases.
	 *
	 * @param Collection $aliases The array of alias names (strings)
	 * @deprecated use $this->aliases =
	 */
	public function setAliases(Collection $aliases): void
	{
		$this->aliases = $aliases;
	}

	/**
	 * This function will mark this attribute as a forced MAY attribute
	 */
	public function setForceMay() {
		$this->forced_as_may = TRUE;
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
	 * Sets this attribute's SUP attribute (ie, the attribute from which this attribute inherits).
	 *
	 * @param string $attr The name of the new parent (SUP) attribute
	 */
	public function setSupAttribute(string $attr): void
	{
		$this->sup_attribute = trim($attr);
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
		$heirachy = collect($array)
			->filter()
			->map(fn($item)=>config('server')
				->schema('objectclasses',$item)
				->getSupClasses()
				->push($item))
			->flatten()
			->unique();

		$validation = collect(Arr::get(config('ldap.validation'),$this->name_lc,[]));
		if (($heirachy->intersect($this->required_by_object_classes)->count() > 0)
			&& (! collect($validation->get($this->name_lc))->contains('required'))) {
			$validation->put($this->name_lc,array_merge(['required','min:1'],$validation->get($this->name_lc,[])))
				->put($this->name_lc.'.*',array_merge(['required','min:1'],$validation->get($this->name_lc.'.*',[])));
		}

		return $validation->toArray();
	}
}