<?php

namespace App\Mcp\Tools;

use App\Models\Relay;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Illuminate\Support\Facades\DB;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Description;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;

#[Name('delete-relay')]
#[Description('Permanently delete a relay together with every payload it has logged. Its endpoint stops accepting webhooks. This cannot be undone.')]
#[IsDestructive]
class DeleteRelayTool extends Tool
{
    public function handle(Request $request): Response
    {
        $validated = $request->validate(['id' => ['required', 'integer']]);

        $relay = Relay::find($validated['id']);

        if (! $relay) {
            return Response::error("Relay {$validated['id']} was not found.");
        }

        $deletedLogs = DB::transaction(function () use ($relay): int {
            $deletedLogs = $relay->logs()->delete();
            $relay->delete();

            return $deletedLogs;
        });

        return Response::text("Deleted relay {$relay->id} ({$relay->name}) and {$deletedLogs} logged payloads.");
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
