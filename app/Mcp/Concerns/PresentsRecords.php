<?php

namespace App\Mcp\Concerns;

use App\Models\User;
use App\Models\Relay;
use App\Models\RelayLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

trait PresentsRecords
{
    /**
     * Describe a relay without its credentials: the endpoint carries the
     * relay key and the webhook URL carries the destination's token.
     *
     * @return array<string, mixed>
     */
    protected function presentRelay(Relay $relay): array
    {
        return [
            'id' => $relay->id,
            'name' => $relay->name,
            'type' => $relay->type,
            'description' => $relay->description,
            'webhook_type' => $relay->webhook_type,
            'webhook_host' => parse_url((string) $relay->webhook_url, PHP_URL_HOST) ?: null,
            'active' => $relay->isActive(),
            'user_id' => $relay->user_id,
            'created_at' => $relay->created_at?->toIso8601String(),
            'updated_at' => $relay->updated_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function presentRelayLog(RelayLog $log): array
    {
        return [
            'id' => $log->id,
            'relay_id' => $log->relay_id,
            'payload' => $log->payload,
            'created_at' => $log->created_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function presentUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'created_at' => $user->created_at?->toIso8601String(),
        ];
    }

    /**
     * @param  callable(mixed): array<string, mixed>  $present
     * @return array{data: list<array<string, mixed>>, total: int, page: int, per_page: int, last_page: int}
     */
    protected function presentPage(LengthAwarePaginator $paginator, callable $present): array
    {
        return [
            'data' => array_map($present, $paginator->items()),
            'total' => $paginator->total(),
            'page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'last_page' => $paginator->lastPage(),
        ];
    }
}
