<?php

use App\Models\User;
use App\Models\Relay;
use App\Models\RelayLog;
use App\Mcp\Tools\GetRelayLogTool;
use App\Mcp\Servers\ChismosaServer;
use App\Mcp\Tools\ListRelayLogsTool;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('list-relay-logs', function () {
    it('returns only the logs of the given relay, newest first', function () {
        $relay = Relay::factory()->create();
        $older = RelayLog::factory()->create(['relay_id' => $relay->id]);
        $newer = RelayLog::factory()->create(['relay_id' => $relay->id, 'payload' => ['status' => 'success']]);
        RelayLog::factory()->create();

        ChismosaServer::actingAs(User::factory()->create())
            ->tool(ListRelayLogsTool::class, ['relay_id' => $relay->id])
            ->assertOk()
            ->assertStructuredContent(fn (AssertableJson $json) => $json
                ->where('total', 2)
                ->where('data.0.id', $newer->id)
                ->where('data.0.payload', ['status' => 'success'])
                ->where('data.1.id', $older->id)
                ->etc());
    });

    it('rejects more than 100 logs per page', function () {
        ChismosaServer::actingAs(User::factory()->create())
            ->tool(ListRelayLogsTool::class, ['per_page' => 101])
            ->assertHasErrors(['The per page field must not be greater than 100.']);
    });
});

describe('get-relay-log', function () {
    it('returns the logged payload', function () {
        $log = RelayLog::factory()->create(['payload' => ['status' => 'failed']]);

        ChismosaServer::actingAs(User::factory()->create())
            ->tool(GetRelayLogTool::class, ['id' => $log->id])
            ->assertOk()
            ->assertStructuredContent(fn (AssertableJson $json) => $json
                ->where('id', $log->id)
                ->where('relay_id', $log->relay_id)
                ->where('payload', ['status' => 'failed'])
                ->etc());
    });

    it('returns an error when the log does not exist', function () {
        ChismosaServer::actingAs(User::factory()->create())
            ->tool(GetRelayLogTool::class, ['id' => 999])
            ->assertHasErrors(['Relay log 999 was not found.']);
    });
});
