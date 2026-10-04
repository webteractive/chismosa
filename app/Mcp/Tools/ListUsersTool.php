<?php

namespace App\Mcp\Tools;

use App\Models\User;
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

#[Name('list-users')]
#[Description('List admin accounts, optionally filtered by name or email.')]
#[IsReadOnly]
class ListUsersTool extends Tool
{
    use PresentsRecords;

    public function handle(Request $request): ResponseFactory
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $users = User::query()
            ->when($validated['search'] ?? null, fn ($query, string $search) => $query->whereAny(['name', 'email'], 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(perPage: $validated['per_page'] ?? 25, page: $validated['page'] ?? 1);

        return Response::structured($this->presentPage($users, $this->presentUser(...)));
    }

    /**
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string()->description('Only accounts whose name or email contains this text.'),
            'page' => $schema->integer()->description('Page number, starting at 1.'),
            'per_page' => $schema->integer()->description('Accounts per page, 1 to 100. Defaults to 25.'),
        ];
    }
}
