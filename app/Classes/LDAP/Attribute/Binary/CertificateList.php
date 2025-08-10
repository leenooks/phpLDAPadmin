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
	public function render(string $attrtag,int $index,bool $edit=FALSE,bool $editable=FALSE,bool $new=FALSE,bool $updated=FALSE,?Template $template=NULL): View
	{
		return view('components.attribute.value.binary.certificatelist')
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
}