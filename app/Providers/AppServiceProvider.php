<?php

namespace App\Providers;

use App\Domain\Lead\Repositories\LeadRepository;
use App\Infrastructure\Lead\EloquentLeadRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            LeadRepository::class,
            EloquentLeadRepository::class,
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}