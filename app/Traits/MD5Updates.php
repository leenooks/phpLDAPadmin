<?php

namespace App\Traits;

use App\Ldap\Entry;

/**
 * Determine if a value has changed by comparing its MD5 value
 */
trait MD5Updates
{
	/**
	 * Determine if an MD5 attribute (normally binary) has changed.
	 *
	 * If an attribute:
	 * + has a self::values(['<tag>_md5_'=>value]) - compare that md5 has to the original value (with the same index).
	 *   Match = no change with old value
	 * + does not have a self::values(['<tag>_md5_'=>value]) - compare the raw values as normal
	 */
	public function isDirty(): bool
	{
		$processed = collect();
		$md5keys = $this->values->keys()
			->filter(fn($item)=>\Str::endsWith($item,Entry::TAG_MD5));

		\Log::debug(sprintf('MD5:Checking MD5 keys [%s] for attribute [%s]',$md5keys->join(','),$this->name));

		if ($md5keys->count()) {
			foreach ($md5keys as $md5key) {
				$orig = \Str::replace(Entry::TAG_MD5,'',$md5key);

				// Different number of entries
				if (count(array_filter($this->values->get($md5key))) !== count($this->values_old->get($orig)))
					return TRUE;

				// Same number of entries
				foreach ($this->values_old->get($orig) as $index => $value)
					if (md5($value) !== $this->values->dot()->get($md5key.'.'.$index))
						return TRUE;

				\Log::debug(sprintf('MD5:MD5 values unchanged for index [%s] for attribute [%s]',$orig,$this->name));

				$processed->push($orig);
			}
		}

		// Process the remaining
		foreach ($this->values_old->except($processed)->dot()->filter()->keys()->merge($this->values->except($md5keys)->dot()->filter()->keys())->unique() as $dotkey) {
			\Log::debug(sprintf('MD5:Checking normal key [%s] for attribute [%s]',$dotkey,$this->name),['old'=>$this->values_old->dot()->get($dotkey),'new'=>$this->values->dot()->get($dotkey)]);

			if ($this->values_old->dot()->get($dotkey) !== $this->values->dot()->get($dotkey))
				return TRUE;
		}

		// No changes
		return FALSE;
	}

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
		$processed = [];
		$vals = collect($values);

		// If the attr tags are the same value as the md5 tag, then nothing has changed
		foreach ($this->keys as $key) {
			if ($vals->has($key))
				foreach ($vals->get($key) as $index => $value) {
					$md5value = $vals->dot()->get($key.Entry::TAG_MD5.'.'.$index);

					if ($md5value) {
						$processed[$key.'.'.$index] =
							($value === $md5value)
								? $this->values_old->dot()->get($key.'.'.$index)
								: $value;
					}
				}

			// We dont have an new values
			else {
				// If the old value matches the MD5, copy that.
				foreach ($vals->get($key.Entry::TAG_MD5,[]) as $index => $value) {
					$old = $this->values_old->dot()->get($key.'.'.$index);

					if ($old && (md5($old) === $value))
						$processed[$key.'.'.$index] = base64_encode($old);
				}
			}
		}

		$helpers = $this->getHelpers($values);

		parent::setValues($processed ? collect($values)->only($helpers)->dot()->merge($processed)->undot()->toArray() : $values);
	}
}