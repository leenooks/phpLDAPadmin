<?php

namespace App\Classes\LDAP\Attribute\Syntax;

use Illuminate\Contracts\View\View;

use App\Classes\LDAP\Attribute;
use App\Classes\Template;

/**
 * Represents an attribute whose values are octect strings
 */
final class OctectString extends Attribute
{
	private function binGuidToString(string $binaryGuid): string
	{
		try {
			// Unpack the 16 bytes into hex chunks based on the endian layout
			// V = 32-bit little-endian, v = 16-bit little-endian, n = 16-bit big-endian, H* = hex remaining
			$unpacked = unpack('V1a/v1b/v1c/n1d/H*e',$binaryGuid);

			return sprintf(
				'%08x-%04x-%04x-%04x-%s',
				$unpacked['a'],
				$unpacked['b'],
				$unpacked['c'],
				$unpacked['d'],
				$unpacked['e']
			);

		} catch (\ErrorException $e) {
			return $binaryGuid;
		}
	}

	public function render_item_new(string $dotkey): ?string
	{
		return $this->binGuidToString(parent::render_item_new($dotkey));
	}

	public function render(string $attrtag,int $index,?View $view=NULL,bool $edit=FALSE,bool $editable=FALSE,bool $new=FALSE,bool $updated=FALSE,?Template $template=NULL): View
	{
		// @note Internal attributes cannot be edited
		if ($this->is_internal || $this->schema->is_no_user_modification)
			return ($view ?: view('components.attribute.value.syntax.octetstring'))
				->with('o',$this)
				->with('dotkey',$this->dotkey($attrtag,$index));

		return parent::render(
			attrtag: $attrtag,
			index: $index,
			view: view('components.attribute.value'),
			edit: $edit,
			editable: $editable,
			new: $new,
			updated: $updated,
			template: $template);
	}
}