<?php

namespace App\Classes\LDAP\Attribute\Binary;

use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

use App\Classes\LDAP\Attribute\Binary;
use App\Classes\Template;

/**
 * Represents an attribute whose values is a binary user certificate
 */
final class Certificate extends Binary
{
	private array $_object = [];

	public function __get(string $key): mixed
	{
		return match ($key) {
			'binarytags'=> $this->_values
				->keys()
				->filter(fn($item) => $item === 'binary'),

			default => parent::__get($key)
		};
	}

	public function authority_key_identifier(int $key=0): string
	{
		$data = collect(explode("\n",$this->cert_info('extensions.authorityKeyIdentifier',$key)));

		return (($data->count() > 1)
			? $data
				->filter(fn($item)=>Str::startsWith($item,'keyid:'))
				->map(fn($item)=>Str::after($item,'keyid:'))
			: $data)
			->first();
	}

	public function certificate(int $key=0): string
	{
		return sprintf("-----BEGIN CERTIFICATE-----\n%s\n-----END CERTIFICATE-----",
			join("\n",str_split(base64_encode(Arr::get($this->_values_old,'binary.'.$key)),self::CERTIFICATE_ENCODE_LENGTH))
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

	public function issuer(int $key=0): string
	{
		$issuer = collect($this->cert_info('issuer',$key))->reverse();

		return $issuer->map(fn($item,$key)=>sprintf("%s=%s",$key,$item))->join(',');
	}

	public function render(string $attrtag,int $index,bool $edit=FALSE,bool $editable=FALSE,bool $new=FALSE,bool $updated=FALSE,?Template $template=NULL): View
	{
		return view('components.attribute.value.binary.certificate')
			->with('o',$this)
			->with('dotkey',$dotkey=$this->dotkey($attrtag,$index))
			->with('value',$this->render_item_new($dotkey))
			->with('edit',$edit)
			->with('editable',$editable)
			->with('new',$new)
			->with('attrtag',$attrtag)
			->with('index',$index)
			->with('updated',$updated)
			->with('template',$template);
	}

	public function subject(int $key=0): string
	{
		$subject = collect($this->cert_info('subject',$key))->reverse();

		return $subject->map(fn($item,$key)=>sprintf("%s=%s",$key,$item))->join(',');
	}

	public function subject_key_identifier(int $key=0): string
	{
		return $this->cert_info('extensions.subjectKeyIdentifier',$key);
	}
}