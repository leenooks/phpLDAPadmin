<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

use App\Classes\LDAP\Attribute as LDAPAttribute;

class Attribute extends Component
{
	public ?LDAPAttribute $o;
	public bool $edit;
	public bool $new;
	public bool $old;
	public bool $updated;

	/**
	 * Create a new component instance.
	 */
	public function __construct(?LDAPAttribute $o,bool $edit=FALSE,bool $old=FALSE,bool $new=FALSE,bool $updated=FALSE)
	{
		$this->o = $o;
		$this->edit = $edit;
		$this->old = $old;
		$this->new = $new;
		$this->updated = $updated;
	}

	/**
	 * Get the view / contents that represent the component.
	 *
	 * @return View|string
	 */
	public function render(): View|string
	{
		return $this->o
			? $this->o
				->render(edit: $this->edit,old: $this->old,new: $this->new,updated: $this->updated)
			: __('Unknown');
	}
}