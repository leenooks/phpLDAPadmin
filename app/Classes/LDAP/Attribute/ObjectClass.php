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
	// Which of the values is the structural object class
	protected Collection $structural;

	public function __construct(string $name,array $values)
	{
		parent::__construct($name,$values);

		$this->structural = collect();

		// Determine which of the values is the structural objectclass
		foreach ($values as $oc) {
			if (config('server')->schema('objectclasses',$oc)->isStructural())
				$this->structural->push($oc);
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
		return $this->structural->search($value) !== FALSE;
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