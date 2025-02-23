<?php

namespace App\Classes\LDAP\Attribute;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;

use App\Classes\LDAP\Attribute;

/**
 * Represents the RDN for an Entry
 */
final class RDN extends Attribute
{
	private string $base;
	private Collection $attrs;

	public function __get(string $key): mixed
	{
		return match ($key) {
			'base' => $this->base,
			'attrs' => $this->attrs->pluck('name'),
			default => parent::__get($key),
		};
	}

	public function hints(): array
	{
		return [
			'required' => __('RDN is required')
		];
	}

	public function render(bool $edit=FALSE,bool $old=FALSE,bool $new=FALSE): View
	{
		return view('components.attribute.rdn')
			->with('o',$this);
	}

	public function setAttributes(Collection $attrs): void
	{
		$this->attrs = $attrs;
	}

	public function setBase(string $base): void
	{
		$this->base = $base;
	}
}