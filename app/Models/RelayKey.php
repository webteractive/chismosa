<?php

namespace App\Models;

use Illuminate\Support\Str;
use Database\Factories\RelayKeyFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RelayKey extends Model
{
    /** @use HasFactory<RelayKeyFactory> */
    use HasFactory;

    public const string CACHE_KEY = 'relay-key-current';

    protected $fillable = [
        'key',
    ];

    public static function current(): ?string
    {
        return cache()->remember(self::CACHE_KEY, now()->addMinutes(5), function () {
            return static::query()->first()?->key;
        });
    }

    public static function generate(): string
    {
        return Str::ulid().Str::ulid().Str::random(12);
    }

    /**
     * Save the relay key, which changes every relay's endpoint URL.
     */
    public static function store(string $key): void
    {
        static::query()->firstOrNew()->fill(['key' => $key])->save();

        cache()->forget(self::CACHE_KEY);
    }

    public static function rotate(): string
    {
        $key = static::generate();

        static::store($key);

        return $key;
    }
}
