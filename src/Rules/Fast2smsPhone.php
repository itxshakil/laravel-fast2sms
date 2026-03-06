<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class Fast2smsPhone implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param Closure(string, ?string=):PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $numbers = explode(',', (string) $value);

        foreach ($numbers as $number) {
            $number = mb_trim($number);

            if (! preg_match('/^[6-9]\d{9}$/', $number)) {
                $fail('The :attribute must be a valid 10-digit Indian mobile number.');

                return;
            }
        }
    }
}
