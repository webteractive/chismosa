<?php

namespace App\Mcp\Tools;

use App\Models\Relay;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Illuminate\Validation\Rule;
use Laravel\Mcp\ResponseFactory;
use App\Rules\DestinationWebhookUrl;
use App\Mcp\Concerns\PresentsRecords;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Description;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;

#[Name('update-relay')]
#[Description('Update a relay. Only the fields given are changed. Set active to false to stop it accepting webhooks, or true to resume.')]
#[IsIdempotent]
class UpdateRelayTool extends Tool
{
    use PresentsRecords;

    public function handle(Request $request): Response|ResponseFactory
    {
        $sources = array_keys(config('chismosa.sources', []));
        $destinations = array_keys(config('chismosa.destinations', []));

        $validated = $request->validate([
            'id' => ['required', 'integer'],
            'name' => ['sometimes', 'string', 'max:255'],
            'type' => ['sometimes', Rule::in($sources)],
            'description' => ['sometimes', 'nullable', 'string', 'max:65535'],
            'webhook_type' => ['sometimes', Rule::in($destinations)],
            'webhook_url' => ['sometimes', 'url', 'max:65535', new DestinationWebhookUrl],
            'active' => ['sometimes', 'boolean'],
            'user_id' => ['sometimes', 'integer', Rule::exists('users', 'id')],
        ]);

        $relay = Relay::find($validated['id']);

        if (! $relay) {
            return Response::error("Relay {$validated['id']} was not found.");
        }

        $attributes = collect($validated)->except(['id', 'active'])->all();

        if (array_key_exists('active', $validated)) {
            $attributes['status'] = (int) $validated['active'];
        }

        $relay->update($attributes);

        return Response::structured($this->presentRelay($relay));
    }

    /**
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        $sources = array_keys(config('chismosa.sources', []));
        $destinations = array_keys(config('chismosa.destinations', []));

        return [
            'id' => $schema->integer()->description('The relay id.')->required(),
            'name' => $schema->string()->description('A name for the relay.'),
            'type' => $schema->string()->enum($sources)->description('The service that sends webhooks to this relay.'),
            'description' => $schema->string()->description('What the relay is for.'),
            'webhook_type' => $schema->string()->enum($destinations)->description('The kind of destination messages are forwarded to.'),
            'webhook_url' => $schema->string()->description('The destination webhook URL messages are forwarded to. Must be '.DestinationWebhookUrl::requirement().'.'),
            'active' => $schema->boolean()->description('Whether the relay accepts webhooks.'),
            'user_id' => $schema->integer()->description('The owning user.'),
        ];
    }
}
