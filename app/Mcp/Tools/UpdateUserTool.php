<?php

namespace App\Mcp\Tools;

use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Illuminate\Validation\Rule;
use Laravel\Mcp\ResponseFactory;
use App\Mcp\Concerns\PresentsRecords;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Description;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;

#[Name('update-user')]
#[Description('Update an admin account. Only the fields given are changed.')]
#[IsIdempotent]
class UpdateUserTool extends Tool
{
    use PresentsRecords;

    public function handle(Request $request): Response|ResponseFactory
    {
        $validated = $request->validate([
            'id' => ['required', 'integer'],
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($request->get('id'))],
            'password' => ['sometimes', 'string', 'min:8', 'max:255'],
        ]);

        $user = User::find($validated['id']);

        if (! $user) {
            return Response::error("User {$validated['id']} was not found.");
        }

        $user->update(collect($validated)->except('id')->all());

        return Response::structured($this->presentUser($user));
    }

    /**
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('The user id.')->required(),
            'name' => $schema->string()->description('The person\'s name.'),
            'email' => $schema->string()->description('The email address they sign in with.'),
            'password' => $schema->string()->description('A new password, at least 8 characters.'),
        ];
    }
}
