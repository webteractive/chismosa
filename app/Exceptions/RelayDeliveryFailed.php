<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\ConnectionException;

/**
 * A delivery failure that is safe to show: it never carries the destination
 * URL, which holds the webhook's credentials and appears in connection errors.
 */
class RelayDeliveryFailed extends Exception
{
    public static function from(RequestException|ConnectionException $exception): self
    {
        $message = $exception instanceof RequestException
            ? "The destination rejected the message with HTTP {$exception->response->status()}."
            : 'The destination could not be reached.';

        return new self($message, previous: $exception);
    }
}
