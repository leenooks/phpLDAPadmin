<?php

namespace App\Classes\LDAP\Attribute;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

use App\Classes\LDAP\Attribute;
use App\Ldap\Entry;

/**
 * Represents the RDN for an Entry
 */
final class RDN extends Attribute
{
	private string $base;

	protected(set) bool $no_attr_tags = TRUE;

	private Collection $attrs;

	public function __get(string $key): mixed
	{
		return match ($key) {
			'base' => $this->base,
			'attrs' => $this->attrs->pluck('name'),
			'rdn_attr' => array_key_first(Arr::get($this->values,Entry::TAG_NOTAG)),
			'rdn_value' => Arr::get($this->values,Entry::TAG_NOTAG.'.'.$this->rdn_attr),

			default => parent::__get($key),
		};
	}

	public function hints(): Collection
	{
		return collect([
			'required' => __('RDN is required')
		]);
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