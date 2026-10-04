<?php

namespace App\Console\Commands;

use Laravel\Passport\Passport;
use Illuminate\Console\Command;

class PurgeUnusedOAuthClients extends Command
{
    protected $signature = 'mcp:purge_unused_clients';

    protected $description = 'Purge OAuth clients registered by MCP clients that were never authorized';

    /**
     * Only clients from open registration are touched: public authorization
     * code clients with no owner. A day is enough to finish signing in.
     */
    public function handle(): int
    {
        $deleted = Passport::client()->newQuery()
            ->whereNull('owner_id')
            ->whereNull('secret')
            ->where('grant_types', 'like', '%"authorization_code"%')
            ->where('created_at', '<=', now()->subDay())
            ->whereDoesntHave('tokens')
            ->whereDoesntHave('authCodes')
            ->delete();

        $this->info(__(':count unused OAuth clients has been purged.', ['count' => $deleted]));

        return self::SUCCESS;
    }
}
