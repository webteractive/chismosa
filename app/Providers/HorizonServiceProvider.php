<?php

namespace App\Providers;

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\HorizonApplicationServiceProvider;

class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    /**
     * Serve Horizon beneath the admin path unless HORIZON_PATH overrides it.
     */
    public function register(): void
    {
        config([
            'horizon.path' => config('horizon.path') ?? config('chismosa.admin_path').'/horizon',
        ]);
    }

    /**
     * Anyone who can sign in to the admin panel can view Horizon.
     */
    protected function gate(): void
    {
        Gate::define('viewHorizon', fn (User $user): bool => $user->canAccessPanel(Filament::getDefaultPanel()));
    }
}
