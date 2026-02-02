<?php

namespace App\Classes\LDAP\Attribute\Samba;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;

use App\Classes\LDAP\Attribute;
use App\Classes\Template;
use App\Interfaces\NoAttrTag;
use App\Ldap\Entry;

/**
 * Represents an attribute whose value is a Samba Account Flag
 * See smbpasswd
 *
 * Table 11.2. Samba SAM Account Control Block Flags
 * Flag    Description
 * D    Account is disabled.
 * H    A home directory is required.
 * I    An inter-domain trust account.
 * L    Account has been auto-locked.
 * M    An MNS (Microsoft network service) logon account.
 * N    Password not required.
 * S    A server trust account.
 * T    Temporary duplicate account entry.
 * U    A normal user account.
 * W    A workstation trust account.
 * X    Password does not expire.
 */
final class AcctFlags extends Attribute implements NoAttrTag
{
	public const values = [
		'D' => 'Account is disabled',
		'H' => 'Home directory is required',
		'I' => 'Inter-domain trust account',
		'L' => 'Account has been auto-locked',
		'M' => 'MNS (Microsoft network service) logon account',
		'N' => 'Password not required',
		'S' => 'Server trust account',
		'T' => 'Temporary duplicate account entry',
		'U' => 'Normal user account',
		'W' => 'Workstation trust account',
		'X' => 'Password does not expire',
	];

	protected static function helpers(): Collection
	{
		return collect(self::values);
	}

	public function isset(string $key): bool
	{
		static $value = preg_replace('/^\[(.*)\]$/','$1',\Arr::first(\Arr::get($this->values_old,Entry::TAG_NOTAG)));

		return \Str::contains($value,strtoupper($key));
	}

	public function render(string $attrtag,int $index,?View $view=NULL,bool $edit=FALSE,bool $editable=FALSE,bool $new=FALSE,bool $updated=FALSE,?Template $template=NULL): View
	{
		return parent::render(
			attrtag: $attrtag,
			index: $index,
			view: view('components.attribute.value.samba.acctflags')
				->with('helper',static::helpers()),
			edit: $edit,
			editable: $editable,
			new: $new,
			updated: $updated,
			template: $template);
	}

	protected function setValuesInternal(Collection $values): Collection
	{
		return $values
			->mapWithKeys(fn($item)=>
				[Entry::TAG_NOTAG => collect($item)
					->map(fn($item) => collect($item)
						->filter()
						->keys()
						->prepend('[')
						->add(']')
						->join(''))
					->toArray()]
			);
	}
}