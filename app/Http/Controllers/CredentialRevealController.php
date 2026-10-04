<?php

namespace App\Http\Controllers;

use App\Models\Relay;
use App\Models\RelayKey;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Support\CredentialReveal;

class CredentialRevealController extends Controller
{
    public function show(Request $request, string $reveal): Response
    {
        $pointer = CredentialReveal::find($reveal, $request->user());

        abort_if($pointer === null, 404);

        $isForOneRelay = (bool) $pointer['relay_id'];

        $relays = Relay::query()
            ->when($pointer['relay_id'], fn ($query, $relayId) => $query->whereKey($relayId))
            ->orderBy('name')
            ->get();

        abort_if($isForOneRelay && $relays->isEmpty(), 404);

        return response()
            ->view('credentials.show', [
                'reveal' => $reveal,
                'relays' => $relays,
                'relayKey' => $isForOneRelay ? null : RelayKey::current(),
                'showsWebhookUrls' => $isForOneRelay,
                'expiresAt' => $pointer['expires_at'],
            ])
            ->header('Cache-Control', 'no-store, private');
    }

    public function destroy(Request $request, string $reveal): Response
    {
        CredentialReveal::forget($reveal, $request->user());

        return response()->noContent();
    }
}
