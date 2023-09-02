<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

use App\Classes\LDAP\Attribute as LDAPAttribute;

class AttributeType extends Component
{
	public LDAPAttribute $o;
	public bool $new;

	/**
	 * Create a new component instance.
	 */
	public function __construct(LDAPAttribute $o,bool $new=FALSE)
	{
		$this->o = $o;
		$this->new = $new;
	}

	/**
	 * Get the view / contents that represent the component.
	 */
	public function render(): View|Closure|string
	{
		return view('components.attribute-type')
			->with('o',$this->o)
			->with('new',$this->new);
	}
}