<?php

namespace App\Classes\LDAP\Attribute;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;

use App\Classes\LDAP\Attribute;
use App\Classes\Template;
use App\Interfaces\NoAttrTag;

/**
 * Represents an attribute whose value is a Kerberos Ticket Flag
 * See RFC4120
 */
final class KrbTicketFlags extends Attribute implements NoAttrTag
{
	private const DISALLOW_POSTDATED	= 0x00000001;
	private const DISALLOW_FORWARDABLE	= 0x00000002;
	private const DISALLOW_TGT_BASED	= 0x00000004;
	private const DISALLOW_RENEWABLE	= 0x00000008;
	private const DISALLOW_PROXIABLE	= 0x00000010;
	private const DISALLOW_DUP_SKEY		= 0x00000020;
	private const DISALLOW_ALL_TIX		= 0x00000040;
	private const REQUIRES_PRE_AUTH		= 0x00000080;
	private const REQUIRES_HW_AUTH		= 0x00000100;
	private const REQUIRES_PWCHANGE		= 0x00000200;
	private const DISALLOW_SVR			= 0x00001000;
	private const PWCHANGE_SERVICE		= 0x00002000;

	protected static function helpers(): Collection
	{
		$helpers = collect([
			log(self::DISALLOW_POSTDATED,2) => __('KRB_DISALLOW_POSTDATED'),
			log(self::DISALLOW_FORWARDABLE,2) => __('KRB_DISALLOW_FORWARDABLE'),
			log(self::DISALLOW_TGT_BASED,2) => __('KRB_DISALLOW_TGT_BASED'),
			log(self::DISALLOW_RENEWABLE,2) => __('KRB_DISALLOW_RENEWABLE'),
			log(self::DISALLOW_PROXIABLE,2) => __('KRB_DISALLOW_PROXIABLE'),
			log(self::DISALLOW_DUP_SKEY,2) => __('KRB_DISALLOW_DUP_SKEY'),
			log(self::DISALLOW_ALL_TIX,2) => __('KRB_DISALLOW_ALL_TIX'),
			log(self::REQUIRES_PRE_AUTH,2) => __('KRB_REQUIRES_PRE_AUTH'),
			log(self::REQUIRES_HW_AUTH,2) => __('KRB_REQUIRES_HW_AUTH'),
			log(self::REQUIRES_PWCHANGE,2) => __('KRB_REQUIRES_PWCHANGE'),
			log(self::DISALLOW_SVR,2) => __('KRB_DISALLOW_SVR'),
			log(self::PWCHANGE_SERVICE,2) => __('KRB_PWCHANGE_SERVICE'),
		])
		->replace(config('pla.krb.bits',[]));

		return $helpers;
	}

	public function render(string $attrtag,int $index,?View $view=NULL,bool $edit=FALSE,bool $editable=FALSE,bool $new=FALSE,bool $updated=FALSE,?Template $template=NULL): View
	{
		return parent::render(
			attrtag: $attrtag,
			index: $index,
			view: view('components.attribute.value.krbticketflags')
				->with('helper',static::helpers()),
			edit: $edit,
			editable: $editable,
			new: $new,
			updated: $updated,
			template: $template);
	}
}