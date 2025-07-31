<?php

namespace App\Classes\LDAP\Attribute\Schema;

use Illuminate\Contracts\View\View;

use App\Classes\LDAP\Attribute\Schema;
use App\Classes\Template;

/**
 * Represents an OID Attribute
 */
final class OID extends Schema
{
	public function __construct(string $dn,string $name,array $values,array $oc=[])
	{
		parent::__construct($dn,$name,$values,$oc);

		$this->values_old = $this->values;
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
	public static function get(string $string,string $key): ?string
	{
		return parent::_get(config_path('ldap_supported_oids.txt'),$string,$key);
	}

	public function render(string $attrtag,int $index,bool $edit=FALSE,bool $editable=FALSE,bool $new=FALSE,bool $updated=FALSE,?Template $template=NULL): View
	{
		// @note Schema attributes cannot be edited
		return view('components.attribute.value.schema.oid')
			->with('o',$this)
			->with('dotkey',$dotkey=$this->dotkey($attrtag,$index))
			->with('value',$this->render_item_new($dotkey));
	}
}