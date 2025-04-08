<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

use App\Classes\LDAP\Attribute as LDAPAttribute;
use App\Ldap\Entry;

class AttributeType extends Component
{
	private LDAPAttribute $o;
	private bool $new;
	private bool $edit;
	private string $langtag;

	/**
	 * Create a new component instance.
	 */
	public function __construct(LDAPAttribute $o,bool $new=FALSE,bool $edit=FALSE,string $langtag=Entry::TAG_NOTAG)
	{
		$this->o = $o;
		$this->new = $new;
		$this->edit = $edit;
		$this->langtag = $langtag;
	}

	/**
	 * Get the view / contents that represent the component.
	 */
	public function render(): View
	{
		return view('components.attribute-type')
			->with('o',$this->o)
			->with('new',$this->new)
			->with('edit',$this->edit)
			->with('langtag',$this->langtag);
	}
}