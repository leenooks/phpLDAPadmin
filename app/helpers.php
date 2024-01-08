<?php

use Illuminate\Support\Arr;

function login_attr_description(): string
{
	return Arr::get(config('ldap.login.attr'),login_attr_name());
}

function login_attr_name(): string
{
	return key(config('ldap.login.attr'));
}