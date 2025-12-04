<?php

namespace App\Classes\LDAP\Attribute;

use Illuminate\Contracts\View\View;

use App\Classes\LDAP\Attribute;
use App\Classes\Template;
use App\Ldap\Entry;

final class Member extends Attribute
{
	protected(set) bool $no_attr_tags = TRUE;
	protected(set) bool $modal_editable = TRUE;

	public function dn_exists(string $dn): bool
	{
		return Entry::query()->setDN($dn)->exists();
	}

	public function render(string $attrtag,int $index,?View $view=NULL,bool $edit=FALSE,bool $editable=FALSE,bool $new=FALSE,bool $updated=FALSE,?Template $template=NULL): View
	{
		return parent::render(
			attrtag: $attrtag,
			index: $index,
			view: view('components.attribute.value.member'),
			edit: $edit,
			editable: $editable,
			new: $new,
			updated: $updated,
			template: $template);
	}
}