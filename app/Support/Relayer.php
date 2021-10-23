<?php

namespace App\Support;

use App\Models\RelayLog;
use App\Support\Messages\Forge;

class Relayer
{
    protected $relay;
    protected $payload;

    protected $messages = [
        'forge' => Forge::class
    ];

    public function __construct($relay)
    {
        $this->relay = $relay;
    }

    public static function make($relay)
    {
        return new self($relay);
    }

    public function withPayload($payload)
    {
        $this->payload = $payload;
        return $this;
    }

    public function log()
    {
        RelayLog::create([
            'payload' => $this->payload,
            'relay_id' => $this->relay->id
        ]);

        return $this;
    }

    public function notify()
    {
        if ($message = ($this->messages[$this->relay->type] ?? null)) {
            (new $message($this->relay, $this->payload))
                ->send();
        }
    }
}
