<?php

namespace App\Traits;

trait SetState
{
	// Required for artisan optimize
	public static function __set_state(array $array): self
	{
		return new self;
	}
}