<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

use App\Traits\SetState;

class CertificateIsBinary implements ValidationRule
{
	use SetState;

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
				\Log::error('CIB: openssl_x509_read failed with :'.$e->getMessage());
				$fail(__('This is not a valid certificate: '.$e->getMessage()));
				break;
			}
		}
    }
}
