<?php

namespace App\Classes\LDAP\Schema;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

use App\Classes\LDAP\Server;
use App\Exceptions\InvalidUsage;

/**
 * Represents an LDAP Schema objectClass
 *
 * @package phpLDAPadmin
 * @subpackage Schema
 */
final class ObjectClass extends Base
{
	// Array of objectClass names from which this objectClass inherits
	private Collection $sup_classes;

	// One of STRUCTURAL, ABSTRACT, or AUXILIARY
	private int $type;

	// Arrays of attribute names that this objectClass requires
	private Collection $must_attrs;

	// Arrays of attribute names that this objectClass allows, but does not require
	private Collection $may_attrs;

	// Arrays of attribute names that this objectClass has been forced to MAY attrs, due to configuration
	private Collection $may_force;

	// Array of objectClasses which inherit from this one
	private Collection $child_objectclasses;

	private bool $is_obsolete;

	/**
	 * Creates a new ObjectClass object given a raw LDAP objectClass string.
	 *
	 * eg: ( 2.5.6.0 NAME 'top' DESC 'top of the superclass chain' ABSTRACT MUST objectClass )
	 *
	 * @param string $line Schema Line
	 * @param Server $server
	 * @todo Change $server to $connection, no need to store the server object here
	 */
	public function __construct(string $line,Server $server)
	{
		parent::__construct($line);

		if (static::DEBUG_VERBOSE)
			Log::debug(sprintf('Parsing ObjectClass [%s]',$line));

		$strings = preg_split('/[\s,]+/',$line,-1,PREG_SPLIT_DELIM_CAPTURE);

		// Init
		$this->may_attrs = collect();
		$this->may_force = collect();
		$this->must_attrs = collect();
		$this->sup_classes = collect();
		$this->child_objectclasses = collect();

		for ($i=0; $i < count($strings); $i++) {
			switch ($strings[$i]) {
				case '(':
				case ')':
					break;

				case 'NAME':
					if ($strings[$i+1] != '(') {
						do {
							$this->name .= (strlen($this->name) ? ' ' : '').$strings[++$i];

						} while (! preg_match('/\'$/s',$strings[$i]));

					} else {
						$i++;

						do {
							$this->name .= (strlen($this->name) ? ' ' : '').$strings[++$i];

						} while (! preg_match('/\'$/s',$strings[$i]));

						do {
							$i++;
						} while (! preg_match('/\)+\)?/',$strings[$i]));
					}

					$this->name = preg_replace("/^\'(.*)\'$/",'$1',$this->name);

					if (static::DEBUG_VERBOSE)
						Log::debug(sprintf(sprintf('- Case NAME returned (%s)',$this->name)));
					break;

				case 'DESC':
					do {
						$this->description .= (strlen($this->description) ? ' ' : '').$strings[++$i];

					} while (! preg_match('/\'$/s',$strings[$i]));

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
					if ($strings[$i+1] != '(') {
						$this->sup_classes->push(preg_replace("/'/",'',$strings[++$i]));

					} else {
						$i++;

						do {
							$i++;

							if ($strings[$i] != '$')
								$this->sup_classes->push(preg_replace("/'/",'',$strings[$i]));

						} while (! preg_match('/\)+\)?/',$strings[$i+1]));
					}

					if (static::DEBUG_VERBOSE)
						Log::debug(sprintf('- Case SUP returned (%s)',$this->sup_classes->join(',')));
					break;

				case 'ABSTRACT':
					$this->type = Server::OC_ABSTRACT;

					if (static::DEBUG_VERBOSE)
						Log::debug(sprintf('- Case ABSTRACT returned (%s)',$this->type));
					break;

				case 'STRUCTURAL':
					$this->type = Server::OC_STRUCTURAL;

					if (static::DEBUG_VERBOSE)
						Log::debug(sprintf('- Case STRUCTURAL returned (%s)',$this->type));
					break;

				case 'AUXILIARY':
					$this->type = Server::OC_AUXILIARY;

					if (static::DEBUG_VERBOSE)
						Log::debug(sprintf('- Case AUXILIARY returned (%s)',$this->type));
					break;

				case 'MUST':
					$attrs = collect();

					$i = $this->parseList(++$i,$strings,$attrs);

					if (static::DEBUG_VERBOSE)
						Log::debug(sprintf('= parseList returned %d (%s)',$i,$attrs->join(',')));

					foreach ($attrs as $string) {
						$attr = new ObjectClassAttribute($string,$this->name);

						if ($server->isForceMay($attr->getName())) {
							$this->may_force->push($attr);
							$this->may_attrs->push($attr);

						} else
							$this->must_attrs->push($attr);
					}

					if (static::DEBUG_VERBOSE)
						Log::debug(sprintf('- Case MUST returned (%s) (%s)',$this->must_attrs->join(','),$this->may_force->join(',')));
					break;

				case 'MAY':
					$attrs = collect();

					$i = $this->parseList(++$i,$strings,$attrs);

					if (static::DEBUG_VERBOSE)
						Log::debug(sprintf('parseList returned %d (%s)',$i,$attrs->join(',')));

					foreach ($attrs as $string) {
						$attr = new ObjectClassAttribute($string,$this->name);
						$this->may_attrs->push($attr);
					}

					if (static::DEBUG_VERBOSE)
						Log::debug(sprintf('- Case MAY returned (%s)',$this->may_attrs->join(',')));
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

	public function __get(string $key): mixed
	{
		return match ($key) {
			'attributes' => $this->getAllAttrs(TRUE),
			'sup' => $this->sup_classes,
			'type_name' => match ($this->type) {
				Server::OC_STRUCTURAL => 'Structural',
				Server::OC_ABSTRACT => 'Abstract',
				Server::OC_AUXILIARY => 'Auxiliary',
				default => throw new InvalidUsage('Unknown ObjectClass Type: ' . $this->type),
			},
			default => parent::__get($key),
		};
	}

	/**
	 * Return a list of attributes that this objectClass provides
	 *
	 * @param bool $parents
	 * @return Collection
	 * @throws InvalidUsage
	 */
	public function getAllAttrs(bool $parents=FALSE): Collection
	{
		return $this->getMustAttrs($parents)
			->transform(function($item) {
				$item->required = true;
				return $item;
			})
			->merge($this->getMayAttrs($parents));
	}

	/**
	 * Adds an objectClass to the list of objectClasses that inherit
	 * from this objectClass.
	 *
	 * @param String $name The name of the objectClass to add
	 */
	public function addChildObjectClass(string $name): void
	{
		if (! $this->child_objectclasses->contains($name))
			$this->child_objectclasses->push($name);
	}

	/**
	 * Returns the array of objectClass names which inherit from this objectClass.
	 *
	 * @return Collection Names of objectClasses which inherit from this objectClass.
	 * @deprecated use $this->child_objectclasses
	 */
	public function getChildObjectClasses(): Collection
	{
		return $this->child_objectclasses;
	}

	/**
	 * Behaves identically to addMustAttrs, but it operates on the MAY
	 * attributes of this objectClass.
	 *
	 * @param array $attr An array of attribute names (strings) to add.
	 */
	private function addMayAttrs(array $attr): void
	{
		if (! is_array($attr) || ! count($attr))
			return;

		$this->may_attrs = $this->may_attrs->merge($attr)->unique();
	}

	/**
	 * Adds the specified array of attributes to this objectClass' list of
	 * MUST attributes. The resulting array of must attributes will contain
	 * unique members.
	 *
	 * @param array $attr An array of attribute names (strings) to add.
	 */
	private function addMustAttrs(array $attr): void
	{
		if (! is_array($attr) || ! count($attr))
			return;

		$this->must_attrs = $this->must_attrs->merge($attr)->unique();
	}

	/**
	 * @return Collection
	 * @deprecated use $this->may_force
	 */
	public function getForceMayAttrs(): Collection
	{
		return $this->may_force;
	}

	/**
	 * Gets an array of AttributeType objects that entries of this ObjectClass may define.
	 * This differs from getMayAttrNames in that it returns an array of AttributeType objects
	 *
	 * @param bool $parents Also get the may attrs of our parents.
	 * @return Collection The array of allowed AttributeType objects.
	 *
	 * @throws InvalidUsage
	 * @see getMustAttrNames
	 * @see getMustAttrs
	 * @see getMayAttrNames
	 * @see AttributeType
	 */
	public function getMayAttrs(bool $parents=FALSE): Collection
	{
		// If we dont need our parents, then we'll just return ours.
		if (! $parents)
			return $this->may_attrs
				->sortBy(fn($item)=>strtolower($item->name.$item->source));

		$attrs = $this->may_attrs;

		foreach ($this->getParents() as $object_class)
			$attrs = $attrs->merge($object_class->getMayAttrs($parents));

		// Remove any duplicates
		$attrs = $attrs->unique(function($item) { return $item->name; });

		// Return a sorted list
		return $attrs->sortBy(function($item) { return strtolower($item->name.$item->source); });
	}

	/**
	 * Gets an array of attribute names (strings) that entries of this ObjectClass must define.
	 * This differs from getMayAttrs in that it returns an array of strings rather than
	 * array of AttributeType objects
	 *
	 * @param bool $parents An array of ObjectClass objects to use when traversing
	 *             the inheritance tree. This presents some what of a bootstrapping problem
	 *             as we must fetch all objectClasses to determine through inheritance which
	 *             attributes this objectClass provides.
	 * @return Collection The array of allowed attribute names (strings).
	 *
	 * @throws InvalidUsage
	 * @see getMustAttrs
	 * @see getMayAttrs
	 * @see getMustAttrNames
	 */
	public function getMayAttrNames(bool $parents=FALSE): Collection
	{
		return $this->getMayAttrs($parents)->ppluck('name');
	}

	/**
	 * Gets an array of AttributeType objects that entries of this ObjectClass must define.
	 * This differs from getMustAttrNames in that it returns an array of AttributeType objects
	 *
	 * @param bool $parents Also get the must attrs of our parents.
	 * @return Collection The array of required AttributeType objects.
	 *
	 * @throws InvalidUsage
	 * @see getMustAttrNames
	 * @see getMayAttrs
	 * @see getMayAttrNames
	 */
	public function getMustAttrs(bool $parents=FALSE): Collection
	{
		// If we dont need our parents, then we'll just return ours.
		if (! $parents)
			return $this->must_attrs->sortBy(function($item) { return strtolower($item->name.$item->source); });

		$attrs = $this->must_attrs;

		foreach ($this->getParents() as $object_class)
			$attrs = $attrs->merge($object_class->getMustAttrs($parents));

		// Remove any duplicates
		$attrs = $attrs->unique(function($item) { return $item->name; });

		// Return a sorted list
		return $attrs->sortBy(function($item) { return strtolower($item->name.$item->source); });
	}

	/**
	 * Gets an array of attribute names (strings) that entries of this ObjectClass must define.
	 * This differs from getMustAttrs in that it returns an array of strings rather than
	 * array of AttributeType objects
	 *
	 * @param bool $parents An array of ObjectClass objects to use when traversing
	 *             the inheritance tree. This presents some what of a bootstrapping problem
	 *             as we must fetch all objectClasses to determine through inheritance which
	 *             attributes this objectClass provides.
	 * @return Collection The array of allowed attribute names (strings).
	 *
	 * @throws InvalidUsage
	 * @see getMustAttrs
	 * @see getMayAttrs
	 * @see getMayAttrNames
	 */
	public function getMustAttrNames(bool $parents=FALSE): Collection
	{
		return $this->getMustAttrs($parents)->ppluck('name');
	}

	/**
	 * This will return all our parent ObjectClass Objects
	 */
	public function getParents(): Collection
	{
		// If the only class is 'top', then we have no more parents
		if (($this->sup_classes->count() === 1) && (strtolower($this->sup_classes->first()) === 'top'))
			return collect();

		$result = collect();

		foreach ($this->sup_classes as $object_class) {
			$oc = config('server')
				->schema('objectclasses',$object_class);

			if ($oc) {
				$result->push($oc);
				$result = $result->merge($oc->getParents());
			}
		}

		return $result;
	}

	/**
	 * Gets the objectClass names from which this objectClass inherits.
	 *
	 * @return Collection An array of objectClass names (strings)
	 * @deprecated use $this->sup_classes;
	 */
	public function getSupClasses(): Collection
	{
		return $this->sup_classes;
	}

	/**
	 * Gets the type of this objectClass: STRUCTURAL, ABSTRACT, or AUXILIARY.
	 *
	 * @deprecated use $this->type_name
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Return if this objectclass is auxiliary
	 *
	 * @return bool
	 */
	public function isAuxiliary(): bool
	{
		return $this->type === Server::OC_AUXILIARY;
	}

	/**
	 * Determine if an array is listed in the may_force attrs
	 */
	public function isForceMay(string $attr): bool
	{
		return $this->may_force->ppluck('name')->contains($attr);
	}

	/**
	 * Return if this objectClass is related to $oclass
	 *
	 * @param array $oclass ObjectClasses that this attribute may be related to
	 * @return bool
	 * @throws InvalidUsage
	 */
	public function isRelated(array $oclass): bool
	{
		// If I am in the array, we'll just return false
		if (in_array_ignore_case($this->name,$oclass))
			return FALSE;

		foreach ($oclass as $object_class)
			if ($object_class->isStructural() && in_array_ignore_case($this->name,$object_class->getParents()->pluck('name')))
				return TRUE;

		return FALSE;
	}

	public function isStructural(): bool
	{
		return $this->type === Server::OC_STRUCTURAL;
	}

	/**
	 * Parse an LDAP schema list
	 *
	 * A list starts with a ( followed by a list of attributes separated by $ terminated by )
	 * The first token can therefore be a ( or a (NAME or a (NAME)
	 * The last token can therefore be a ) or NAME)
	 * The last token may be terminated by more than one bracket
	 */
	private function parseList(int $i,array $strings,Collection &$attrs): int
	{
		$string = $strings[$i];

		if (! preg_match('/^\(/',$string)) {
			// A bareword only - can be terminated by a ) if the last item
			if (preg_match('/\)+$/',$string))
				$string = preg_replace('/\)+$/','',$string);

			$attrs->push($string);

		} elseif (preg_match('/^\(.*\)$/',$string)) {
			$string = preg_replace('/^\(/','',$string);
			$string = preg_replace('/\)+$/','',$string);

			$attrs->push($string);

		} else {
			// Handle the opening cases first
			if ($string === '(') {
				$i++;

			} elseif (preg_match('/^\(./',$string)) {
				$string = preg_replace('/^\(/','',$string);
				$attrs->push($string);
				$i++;
			}

			// Token is either a name, a $ or a ')'
			// NAME can be terminated by one or more ')'
			while (! preg_match('/\)+$/',$strings[$i])) {
				$string = $strings[$i];

				if ($string === '$') {
					$i++;
					continue;
				}

				if (preg_match('/\)$/',$string))
					$string = preg_replace('/\)+$/','',$string);
				else
					$i++;

				$attrs->push($string);
			}
		}

		$attrs = $attrs->sort();

		return $i;
	}
}