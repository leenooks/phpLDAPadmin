<?php

namespace App\Schema;

use Adldap\Schemas\OpenLDAP;
use App\LdapUser;

class Adldap extends OpenLDAP
{
    public function userModel()
    {
        return LdapUser::class;
    }
}
