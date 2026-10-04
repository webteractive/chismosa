<?php

use App\Models\User;
use App\Models\Relay;
use App\Mcp\Tools\ListUsersTool;
use App\Mcp\Tools\CreateUserTool;
use App\Mcp\Tools\DeleteUserTool;
use App\Mcp\Tools\UpdateUserTool;
use App\Mcp\Servers\ChismosaServer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('list-users', function () {
    it('returns the accounts matching the search without their password', function () {
        $actor = User::factory()->create(['name' => 'Actor', 'email' => 'actor@example.com']);
        $match = User::factory()->create(['name' => 'Ana Reyes', 'email' => 'ana@example.com']);

        ChismosaServer::actingAs($actor)
            ->tool(ListUsersTool::class, ['search' => 'ana@'])
            ->assertOk()
            ->assertStructuredContent(fn (AssertableJson $json) => $json
                ->where('total', 1)
                ->where('data.0.id', $match->id)
                ->where('data.0.email', 'ana@example.com')
                ->missing('data.0.password')
                ->etc());
    });
});

describe('create-user', function () {
    it('creates an account with a hashed password that is not returned', function () {
        ChismosaServer::actingAs(User::factory()->create())
            ->tool(CreateUserTool::class, [
                'name' => 'Ana Reyes',
                'email' => 'ana@example.com',
                'password' => 'correct-horse',
            ])
            ->assertOk()
            ->assertDontSee('correct-horse');

        $user = User::query()->where('email', 'ana@example.com')->sole();

        expect($user->name)->toBe('Ana Reyes')
            ->and(Hash::check('correct-horse', $user->password))->toBeTrue();
    });

    it('rejects an email that is already taken', function () {
        User::factory()->create(['email' => 'ana@example.com']);

        ChismosaServer::actingAs(User::factory()->create())
            ->tool(CreateUserTool::class, [
                'name' => 'Ana Reyes',
                'email' => 'ana@example.com',
                'password' => 'correct-horse',
            ])
            ->assertHasErrors(['The email has already been taken.']);
    });

    it('rejects a password shorter than 8 characters', function () {
        ChismosaServer::actingAs(User::factory()->create())
            ->tool(CreateUserTool::class, [
                'name' => 'Ana Reyes',
                'email' => 'ana@example.com',
                'password' => 'short',
            ])
            ->assertHasErrors(['The password field must be at least 8 characters.']);

        $this->assertDatabaseMissing(User::class, ['email' => 'ana@example.com']);
    });
});

describe('update-user', function () {
    it('changes only the given fields', function () {
        $user = User::factory()->create(['name' => 'Ana', 'email' => 'ana@example.com']);

        ChismosaServer::actingAs(User::factory()->create())
            ->tool(UpdateUserTool::class, ['id' => $user->id, 'name' => 'Ana Reyes', 'email' => 'ana@example.com'])
            ->assertOk();

        expect($user->refresh())
            ->name->toBe('Ana Reyes')
            ->email->toBe('ana@example.com');
    });

    it('rejects an email that belongs to another account', function () {
        User::factory()->create(['email' => 'taken@example.com']);
        $user = User::factory()->create();

        ChismosaServer::actingAs(User::factory()->create())
            ->tool(UpdateUserTool::class, ['id' => $user->id, 'email' => 'taken@example.com'])
            ->assertHasErrors(['The email has already been taken.']);
    });

    it('returns an error when the user does not exist', function () {
        ChismosaServer::actingAs(User::factory()->create())
            ->tool(UpdateUserTool::class, ['id' => 999, 'name' => 'Nobody'])
            ->assertHasErrors(['User 999 was not found.']);
    });
});

describe('delete-user', function () {
    it('deletes another account that owns no relays', function () {
        $user = User::factory()->create();

        ChismosaServer::actingAs(User::factory()->create())
            ->tool(DeleteUserTool::class, ['id' => $user->id])
            ->assertOk();

        $this->assertModelMissing($user);
    });

    it('refuses to delete the signed-in account', function () {
        $actor = User::factory()->create();

        ChismosaServer::actingAs($actor)
            ->tool(DeleteUserTool::class, ['id' => $actor->id])
            ->assertHasErrors(['You cannot delete the account you are signed in as.']);

        $this->assertModelExists($actor);
    });

    it('refuses to delete an account that still owns relays', function () {
        $user = User::factory()->create();
        Relay::factory()->create(['user_id' => $user->id]);

        ChismosaServer::actingAs(User::factory()->create())
            ->tool(DeleteUserTool::class, ['id' => $user->id])
            ->assertHasErrors(["User {$user->id} still owns 1 relays. Give them to another user with update-relay first."]);

        $this->assertModelExists($user);
    });
});
