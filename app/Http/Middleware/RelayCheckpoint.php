<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Relay;
use App\Models\RelayKey;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RelayCheckpoint
{
    public function handle(Request $request, Closure $next): Response
    {
        $relay = Relay::find($request->route('id'));

        if (! $relay) {
            $this->logAndAbort(__('Relay :id not found.', ['id' => $request->route('id')]));
        }

        if (! $relay->isActive()) {
            $this->logAndAbort(__('Relay :id is inactive.', ['id' => $relay->id]));
        }

        $storedKey = RelayKey::current();

        if (! $storedKey || ! hash_equals($storedKey, (string) $request->route('key'))) {
            $this->logAndAbort('Relay not authorized.');
        }

        return $next($request);
    }

    protected function logAndAbort(string $message): never
    {
        logger()->info(__CLASS__.': '.$message);
        abort(404);
    }
}
