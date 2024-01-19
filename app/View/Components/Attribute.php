<?php

namespace App\View\Components;

use Illuminate\View\Component;

use App\Classes\LDAP\Attribute as LDAPAttribute;

class Attribute extends Component
{
	public LDAPAttribute $o;
	public bool $edit;
	public bool $new;
	public bool $old;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(LDAPAttribute $o,bool $edit,bool $old=FALSE,bool $new=FALSE)
    {
		$this->o = $o;
		$this->edit = $edit;
		$this->old = $old;
		$this->new = $new;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
		return $this->o->render($this->edit,$this->old,$this->new);
    }
}