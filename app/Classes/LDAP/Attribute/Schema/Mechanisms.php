<?php

namespace App\Classes\LDAP\Attribute\Schema;

use App\Classes\LDAP\Attribute\Schema;

/**
 * Represents an attribute whose values are binary
 */
final class Mechanisms extends Schema
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
	protected static function get(string $string,string $key): ?string
	{
		return parent::_get(config_path('ldap_supported_saslmechanisms.txt'),$string,$key);
	}
}