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

#[Name('create-user')]
#[Description('Create an admin account. The account can sign in to the admin panel and use this server.')]
class CreateUserTool extends Tool
{
    use PresentsRecords;

    public function handle(Request $request): ResponseFactory
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'max:255'],
        ]);

        return Response::structured($this->presentUser(User::create($validated)));
    }

    /**
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('The person\'s name.')->required(),
            'email' => $schema->string()->description('The email address they sign in with.')->required(),
            'password' => $schema->string()->description('Their password, at least 8 characters.')->required(),
        ];
    }
}
