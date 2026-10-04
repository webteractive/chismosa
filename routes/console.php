<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('relay:purge_old_logs')->weekly();
Schedule::command('mcp:purge_unused_clients')->daily();
Schedule::command('horizon:snapshot')->everyFiveMinutes();
