<?php

namespace App\Support\Messages;

use Illuminate\Support\Arr;

abstract class Message
{
    protected $relay;

    protected $payload;

    public function __construct($relay, $payload)
    {
        $this->relay = $relay;
        $this->payload = $payload;
    }

    abstract public function send();

    public function payload($key, $default = null)
    {
        return Arr::get($this->payload, $key, $default);
    }
}
