<?php

namespace App\Classes\LDAP\Attribute;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

use App\Classes\LDAP\Attribute;

/**
 * Represents an attribute whose values are binary
 */
class Mechanisms extends Attribute
{
	public function __toString(): string
	{
		return $this->values
			->transform(function($item) {
				$format = sprintf('<abbr class="pb-1" title="%s"><i class="fas fa-book pr-2"></i>%s</abbr>%s<p class="mb-0">%s</p>',
					$item,
					static::get($item,'title'),
					($x=static::get($item,'ref')) ? sprintf('<abbr class="pl-2" title="%s"><i class="fas fa-comment-dots"></i></abbr>',$x) : '',
					static::get($item,'desc'),
				);

				return $format;
			})->join('<br>');
	}

	/**
	 * Given an SASL Mechanism name, returns a verbose description of the Mechanism.
	 * This function parses ldap_supported_saslmechanisms.txt and looks up the specified
	 * Mechanism, and returns the verbose message defined in that file.
	 *
	 * <code>
	 *  "SCRAM-SHA-1" => array:3 [▼
	 *    "title" => "Salted Challenge Response Authentication Mechanism (SCRAM) SHA1"
	 *    "ref" => "RFC 5802"
	 *    "desc" => "This specification describes a family of authentication mechanisms called the Salted Challenge Response Authentication Mechanism (SCRAM) which addresses the req ▶"
	 *  ]
	 * </code>
	 *
	 * @param string $string The SASL Mechanism (ie, "SCRAM-SHA-1") of interest.
	 * @param string $key The title|ref|desc to return
	 * @return string|NULL
	 */
	private static function get(string $string,string $key): ?string
	{
		$array = Cache::remember('saslmechanisms',86400,function() {
			try {
				$f = fopen(config_path('ldap_supported_saslmechanisms.txt'),'r');

			} catch (\Exception $e) {
				return NULL;
			}

			$result = collect();

			while (! feof($f)) {
				$line = trim(fgets($f));

				if (! $line OR preg_match('/^#/',$line))
					continue;

				$fields = explode(':',$line);

				$result->put($x=Arr::get($fields,0),[
					'title'=>Arr::get($fields,1,$x),
					'ref'=>Arr::get($fields,2),
					'desc'=>Arr::get($fields,3,__('No description available, can you help with one?')),
				]);
			}
			fclose($f);

			return $result;
		});

		return Arr::get(($array ? $array->get($string) : []),$key);
	}
}