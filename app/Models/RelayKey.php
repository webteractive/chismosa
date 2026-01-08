<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RelayKey extends Model
{
    /** @use HasFactory<\Database\Factories\RelayKeyFactory> */
    use HasFactory;

    protected $fillable = [
        'key',
    ];

    public static function current(): ?string
    {
        return static::query()->first()?->key;
    }
}
