<?php

namespace App\View\Components\Attribute;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

use App\Classes\LDAP\Attribute as LDAPAttribute;
use App\Classes\Template;

/**
 * This class enables us to call the attribute's render() method, that determines which component to use for the attribute
 */
class Value extends Component
{
	private(set) LDAPAttribute $o;		// The attribute being rendered
	private(set) string $attrtag;		// The render's values attribute tag
	private(set) int $index;			// The render's values index of multivalue attribute values
	private(set) bool $edit;			// Render the value editable
	private(set) bool $editable;		// Render the value so javascript can make it editable
	private(set) bool $new;				// Render "Add" if the attribute allows multiple values @todo change to "add"
	private(set) bool $updated;			// Can we work this out, because >old != >new
	private(set) ?Template $template;	// Template this value is being rendered with - needed for JavaScript and CSS id's
	private(set) ?string $value;		// The attributes value

	public function __construct(LDAPAttribute $o,string $attrtag,int $index,?string $value='',bool $edit=FALSE,bool $new=FALSE,bool $editable=FALSE,bool $updated=FALSE,?Template $template=NULL) {
		$this->o = $o;
		$this->attrtag = $attrtag;
		$this->index = $index;
		$this->edit = $edit;
		$this->editable = $editable;
		$this->new = $new;
		$this->updated = $updated;
		$this->template = $template;
		$this->value = $value;
	}

	public function render(): View|string
	{
		return $this->o
			->render(
				attrtag: $this->attrtag,
				index: $this->index,
				edit: $this->edit,
				editable: $this->editable,
				new: $this->new,
				updated: $this->updated,
				template: $this->template,
			);
	}
}