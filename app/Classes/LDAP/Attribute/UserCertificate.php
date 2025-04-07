<?php

namespace App\Classes\LDAP\Attribute;

use Carbon\Carbon;
use Illuminate\Support\Arr;

use App\Classes\LDAP\Attribute;
use App\Traits\MD5Updates;

/**
 * Represents an attribute whose values is a binary user certificate
 */
final class UserCertificate extends Attribute
{
	use MD5Updates;

	private array $_object = [];

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

	public function expires($key=0): Carbon
	{
		return Carbon::createFromTimestampUTC($this->cert_info('validTo_time_t',$key));
	}

	public function render_item_old(string $dotkey): ?string
	{
		return join("\n",str_split(base64_encode(parent::render_item_old($dotkey)),80));
	}

	public function subject($key=0): string
	{
		$subject = collect($this->cert_info('subject',$key))->reverse();

		return $subject->map(fn($item,$key)=>sprintf("%s=%s",$key,$item))->join(',');
	}
}