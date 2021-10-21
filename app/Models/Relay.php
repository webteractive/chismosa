<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Relay extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'webhook_url',
        'user_id'
    ];

    public function getEndpointAttribute()
    {
    return route('relay', ['id' => $this->id]);
    }
}
