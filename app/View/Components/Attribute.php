<?php

namespace App\View\Components;

use Illuminate\View\Component;

use App\Classes\LDAP\Attribute as LDAPAttribute;
use App\Ldap\Entry;

class Attribute extends Component
{
	public ?LDAPAttribute $o;
	public bool $edit;
	public bool $new;
	public bool $old;
	public string $langtag;
	public ?string $na;	// Text to render if the LDAPAttribute is null

	/**
	 * Create a new component instance.
	 */
	public function __construct(?LDAPAttribute $o,bool $edit=FALSE,bool $old=FALSE,bool $new=FALSE,string $langtag=Entry::TAG_NOTAG,?string $na=NULL)
	{
		$this->o = $o;
		$this->edit = $edit;
		$this->old = $old;
		$this->new = $new;
		$this->langtag = $langtag;
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