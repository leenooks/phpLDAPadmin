<?php

namespace App\Classes\LDAP\Attribute\Schema;

use App\Classes\LDAP\Attribute\Schema;

/**
 * Represents an attribute whose values are binary
 */
final class OID extends Schema
{
	public function __toString(): string
	{
		return $this->values
			->transform(function($item) {
				if (preg_match('/[0-9]+\.[0-9]+\.[0-9]+/',$item)) {
					$format = sprintf('<abbr class="pb-1" title="%s"><i class="fas fa-list-ol pr-2"></i>%s</abbr>%s<p class="mb-0">%s</p>',
						$item,
						static::get($item,'title'),
						($x=static::get($item,'ref')) ? sprintf('<abbr class="pl-2" title="%s"><i class="fas fa-comment-dots"></i></abbr>',$x) : '',
						static::get($item,'desc'),
					);

					return $format;

				} else
					return $item;
			})->join('<br>');
	}

	/**
	 * Given an LDAP OID number, returns a verbose description of the OID.
	 * This function parses ldap_supported_oids.txt and looks up the specified
	 * OID, and returns the verbose message defined in that file.
	 *
	 * <code>
	 *  "1.3.6.1.4.1.4203.1.5.1" => array:3 [
	 *    [title] => All Operational Attribute
	 *    [ref] => RFC 3673
	 *    [desc] => An LDAP extension which clients may use to request the return of all operational attributes.
	 *  ]
	 * </code>
	 *
	 * @param string $string The OID number (ie, "1.3.6.1.4.1.4203.1.5.1") of the OID of interest.
	 * @param string $key The title|ref|desc to return
	 * @return string|null
	 * @testedby TranslateOidTest::testRootDSE()
	 */
	protected static function get(string $string,string $key): ?string
	{
		return parent::_get(config_path('ldap_supported_oids.txt'),$string,$key);
	}
}