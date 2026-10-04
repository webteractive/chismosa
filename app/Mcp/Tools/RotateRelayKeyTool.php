<?php

namespace App\Mcp\Tools;

use App\Models\Relay;
use App\Models\RelayKey;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\ResponseFactory;
use App\Support\CredentialReveal;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Description;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;

#[Name('rotate-relay-key')]
#[Description('Replace the relay key with a newly generated one. Every relay endpoint URL changes at once and the old URLs stop working, so each sending service must be given its new endpoint. The response holds a link to a page showing the new endpoints, not the key itself.')]
#[IsDestructive]
class RotateRelayKeyTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        RelayKey::rotate();

        return Response::structured([
            'rotated' => true,
            'relays_affected' => Relay::count(),
            'credentials_link' => CredentialReveal::create($request->user()),
        ]);
    }

    /**
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
