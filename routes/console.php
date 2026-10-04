<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('relay:purge_old_logs')->weekly();
Schedule::command('horizon:snapshot')->everyFiveMinutes();
