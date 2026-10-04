<?php

namespace App\Jobs;

use App\Models\Relay;
use App\Support\Relayer;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Queue\Attributes\Backoff;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\Attributes\DeleteWhenMissingModels;

#[Tries(3)]
#[Backoff([10, 60])]
#[DeleteWhenMissingModels]
class SendRelayMessage implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(public Relay $relay, public array $payload) {}

    public function handle(): void
    {
        Relayer::make($this->relay)
            ->withPayload($this->payload)
            ->send();
    }
}
