<?php

namespace App\Support;

use App\Models\Relay;
use App\Models\RelayLog;
use Illuminate\Support\Facades\Http;

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
