<?php

namespace App\Providers;

use App\Services\Tenancy\CurrentHotel;
use Illuminate\Support\ServiceProvider;

class TenancyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CurrentHotel::class, function () {
            return new CurrentHotel();
        });
    }
}
