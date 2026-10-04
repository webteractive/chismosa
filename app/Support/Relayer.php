<?php

namespace App\Support;

use App\Models\Relay;
use App\Models\RelayLog;
use App\Jobs\SendRelayMessage;
use App\Support\Messages\Forge;
use App\Support\Messages\Message;

class Relayer
{
    /**
     * @var array<string, mixed>
     */
    protected array $payload = [];

    /**
     * @var array<string, class-string<Message>>
     */
    protected array $messages = [
        'forge' => Forge::class,
    ];

    public function __construct(protected Relay $relay) {}

    public static function make(Relay $relay): self
    {
        return new self($relay);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function withPayload(array $payload): self
    {
        $this->payload = $payload;

        return $this;
    }

    public function log(): self
    {
        RelayLog::create([
            'payload' => $this->payload,
            'relay_id' => $this->relay->id,
        ]);

        return $this;
    }

    /**
     * Queue the outbound message when the relay's type has one.
     */
    public function notify(): void
    {
        if ($this->message()) {
            SendRelayMessage::dispatch($this->relay, $this->payload);
        }
    }

    /**
     * Send the outbound message immediately.
     */
    public function send(): void
    {
        $this->message()?->send();
    }

    protected function message(): ?Message
    {
        $message = $this->messages[$this->relay->type] ?? null;

        return $message ? new $message($this->relay, $this->payload) : null;
    }
}
