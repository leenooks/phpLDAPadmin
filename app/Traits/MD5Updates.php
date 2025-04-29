<?php

/**
 * Determine if a value has changed by comparing its MD5 value
 */
namespace App\Traits;

use Illuminate\Support\Arr;

trait MD5Updates
{
	public function isDirty(): bool
	{
		foreach ($this->values_old->dot()->keys()->merge($this->values->dot()->keys())->unique() as $dotkey)
			if ((Arr::get($this->values_old->dot(),$dotkey) !== Arr::get($this->values->dot(),$dotkey))
				&& (md5(Arr::get($this->values_old->dot(),$dotkey)) !== Arr::get($this->values->dot(),$dotkey)))
				return TRUE;

		return FALSE;
	}
}