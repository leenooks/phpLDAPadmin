<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

use App\Classes\LDAP\Attribute as LDAPAttribute;

class AttributeType extends Component
{
	private LDAPAttribute $o;
	private bool $new;
	private string $langtag;

	/**
	 * Create a new component instance.
	 */
	public function __construct(LDAPAttribute $o,bool $new=FALSE,string $langtag='')
	{
		$this->o = $o;
		$this->new = $new;
		$this->langtag = $langtag;
	}

	/**
	 * Get the view / contents that represent the component.
	 */
	public function render(): View|Closure|string
	{
		return view('components.attribute-type')
			->with('o',$this->o)
			->with('new',$this->new)
			->with('langtag',$this->langtag);
	}
}