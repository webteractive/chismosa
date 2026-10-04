<?php

namespace App\Mcp\Tools;

use App\Models\Relay;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Illuminate\Validation\Rule;
use Laravel\Mcp\ResponseFactory;
use App\Support\CredentialReveal;
use App\Mcp\Concerns\PresentsRecords;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Description;
use Illuminate\Contracts\JsonSchema\JsonSchema;

#[Name('create-relay')]
#[Description('Create a relay owned by the signed-in user unless another user id is given. The response holds a link to a page showing the endpoint to give the sending service, not the endpoint itself. Give the link to the user: it needs the admin sign-in, works only for the user who asked, and stops working once the page is closed or it expires.')]
class CreateRelayTool extends Tool
{
    use PresentsRecords;

    public function handle(Request $request): ResponseFactory
    {
        $services = array_keys(config('chismosa.services', []));

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in($services)],
            'description' => ['nullable', 'string', 'max:65535'],
            'webhook_type' => ['required', Rule::in($services)],
            'webhook_url' => ['required', 'url', 'max:65535'],
            'active' => ['nullable', 'boolean'],
            'user_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
        ]);

        $relay = Relay::create([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'description' => $validated['description'] ?? null,
            'webhook_type' => $validated['webhook_type'],
            'webhook_url' => $validated['webhook_url'],
            'status' => (int) ($validated['active'] ?? true),
            'user_id' => $validated['user_id'] ?? $request->user()->id,
        ]);

        return Response::structured([
            ...$this->presentRelay($relay),
            'credentials_link' => CredentialReveal::create($request->user(), $relay),
        ]);
    }

    /**
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        $services = array_keys(config('chismosa.services', []));

        return [
            'name' => $schema->string()->description('A name for the relay.')->required(),
            'type' => $schema->string()->enum($services)->description('The service that sends webhooks to this relay.')->required(),
            'description' => $schema->string()->description('What the relay is for.'),
            'webhook_type' => $schema->string()->enum($services)->description('The kind of destination messages are forwarded to.')->required(),
            'webhook_url' => $schema->string()->description('The destination webhook URL messages are forwarded to.')->required(),
            'active' => $schema->boolean()->description('Whether the relay accepts webhooks. Defaults to true.'),
            'user_id' => $schema->integer()->description('The owning user. Defaults to the signed-in user.'),
        ];
    }
}
