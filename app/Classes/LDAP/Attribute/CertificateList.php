<?php

namespace App\Classes\LDAP\Attribute;

use Carbon\Carbon;
use Illuminate\Support\Arr;

use App\Classes\LDAP\Attribute;
use App\Traits\MD5Updates;

/**
 * Represents an attribute whose values is a binary user certificate
 */
final class CertificateList extends Attribute
{
	use MD5Updates;
}