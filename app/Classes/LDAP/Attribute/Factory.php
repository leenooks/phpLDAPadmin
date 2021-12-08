<?php

namespace App\Classes\LDAP\Attribute;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

use App\Classes\LDAP\{Attribute};

class Factory
{
	private const LOGKEY = 'LAf';

	/**
	 * @var array event type to event class mapping
	 */
	public const map = [
		'jpegphoto'=>Attribute\Binary\JpegPhoto::class,
	];

	/**
	 * Returns new event instance
	 *
	 * @param string $attribute
	 * @param array $values
	 * @return Attribute
	 */
	public static function create(string $attribute,array $values): Attribute
	{
		$class = Arr::get(self::map,strtolower($attribute),Attribute::class);
		Log::debug(sprintf('%s:Creating LDAP Attribute [%s] as [%s]',static::LOGKEY,$attribute,$class));

		return new $class($attribute,$values);
	}
}