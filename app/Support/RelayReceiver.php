<?php

namespace App\Support;

use App\Models\RelayLog;

class RelayReceiver
{
    protected $relayId;

    public function __construct($relayId)
    {
        $this->relayId = $relayId;
    }

    public function log($payload)
    {
        RelayLog::create([
            'payload' => $payload,
            'relay_id' => $this->relayId
        ]);
    }
}
