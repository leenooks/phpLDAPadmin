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
		foreach ($this->_values_old->dot()->keys()->merge($this->_values->dot()->keys())->unique() as $dotkey)
			if ((Arr::get($this->_values_old->dot(),$dotkey) !== Arr::get($this->_values->dot(),$dotkey))
				&& (md5(Arr::get($this->_values_old->dot(),$dotkey)) !== Arr::get($this->_values->dot(),$dotkey)))
				return TRUE;

		return FALSE;
	}
}