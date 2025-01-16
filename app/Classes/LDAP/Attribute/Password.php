<?php

namespace App\Classes\LDAP\Attribute;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;

use App\Classes\LDAP\Attribute;
use App\Traits\MD5Updates;

/**
 * Represents an attribute whose values are passwords
 */
final class Password extends Attribute
{
	use MD5Updates;

	public function render(bool $edit=FALSE,bool $old=FALSE,bool $new=FALSE): View
	{
		return view('components.attribute.password')
			->with('o',$this)
			->with('edit',$edit)
			->with('old',$old)
			->with('new',$new);
	}

	public function render_item_old(int $key): ?string
	{
		return Arr::get($this->oldValues,$key) ? str_repeat('x',8) : NULL;
	}

	public function render_item_new(int $key): ?string
	{
		return Arr::get($this->values,$key) ? str_repeat('x',8) : NULL;
	}
}