<?php

namespace App\Mcp\Tools;

use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Description;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;

#[Name('delete-user')]
#[Description('Permanently delete an admin account. You cannot delete the account you are signed in as, or one that still owns relays; give those relays to another user with update-relay first.')]
#[IsDestructive]
class DeleteUserTool extends Tool
{
    public function handle(Request $request): Response
    {
        $validated = $request->validate(['id' => ['required', 'integer']]);

        $user = User::find($validated['id']);

        if (! $user) {
            return Response::error("User {$validated['id']} was not found.");
        }

        if ($user->is($request->user())) {
            return Response::error('You cannot delete the account you are signed in as.');
        }

        $ownedRelays = $user->relays()->count();

        if ($ownedRelays > 0) {
            return Response::error("User {$user->id} still owns {$ownedRelays} relays. Give them to another user with update-relay first.");
        }

        $user->delete();

        return Response::text("Deleted user {$user->id} ({$user->email}).");
    }

    /**
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('The user id.')->required(),
        ];
    }
}
