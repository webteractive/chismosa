<?php

namespace App\Support;

use App\Models\Relay;
use App\Models\RelayLog;
use App\Jobs\SendRelayMessage;
use App\Support\Messages\Forge;
use App\Support\Messages\Message;
use App\Exceptions\RelayDeliveryFailed;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\ConnectionException;

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
     * Queue the outbound message, returning false when the relay's type has none.
     */
    public function notify(): bool
    {
        if (! $this->message()) {
            return false;
        }

        SendRelayMessage::dispatch($this->relay, $this->payload);

        return true;
    }

    /**
     * Send the outbound message immediately.
     */
    public function send(): void
    {
        $this->message()?->send();
    }

    /**
     * Send the type's sample message straight to the destination without logging it,
     * returning false when the relay's type has no message.
     *
     * @throws RelayDeliveryFailed
     */
    public function sendTest(): bool
    {
        $messageClass = $this->messageClass();

        if (! $messageClass) {
            return false;
        }

        try {
            (new $messageClass($this->relay, $messageClass::samplePayload()))->send();
        } catch (RequestException|ConnectionException $exception) {
            throw RelayDeliveryFailed::from($exception);
        }

        return true;
    }

    protected function message(): ?Message
    {
        $messageClass = $this->messageClass();

        return $messageClass ? new $messageClass($this->relay, $this->payload) : null;
    }

    /**
     * @return class-string<Message>|null
     */
    protected function messageClass(): ?string
    {
        return $this->messages[$this->relay->type] ?? null;
    }
}
