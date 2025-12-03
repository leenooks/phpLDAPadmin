<?php

namespace App\Classes\LDAP\Attribute;

use App\Classes\LDAP\Attribute;
use App\Ldap\Entry;

final class Member extends Attribute
{
	protected(set) bool $no_attr_tags = TRUE;
	protected(set) bool $modal_editable = TRUE;

	public function dn_exists(string $dn): bool
	{
		return Entry::query()->setDN($dn)->exists();
	}
}