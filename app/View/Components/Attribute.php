<?php

namespace App\View\Components;

use Illuminate\View\Component;

use App\Classes\LDAP\Attribute as LDAPAttribute;

class Attribute extends Component
{
	public LDAPAttribute $o;
	public bool $edit;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(bool $edit,LDAPAttribute $o)
    {
		$this->edit = $edit;
		$this->o = $o;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
		return $this->o->render($this->edit);
    }
}