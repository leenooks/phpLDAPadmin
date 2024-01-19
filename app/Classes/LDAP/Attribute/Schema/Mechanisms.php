<?php

namespace App\Classes\LDAP\Attribute\Schema;

use Illuminate\Contracts\View\View;

use App\Classes\LDAP\Attribute\Schema;

/**
 * Represents a Mechanisms Attribute
 */
final class Mechanisms extends Schema
{
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
	public static function get(string $string,string $key): ?string
	{
		return parent::_get(config_path('ldap_supported_saslmechanisms.txt'),$string,$key);
	}

	public function render(bool $edit=FALSE,bool $old=FALSE,bool $new=FALSE): View
	{
		// @note Schema attributes cannot be edited
		return view('components.attribute.schema.mechanisms')
			->with('o',$this);
	}
}