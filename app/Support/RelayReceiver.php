<?php

namespace App\Support;

use App\Models\Relay;

class RelayReceiver
{
    protected Relay $relay;

    public function __construct(int|string $relayId)
    {
        $this->relay = Relay::findOrFail($relayId);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function handle(array $payload): void
    {
        Relayer::make($this->relay)
            ->withPayload($payload)
            ->log()
            ->notify();
    }
}
