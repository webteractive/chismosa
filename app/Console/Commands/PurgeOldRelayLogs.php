<?php

namespace App\Console\Commands;

use App\Models\RelayLog;
use Illuminate\Console\Command;

class PurgeOldRelayLogs extends Command
{
    protected $signature = 'relay:purge_old_logs';

    protected $description = 'Purge old relay logs';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $deleted = RelayLog::query()
            ->where('created_at', '<=', now()->subMonth())
            ->delete();

        $this->info(__(':count old relay logs has been purged.', ['count' => $deleted]));

        return 0;
    }
}
