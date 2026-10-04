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

#[Name('list-relays')]
#[Description('List relays, newest first, optionally filtered by name or by whether they are active. Credentials are never included.')]
#[IsReadOnly]
class ListRelaysTool extends Tool
{
    use PresentsRecords;

    public function handle(Request $request): ResponseFactory
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'active' => ['nullable', 'boolean'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $relays = Relay::query()
            ->when($validated['search'] ?? null, fn ($query, string $search) => $query->whereLike('name', "%{$search}%"))
            ->when(isset($validated['active']), fn ($query) => $query->where('status', (int) $validated['active']))
            ->latest('id')
            ->paginate(perPage: $validated['per_page'] ?? 25, page: $validated['page'] ?? 1);

        return Response::structured($this->presentPage($relays, $this->presentRelay(...)));
    }

    /**
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string()->description('Only relays whose name contains this text.'),
            'active' => $schema->boolean()->description('Only active (true) or inactive (false) relays.'),
            'page' => $schema->integer()->description('Page number, starting at 1.'),
            'per_page' => $schema->integer()->description('Relays per page, 1 to 100. Defaults to 25.'),
        ];
    }
}
