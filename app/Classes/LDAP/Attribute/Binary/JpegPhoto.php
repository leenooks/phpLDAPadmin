<?php

namespace App\Classes\LDAP\Attribute\Binary;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;

use App\Classes\LDAP\Attribute\Binary;
use App\Classes\Template;
use App\Ldap\Entry;

/**
 * Represents an JpegPhoto Attribute
 */
final class JpegPhoto extends Binary
{
	protected(set) bool $render_tables = TRUE;

	public function render(string $attrtag,int $index,bool $edit=FALSE,bool $editable=FALSE,bool $new=FALSE,bool $updated=FALSE,?Template $template=NULL): View
	{
		return view('components.attribute.value.binary.jpegphoto')
			->with('o',$this)
			->with('dotkey',$dotkey=$this->dotkey($attrtag,$index))
			->with('value',$this->values->dot()->get($dotkey))
			->with('edit',$edit)
			->with('editable',$editable)
			->with('new',$new)
			->with('attrtag',$attrtag)
			->with('index',$index)
			->with('updated',$updated)
			->with('template',$template)
			->with('f',new \finfo);
	}

	public function render_item_old(string $dotkey): ?string
	{
		return base64_encode(parent::render_item_old($dotkey));
	}

	public function render_item_new(string $dotkey): ?string
	{
		return base64_encode(parent::render_item_old($dotkey));
	}

	public function setValues(Collection $values): void
	{
		$processed = [];

		// If the attr tags are the same value as the md5 tag, then nothing has changed
		foreach ($this->keys as $key) {
			if ($values->has($key))
				foreach ($values->get($key) as $index => $value) {
					$md5value = $values->dot()->get($key.Entry::TAG_MD5.'.'.$index);

					if ((!$md5value) || ($value !== $md5value)) {
						$processed[$key.'.'.$index] = $value;

					} else {
						$processed[$key.Entry::TAG_MD5.'.'.$index] = $value;
					}
				}

			// We dont have an new values
			else {
				// If the old value matches the MD5, copy that.
				foreach ($values->get($key.Entry::TAG_MD5,[]) as $index => $value) {
					$old = $this->values_old->dot()->get($key.'.'.$index);

					if ($old && (md5($old) === $value))
						$processed[$key.'.'.$index] = $old;
				}
			}
		}

		parent::setValues($processed ? collect(\Arr::undot($processed)) : $values);
	}
}