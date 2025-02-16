<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

use App\Classes\LDAP\Attribute as LDAPAttribute;

class AttributeType extends Component
{
	public Collection $oc;
	public LDAPAttribute $o;
	public bool $new;

	/**
	 * Create a new component instance.
	 */
	public function __construct(LDAPAttribute $o,bool $new=FALSE,?Collection $oc=NULL)
	{
		$this->o = $o;
		$this->oc = $oc;
		$this->new = $new;
	}

	/**
	 * Get the view / contents that represent the component.
	 */
	public function render(): View|Closure|string
	{
		return view('components.attribute-type')
			->with('o',$this->o)
			->with('oc',$this->oc)
			->with('new',$this->new);
	}
}