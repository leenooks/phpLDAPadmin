<?php

namespace App\Classes\LDAP\Schema;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

use App\Classes\LDAP\Server;
use App\Exceptions\InvalidUsage;

/**
 * Represents an LDAP Schema objectClass
 */
final class ObjectClass extends Base
{
	private const LOGKEY = 'SOC';

	// Array of objectClasses which inherit from this one
	private(set) Collection $child_classes;

	// Array of objectClass names from which this objectClass inherits
	private(set) Collection $sup_classes;

	// One of STRUCTURAL, ABSTRACT, or AUXILIARY
	private int $type;

	// Attributes that this objectclass defines
	private(set) Collection $attributes;

	public function __get(string $key): mixed
	{
		return match ($key) {
			'all_attributes' => $this->getMustAttrs(TRUE)
				->merge($this->getMayAttrs(TRUE)),
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
	 * Adds an objectClass to the list of objectClasses that inherit
	 * from this objectClass.
	 *
	 * @param String $name The name of the objectClass to add
	 */
	public function addChildObjectClass(string $name): void
	{
		if (! $this->child_classes->contains($name))
			$this->child_classes->push($name);
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
		$attrs = $this->attributes
			->filter(fn($item)=>! $item->is_must)
			->transform(function($item) {
				$item->source = $this->name;
				return $item;
			});

		if ($parents)
			foreach ($this->getParents() as $object_class)
				$attrs = $attrs->merge($object_class
					->getMayAttrs($parents)
					->transform(function($item) use ($object_class) {
						$item->source = $item->source ?: $object_class->name;
						return $item;
					}));

		// Return a sorted list
		return $attrs
			->unique(fn($item)=>$item->name)
			->sortBy(fn($item)=>$item->name);
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
		$attrs = $this->attributes
			->filter(fn($item)=>$item->is_must)
			->transform(function($item) {
				$item->source = $this->name;
				return $item;
			});

		if ($parents)
			foreach ($this->getParents() as $object_class)
				$attrs = $attrs->merge($object_class
					->getMustAttrs($parents)
					->transform(function($item) use ($object_class) {
						$item->source = $item->source ?: $object_class->name;
						return $item;
					}));

		// Return a sorted list
		return $attrs
			->unique(fn($item)=>$item->name)
			->sortBy(fn($item)=>$item->name);
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
	 * Return if this objectclass is auxiliary
	 *
	 * @return bool
	 */
	public function isAuxiliary(): bool
	{
		return $this->type === Server::OC_AUXILIARY;
	}

	public function isStructural(): bool
	{
		return $this->type === Server::OC_STRUCTURAL;
	}

	/**
	 * Creates a new ObjectClass object given a raw LDAP objectClass string.
	 *
	 * eg: ( 2.5.6.0 NAME 'top' DESC 'top of the superclass chain' ABSTRACT MUST objectClass )
	 *
	 * @param string $line Schema Line
	 */
	protected function parse(string $line): void
	{
		Log::debug(sprintf('%s:Parsing ObjectClass [%s]',self::LOGKEY,$line));

		// Init
		$this->attributes = collect();
		$this->sup_classes = collect();
		$this->child_classes = collect();

		parent::parse($line);
	}

	protected function parse_chunk(array $strings,int &$i): void
	{
		switch ($strings[$i]) {
			case 'SUP':
				if ($strings[$i+1] !== '(') {
					$this->sup_classes->push(preg_replace("/'/",'',$strings[++$i]));

				} else {
					$i++;

					do {
						$i++;

						if ($strings[$i] !== '$')
							$this->sup_classes->push(preg_replace("/'/",'',$strings[$i]));

					} while (! preg_match('/\)+\)?/',$strings[$i+1]));
				}

				if (static::DEBUG_VERBOSE)
					Log::debug(sprintf('%s:- Case SUP returned (%s)',self::LOGKEY,$this->sup_classes->join(',')));
				break;

			case 'ABSTRACT':
				$this->type = Server::OC_ABSTRACT;

				if (static::DEBUG_VERBOSE)
					Log::debug(sprintf('%s:- Case ABSTRACT returned (%s)',self::LOGKEY,$this->type));
				break;

			case 'STRUCTURAL':
				$this->type = Server::OC_STRUCTURAL;

				if (static::DEBUG_VERBOSE)
					Log::debug(sprintf('%s:- Case STRUCTURAL returned (%s)',self::LOGKEY,$this->type));
				break;

			case 'AUXILIARY':
				$this->type = Server::OC_AUXILIARY;

				if (static::DEBUG_VERBOSE)
					Log::debug(sprintf('%s:- Case AUXILIARY returned (%s)',self::LOGKEY,$this->type));
				break;

			case 'MUST':
				$attrs = collect();

				$i = $this->parseList(++$i,$strings,$attrs);

				foreach ($attrs as $string) {
					$attr = clone config('server')->schema('attributetypes',$string);

					if (! $attr->forced_as_may)
						$attr->setMust();

					$this->attributes->push($attr);
				}

				if (static::DEBUG_VERBOSE)
					Log::debug(sprintf('%s:- Case MUST returned (%s) (%s)',self::LOGKEY,$attrs->join(','),$this->forced_as_may ? 'FORCED MAY' : 'MUST'));
				break;

			case 'MAY':
				$attrs = collect();

				$i = $this->parseList(++$i,$strings,$attrs);

				foreach ($attrs as $string)
					$this->attributes->push(config('server')->schema('attributetypes',$string));

				if (static::DEBUG_VERBOSE)
					Log::debug(sprintf('%s:- Case MAY returned (%s)',self::LOGKEY,$attrs->join(',')));
				break;

			default:
				parent::parse_chunk($strings,$i);
		}
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