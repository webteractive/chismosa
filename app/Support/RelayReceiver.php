<?php

namespace App\Support;

use App\Models\Relay;

class RelayReceiver
{
    protected $relay;

    public function __construct($relayId)
    {
        $this->relay = Relay::find($relayId);
    }

    public function handle($payload)
    {
        Relayer::make($this->relay)
            ->withPayload($payload)
            ->log()
            ->notify();
    }
}
