<?php

namespace App\Traits;

use App\Ldap\Entry;

/**
 * Determine if a value has changed by comparing its MD5 value
 */
trait MD5Updates
{
	/**
	 * Process values that belong to an attribute with MD5Trait
	 *
	 * If the $attrtag value equals the same value as the ${attrtag}TAG_MD5 value then the value hasnt changed
	 * If there is no $attrtag value, but there is an ${attrtag}TAG_MD5 value, then the value hasnt changed
	 * If the ($attrtag) value is empty, its been removed as normal
	 *
	 * If the $attrtag value has another value, then (eg: ${attrtag}TAG_HELPER) then pass the value (if not empty)
	 * to the helper. This should be done by the attribute::class of the entry, so parent::setValue() should be called
	 * first before processing the TAG_HELPER.
	 *
	 * @param array $values
	 * @return void
	 */
	public function setValues(array $values): void
	{
		$processed = collect();
		$vals = collect($values);

		// If the attr tags are the same value as the md5 tag, then nothing has changed
		foreach ($this->keys as $key) {
			if ($vals->has($key))
				foreach ($vals->get($key) as $index => $value) {
					$md5value = $vals->dot()->get($key.Entry::TAG_MD5.'.'.$index);

					// If the md5 value matches, we dont need to use the helper, if there is one.
					if ($md5value && ($value === $md5value)) {
						$processed->put($key.'.'.$index,$this->values_old->dot()->get($key.'.'.$index));

					} else {
						$processed->put($key.'.'.$index,$value);

						// If there is a helper, we need to set it, so that it gets called
						if ($x=$vals->dot()->get($key.Entry::TAG_HELPER.'.'.$index))
							$processed->put($key.Entry::TAG_HELPER.'.'.$index,$x);
					}
				}

			// We dont have an new values
			else {
				// If the old value matches the MD5, copy that.
				foreach ($vals->get($key.Entry::TAG_MD5,[]) as $index => $value) {
					$old = $this->values_old->dot()->get($key.'.'.$index);

					if ($old && (md5($old) === $value))
						$processed->put($key.'.'.$index,$old);
				}
			}
		}

		parent::setValues($processed ? $processed->undot()->toArray() : $values);
	}
}