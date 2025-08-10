<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CertificateIsBinary implements ValidationRule
{
	// Required for artisan optimize
	public static function __set_state(array $array): self
	{
		return new self;
	}

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute,mixed $value,Closure $fail): void
    {
		foreach (collect($value)->dot() as $item) {

			try {
				openssl_x509_read($item);

			} catch (\ErrorException $e) {
				$fail(__('This is not a valid certificate: '.$e->getMessage()));
				return;
			}
		}
    }
}
