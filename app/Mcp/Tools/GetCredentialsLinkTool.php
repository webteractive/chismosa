<?php

namespace App\Mcp\Tools;

use App\Models\Relay;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\ResponseFactory;
use App\Support\CredentialReveal;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Description;
use Illuminate\Contracts\JsonSchema\JsonSchema;

#[Name('get-credentials-link')]
#[Description('Get a short-lived link to a page that shows credentials. With a relay id the page shows that relay\'s endpoint URL and destination webhook URL; without one it shows the relay key and every relay\'s endpoint URL. Give the link to the user: it needs the admin sign-in, works only for the user who asked, and stops working once the page is closed or it expires.')]
class GetCredentialsLinkTool extends Tool
{
    public function handle(Request $request): Response|ResponseFactory
    {
        $validated = $request->validate(['relay_id' => ['nullable', 'integer']]);

        $relay = null;

        if (isset($validated['relay_id'])) {
            $relay = Relay::find($validated['relay_id']);

            if (! $relay) {
                return Response::error("Relay {$validated['relay_id']} was not found.");
            }
        }

        return Response::structured(CredentialReveal::create($request->user(), $relay));
    }

    /**
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'relay_id' => $schema->integer()->description('The relay whose credentials to show. Leave out for the relay key and every endpoint.'),
        ];
    }
}
