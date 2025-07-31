<?php

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;

function login_attr_description(): string
{
	return Arr::get(config('pla.login.attr'),login_attr_name());
}

function login_attr_name(): string
{
	return key(config('pla.login.attr'));
}

/**
 * Used to generate a random salt for crypt-style passwords. Salt strings are used
 * to make pre-built hash cracking dictionaries difficult to use as the hash algorithm uses
 * not only the user's password but also a randomly generated string. The string is
 * stored as the first N characters of the hash for reference of hashing algorithms later.
 *
 * @param int $length The length of the salt string to generate.
 * @return string The generated salt string.
 * @throws \Random\RandomException
 */
function random_salt(int $length): string
{
	$str = bin2hex(random_bytes(ceil($length/2)));
	if ($length%2 === 1)
		return substr($str,0,-1);

	return $str;
}

/**
 * For any incoming request, work out the command and DN involved
 *
 * @param Request $request
 * @param Collection|null $old
 * @return array
 */
function request_key(Request $request): array
{
	// Setup
	$cmd = NULL;
	$dn = NULL;
	$key = ($x=$request->get('_key',old('_key')))
		? Crypt::decryptString($x)
		: NULL;

	// Determine if our key has a command
	if (str_contains($key,'|')) {
		$m = [];

		if (preg_match('/\*([a-z_]+)\|(.+)$/',$key,$m)) {
			$cmd = $m[1];
			$dn = ($m[2] !== '_NOP') ? $m[2] : NULL;
		}

	} elseif ($x=old('dn',$request->get('_key'))) {
		$cmd = 'dn';
		$dn = Crypt::decryptString($x);
	}

	return ['cmd'=>$cmd,'dn'=>$dn];
}