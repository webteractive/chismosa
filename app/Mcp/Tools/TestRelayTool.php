<?php

namespace App\Mcp\Tools;

use App\Models\Relay;
use App\Support\Relayer;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Illuminate\JsonSchema\Types\Type;
use App\Exceptions\RelayDeliveryFailed;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Description;
use Illuminate\Contracts\JsonSchema\JsonSchema;

#[Name('test-relay')]
#[Description('Send a sample message, marked as a test, straight to a relay\'s destination and report whether the destination accepted it. Nothing is logged. Use it to check that a relay\'s webhook URL works.')]
class TestRelayTool extends Tool
{
    public function handle(Request $request): Response
    {
        $validated = $request->validate(['id' => ['required', 'integer']]);

        $relay = Relay::find($validated['id']);

        if (! $relay) {
            return Response::error("Relay {$validated['id']} was not found.");
        }

        try {
            $sent = Relayer::make($relay)->sendTest();
        } catch (RelayDeliveryFailed $exception) {
            return Response::error("The test message for relay {$relay->id} failed. {$exception->getMessage()}");
        }

        if (! $sent) {
            return Response::error("Relay {$relay->id} receives {$relay->type} webhooks, which have no outgoing message to test.");
        }

        return Response::text("Sent a test message to the destination of relay {$relay->id} ({$relay->name}), and it was accepted.");
    }

    /**
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('The relay id.')->required(),
        ];
    }
}
