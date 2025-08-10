<?php

namespace App\Classes\LDAP\Attribute\Binary;

use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

use App\Classes\LDAP\Attribute\Binary;
use App\Classes\Template;

/**
 * Represents an attribute whose values is a binary user certificate
 */
final class Certificate extends Binary
{
	private const LOGKEY = 'ACF';

	private array $_object = [];

	public function __get(string $key): mixed
	{
		return match ($key) {
			'binarytags' => $this->values
				->keys()
				->filter(fn($item)=>$item==='binary'),

			default => parent::__get($key)
		};
	}

	public function authority_key_identifier(string $dotkey): string
	{
		$data = collect(explode("\n",collect($this->cert_info($dotkey)->get('extensions'))->get('authorityKeyIdentifier','')));

		return (($data->count() > 1)
			? $data
				->filter(fn($item)=>Str::startsWith($item,'keyid:'))
				->map(fn($item)=>Str::after($item,'keyid:'))
			: $data)
			->first();
	}

	private function cert(string $string): string
	{
		return $this->is_cert($x=sprintf("-----BEGIN CERTIFICATE-----\n%s\n-----END CERTIFICATE-----",
			$y=join("\n",str_split($string,self::CERTIFICATE_ENCODE_LENGTH)))) ? $x : $y;
	}

	public function cert_info(string $dotkey): Collection
	{
		return collect(($x=$this->is_cert($this->render_item_new($dotkey)))
			? openssl_x509_parse($x)
			: []);
	}

	public function expires(string $dotkey): ?Carbon
	{
		return ($x=$this->cert_info($dotkey)->get('validTo_time_t'))
			? Carbon::createFromTimestampUTC($x)
			: NULL;
	}

	private function is_cert(string $cert): FALSE|\OpenSSLCertificate
	{
		$key = md5($cert);

		if (! array_key_exists($key,$this->_object)) {
			try {
				$this->_object[$key] = openssl_x509_read($cert);

			} catch (\ErrorException $e) {
				$this->_object[$key] = FALSE;
			}
		}

		return $this->_object[$key];
	}

	public function issuer(string $dotkey): string
	{
		$issuer = collect($this->cert_info($dotkey)->get('issuer'))->reverse();

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

	public function render_item_old(string $dotkey): ?string
	{
		return $this->cert(parent::render_item_old($dotkey));
	}

	public function render_item_new(string $dotkey): ?string
	{
		return $this->cert(parent::render_item_new($dotkey));
	}

	public function setValues(array $values): void
	{
		// We need to remove the BEGIN/END CERTIFICATE tags if they exist
		$vals = collect($values)->dot()->map(function ($item) {
			$item = preg_replace('/^(-+[A-Z\s]+-+\r\n)?/','',$item);
			$item = preg_replace('/(\r\n-+[A-Z\s]+-+)?$/','',$item);
			return $item;
		})->undot()->toArray();

		parent::setValues($vals);
	}

	public function subject(string $dotkey): string
	{
		$subject = collect($this->cert_info($dotkey)->get('subject'))->reverse();

		return $subject->map(fn($item,$key)=>sprintf("%s=%s",$key,$item))->join(',');
	}

	public function subject_key_identifier(string $dotkey): string
	{
		return collect($this->cert_info($dotkey)->get('extensions'))->get('subjectKeyIdentifier','');
	}
}