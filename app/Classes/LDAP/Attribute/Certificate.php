<?php

namespace App\Classes\LDAP\Attribute;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

use App\Classes\LDAP\Attribute;
use App\Traits\MD5Updates;

/**
 * Represents an attribute whose values is a binary user certificate
 */
final class Certificate extends Attribute
{
	use MD5Updates;

	private array $_object = [];

	public function authority_key_identifier(int $key=0): string
	{
		$data = collect(explode("\n",$this->cert_info('extensions.authorityKeyIdentifier',$key)));
		return $data
			->filter(fn($item)=>Str::startsWith($item,'keyid:'))
			->map(fn($item)=>Str::after($item,'keyid:'))
			->first();
	}

	public function certificate(int $key=0): string
	{
		return sprintf("-----BEGIN CERTIFICATE-----\n%s\n-----END CERTIFICATE-----",
			join("\n",str_split(base64_encode(Arr::get($this->values_old,'binary.'.$key)),80))
		);
	}

	public function cert_info(string $index,int $key=0): mixed
	{
		if (! array_key_exists($key,$this->_object))
			$this->_object[$key] = openssl_x509_parse(openssl_x509_read($this->certificate($key)));

		return Arr::get($this->_object[$key],$index);
	}

	public function expires(int $key=0): Carbon
	{
		return Carbon::createFromTimestampUTC($this->cert_info('validTo_time_t',$key));
	}

	public function field(string $field,int $key=0): string
	{
		$subject = collect($this->cert_info($field,$key))->reverse();

		return $subject->map(fn($item,$key)=>sprintf("%s=%s",$key,$item))->join(',');
	}

	public function subject_key_identifier(int $key=0): string
	{
		return $this->cert_info('extensions.subjectKeyIdentifier',$key);
	}
}