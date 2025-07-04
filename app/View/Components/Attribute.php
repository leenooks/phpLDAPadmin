<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

use App\Classes\LDAP\Attribute as LDAPAttribute;
use App\Classes\Template;

class Attribute extends Component
{
	public ?LDAPAttribute $o;
	public bool $edit;
	public bool $new;
	public bool $old;
	public bool $updated;
	public ?Template $template;

	/**
	 * Create a new component instance.
	 */
	public function __construct(?LDAPAttribute $o,bool $edit=FALSE,bool $old=FALSE,bool $new=FALSE,bool $updated=FALSE,?Template $template=NULL)
	{
		$this->o = $o;
		$this->edit = $edit;
		$this->old = $old;
		$this->new = $new;
		$this->updated = $updated;
		$this->template = $template;
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
				->render(
					edit: $this->edit,
					old: $this->old,
					new: $this->new,
					updated: $this->updated,
					template: $this->template)
			: __('Unknown');
	}
}