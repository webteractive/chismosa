<?php

namespace App\Mcp\Tools;

use App\Models\RelayLog;
use App\Support\Relayer;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Description;
use Illuminate\Contracts\JsonSchema\JsonSchema;

#[Name('resend-relay-log')]
#[Description('Queue a logged webhook payload to be delivered to its relay\'s destination again, formatted the same way as when it arrived. No new log is created. Only do this when the user asks for it.')]
class ResendRelayLogTool extends Tool
{
    public function handle(Request $request): Response
    {
        $validated = $request->validate(['id' => ['required', 'integer']]);

        $log = RelayLog::with('relay')->find($validated['id']);

        if (! $log) {
            return Response::error("Relay log {$validated['id']} was not found.");
        }

        $relay = $log->relay;

        if (! $relay) {
            return Response::error("Relay log {$log->id} belongs to a relay that no longer exists.");
        }

        if (! Relayer::make($relay)->withPayload($log->payload)->notify()) {
            return Response::error("Relay {$relay->id} receives {$relay->type} webhooks, which have no outgoing message to resend.");
        }

        return Response::text("Queued relay log {$log->id} for delivery through relay {$relay->id} ({$relay->name}).");
    }

    /**
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('The relay log id.')->required(),
        ];
    }
}
