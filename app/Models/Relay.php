<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Relay extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'description',
        'webhook_type',
        'webhook_url',
        'status',
        'user_id',
    ];

    public function getEndpointAttribute(): ?string
    {
        $key = RelayKey::current();

        if (! $key) {
            return null;
        }

        return route('relay', ['id' => $this->id, 'key' => $key]);
    }

    public function isActive(): bool
    {
        return (bool) $this->status;
    }

    public function logs(): HasMany
    {
        return $this->hasMany(RelayLog::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
