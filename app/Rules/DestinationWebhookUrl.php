<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

/**
 * Only allow https URLs on a known destination's host, so a relay can never
 * be pointed at an internal address the server would then post to.
 */
class DestinationWebhookUrl implements ValidationRule
{
    /**
     * The hosts a destination webhook URL may point at.
     *
     * @return list<string>
     */
    public static function hosts(): array
    {
        return array_column(config('chismosa.destinations', []), 'host');
    }

    /**
     * What a valid URL looks like, e.g. "an https URL on chat.googleapis.com".
     */
    public static function requirement(): string
    {
        return 'an https URL on '.implode(' or ', self::hosts());
    }

    /**
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)
            || parse_url($value, PHP_URL_SCHEME) !== 'https'
            || ! in_array(parse_url($value, PHP_URL_HOST), self::hosts(), true)) {
            $fail('The :attribute must be '.self::requirement().'.');
        }
    }
}
