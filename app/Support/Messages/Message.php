<?php

namespace App\Support\Messages;

use App\Models\Relay;
use Illuminate\Support\Arr;

abstract class Message
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(protected Relay $relay, protected array $payload) {}

    /**
     * Deliver the message, throwing when the destination rejects it.
     */
    abstract public function send(): void;

    public function payload(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->payload, $key, $default);
    }
}
