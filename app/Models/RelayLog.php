<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RelayLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'payload',
        'relay_id'
    ];

    protected $casts = [
        'payload' => 'array'
    ];

    public function relay()
    {
        return $this->belongsTo(Relay::class);
    }
}
