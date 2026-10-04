<?php

namespace App\Mcp\Tools;

use App\Models\Relay;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\ResponseFactory;
use App\Mcp\Concerns\PresentsRecords;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Description;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Name('get-relay')]
#[Description('Get one relay by id, with how many payloads it has logged. Credentials are never included; use get-credentials-link for its endpoint or webhook URL.')]
#[IsReadOnly]
class GetRelayTool extends Tool
{
    use PresentsRecords;

    public function handle(Request $request): Response|ResponseFactory
    {
        $validated = $request->validate(['id' => ['required', 'integer']]);

        $relay = Relay::query()->withCount('logs')->find($validated['id']);

        if (! $relay) {
            return Response::error("Relay {$validated['id']} was not found.");
        }

        return Response::structured([
            ...$this->presentRelay($relay),
            'logs_count' => $relay->logs_count,
        ]);
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
