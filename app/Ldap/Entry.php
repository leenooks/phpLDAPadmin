<?php

namespace App\Ldap;

use LdapRecord\Models\Model;

class Entry extends Model
{
    /**
     * The object classes of the LDAP model.
     *
     * @var array
     */
    public static $objectClasses = [];
}
