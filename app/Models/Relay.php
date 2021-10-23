<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Relay extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'description',
        'webhook_type',
        'webhook_url',
        'secret',
        'status',
        'user_id'
    ];

    public function getEndpointAttribute()
    {
        return route('relay', ['id' => $this->id, 'key' => config('chismosa.key')]);
    }

    public function logs()
    {
        return $this->hasMany(RelayLog::class);
    }
}
