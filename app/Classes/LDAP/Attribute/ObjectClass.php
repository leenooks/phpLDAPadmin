<?php

namespace App\Classes\LDAP\Attribute;

use App\Exceptions\InvalidUsage;
use Illuminate\Support\Collection;

use App\Classes\LDAP\Attribute;

/**
 * Represents an ObjectClass Attribute
 */
final class ObjectClass extends Attribute
{
	protected(set) bool $no_attr_tags = TRUE;

	protected(set) bool $modal_editable = TRUE;

	// The schema ObjectClasses for this objectclass of a DN
	protected Collection $oc_schema;

	/**
	 * Create an ObjectClass Attribute
	 *
	 * @param string $dn DN this attribute is used in
	 * @param string $name Name of the attribute
	 * @param array $values Current Values
	 * @param array $oc The objectclasses that the DN of this attribute has (ignored for objectclasses)
	 * @throws InvalidUsage
	 */
	public function __construct(string $dn,string $name,array $values,array $oc=[])
	{
		parent::__construct($dn,$name,$values,['top']);

		$this->set_oc_schema($this->tagValuesOld());
	}

	public function __get(string $key): mixed
	{
		return match ($key) {
			'structural' => $this->oc_schema->filter(fn($item)=>$item->isStructural()),
			default => parent::__get($key),
		};
	}

	public function __set(string $key,mixed $values): void
	{
		switch ($key) {
			case 'values':
				parent::__set($key,$values);

				// We need to populate oc_schema, if we are a new OC and thus dont have any old values
				if (! $this->values_old->count() && $this->values->count())
					$this->set_oc_schema($this->tagValues());

				break;

			default: parent::__set($key,$values);
		}
	}

	/**
	 * Is a specific value the structural objectclass
	 *
	 * @param string $value
	 * @return bool
	 */
	public function isStructural(string $value): bool
	{
		return $this->structural
			->map(fn($item)=>$item->name)
			->contains($value);
	}

	private function set_oc_schema(Collection $tv): void
	{
		$this->oc_schema = config('server')
			->schema('objectclasses')
			->filter(fn($item)=>$tv->contains($item->name));
	}
}