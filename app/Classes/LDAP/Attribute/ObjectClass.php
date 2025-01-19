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

	public function __construct(string $name,array $values)
	{
		parent::__construct($name,$values);

		$this->oc_schema = config('server')
			->schema('objectclasses')
			->filter(fn($item)=>$this->values->contains($item->name));
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