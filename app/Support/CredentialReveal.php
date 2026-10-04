<?php

namespace App\Support;

use App\Models\User;
use App\Models\Relay;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Cache;

/**
 * A short-lived pointer to credentials that only the user who asked for it
 * can open in the browser. It stores no secret itself, only what to show.
 */
class CredentialReveal
{
    public const int LIFETIME_IN_MINUTES = 10;

    /**
     * Create a reveal for one relay's credentials, or for the relay key
     * and every relay endpoint when no relay is given.
     *
     * @return array{url: string, expires_at: string}
     */
    public static function create(User $user, ?Relay $relay = null): array
    {
        $token = (string) Str::ulid();
        $expiresAt = now()->addMinutes(self::LIFETIME_IN_MINUTES);

        Cache::put(self::cacheKey($token), [
            'user_id' => $user->id,
            'relay_id' => $relay?->id,
            'expires_at' => $expiresAt->toIso8601String(),
        ], $expiresAt);

        return [
            'url' => URL::temporarySignedRoute('credentials.show', $expiresAt, ['reveal' => $token]),
            'expires_at' => $expiresAt->toIso8601String(),
        ];
    }

    /**
     * @return array{relay_id: int|null, expires_at: Carbon}|null
     */
    public static function find(string $token, User $user): ?array
    {
        $reveal = Cache::get(self::cacheKey($token));

        if (! $reveal || $reveal['user_id'] !== $user->id) {
            return null;
        }

        return [
            'relay_id' => $reveal['relay_id'],
            'expires_at' => Carbon::parse($reveal['expires_at']),
        ];
    }

    public static function forget(string $token, User $user): void
    {
        if (self::find($token, $user)) {
            Cache::forget(self::cacheKey($token));
        }
    }

    protected static function cacheKey(string $token): string
    {
        return "credential-reveal:{$token}";
    }
}
