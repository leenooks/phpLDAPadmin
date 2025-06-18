<?php

namespace App\Classes\LDAP\Attribute;

use Illuminate\Contracts\View\View;

use App\Classes\LDAP\Attribute;
use App\Classes\Template;
use App\Traits\MD5Updates;

/**
 * Represents an attribute whose values are passwords
 */
final class KrbPrincipalKey extends Attribute
{
	use MD5Updates;

	protected(set) bool $no_attr_tags = TRUE;

	public function render(bool $edit=FALSE,bool $old=FALSE,bool $new=FALSE,bool $updated=FALSE,?Template $template=NULL): View
	{
		return view('components.attribute.krbprincipalkey')
			->with('o',$this)
			->with('edit',$edit)
			->with('old',$old)
			->with('new',$new)
			->with('updated',$updated);
	}

	public function render_item_old(string $dotkey): ?string
	{
		return parent::render_item_old($dotkey)
			? str_repeat('*',16)
			: NULL;
	}

	public function render_item_new(string $dotkey): ?string
	{
		return parent::render_item_new($dotkey)
			? str_repeat('*',16)
			: NULL;
	}
}