<?php

namespace App\View\Components;

use Illuminate\View\Component;

use App\Classes\LDAP\Attribute as LDAPAttribute;

class Attribute extends Component
{
	public ?LDAPAttribute $o;
	public bool $edit;
	public bool $new;
	public bool $old;
	public ?string $na;

    /**
     * Create a new component instance.
     */
    public function __construct(?LDAPAttribute $o,bool $edit=FALSE,bool $old=FALSE,bool $new=FALSE,string $na=NULL)
    {
		$this->o = $o;
		$this->edit = $edit;
		$this->old = $old;
		$this->new = $new;
		$this->na = $na;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
		return $this->o
			? $this->o
				->render($this->edit,$this->old,$this->new)
			: $this->na;
    }
}