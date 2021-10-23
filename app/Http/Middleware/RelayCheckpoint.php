<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Relay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RelayCheckpoint
{
    public function handle(Request $request, Closure $next)
    {
        if ($relay = Relay::find($request->id)) {
            if (Hash::check(config('chismosa.key'), $relay->secret)) {
                return $next($request);
            }

            $this->logAndAbort('Relay not authorized.');
        }

        $this->logAndAbort(__('Relay :id not found.', ['id' => $request->id]));
    }

    public function logAndAbort($message)
    {
        logger()->info(__CLASS__ . ': ' . $message);
        abort(404);
    }
}
