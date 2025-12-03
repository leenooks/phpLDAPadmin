<?php

namespace App\Classes\LDAP\Attribute\Binary;

use Illuminate\Contracts\View\View;

use App\Classes\LDAP\Attribute\Binary;
use App\Classes\Template;

/**
 * Represents an attribute whose values is a binary Certificate Revocation Lists (CRLs)
 */
final class CertificateList extends Binary
{
	public function render(string $attrtag,int $index,?View $view=NULL,bool $edit=FALSE,bool $editable=FALSE,bool $new=FALSE,bool $updated=FALSE,?Template $template=NULL): View
	{
		return parent::render(
			attrtag: $attrtag,
			index: $index,
			view: view('components.attribute.value.binary.certificatelist'),
			edit: $edit,
			editable: $editable,
			new: $new,
			updated: $updated,
			template: $template);
	}

	public function render_item_old(string $dotkey): ?string
	{
		return join("\n",str_split(parent::render_item_old($dotkey),self::CERTIFICATE_ENCODE_LENGTH));
	}

	public function render_item_new(string $dotkey): ?string
	{
		return join("\n",str_split(parent::render_item_new($dotkey),self::CERTIFICATE_ENCODE_LENGTH));
	}
}