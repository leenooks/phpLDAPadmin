<?php

namespace App\Traits;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;

use App\Classes\LDAP\Attribute\Password;
use App\Classes\LDAP\Attribute\Password\Base;
use App\Classes\Template;
use App\Ldap\Entry;

/**
 * Functions required to facilitate hashing samba password attribute values
 */
trait SambaPassword
{
	/**
	 * Return the object that will process a password
	 *
	 * @param string $id
	 * @return Base|NULL
	 */
	public static function hash_id(string $id): ?Base
	{
		return ($helpers=static::helpers())->has($id) ? new ($helpers->get($id)) : NULL;
	}

	public function render(string $attrtag,int $index,?View $view=NULL,bool $edit=FALSE,bool $editable=FALSE,bool $new=FALSE,bool $updated=FALSE,?Template $template=NULL): View
	{
		return parent::render(
			attrtag: $attrtag,
			index: $index,
			view: view('components.attribute.value.samba.password'),
			edit: $edit,
			editable: $editable,
			new: $new,
			updated: $updated,
			template: $template);
	}

	public function render_item_old(string $dotkey): ?string
	{
		return Password::obfuscate;
	}

	public function render_item_new(string $dotkey): ?string
	{
		return Password::obfuscate;
	}

	protected function setValuesHelper(Collection $values): Collection
	{
		$processed = collect();

		// If the attr tags are the same value as the md5 tag, then nothing has changed
		foreach ($this->keys as $key) {
			foreach ($values->get($key,[]) as $index => $value) {
				$helper = $values->dot()->get($key.Entry::TAG_HELPER.'.'.$index);

				$processed->put($key.'.'.$index,
					($value && $helper)
						? self::hash_id($helper)->encode($value)
						: $value);
			}
		}

		return $processed->undot();
	}
}