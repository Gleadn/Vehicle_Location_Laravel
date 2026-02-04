<?php

namespace App\Providers;

use App\Models\LocationDemand;
use App\Models\Vehicle;
use App\Observers\LocationDemandObserver;
use App\Observers\VehicleObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register observers
        Vehicle::observe(VehicleObserver::class);
        LocationDemand::observe(LocationDemandObserver::class);
    }
}
