<?php

namespace App\Classes\LDAP\Attribute;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;

use App\Classes\LDAP\Attribute;

/**
 * Represents an ObjectClass Attribute
 */
final class ObjectClass extends Attribute
{
	// The schema ObjectClasses for this objectclass of a DN
	protected Collection $oc_schema;

	/**
	 * Create an ObjectClass Attribute
	 *
	 * @param string $dn DN this attribute is used in
	 * @param string $name Name of the attribute
	 * @param array $values Current Values
	 * @param array $oc The objectclasses that the DN of this attribute has
	 */
	public function __construct(string $dn,string $name,array $values,array $oc=[])
	{
		parent::__construct($dn,$name,$values,['top']);

		$this->oc_schema = config('server')
			->schema('objectclasses')
			->filter(fn($item)=>$this->values->merge($this->values_old)->unique()->contains($item->name));
	}

	public function __get(string $key): mixed
	{
		return match ($key) {
			'structural' => $this->oc_schema->filter(fn($item) => $item->isStructural()),
			default => parent::__get($key),
		};
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

	public function render(bool $edit=FALSE,bool $old=FALSE,bool $new=FALSE): View
	{
		return view('components.attribute.objectclass')
			->with('o',$this)
			->with('edit',$edit)
			->with('old',$old)
			->with('new',$new);
	}
}