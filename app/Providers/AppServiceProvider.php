<?php

namespace App\Providers;

use App\Services\TenagaKependidikanService;
use App\Services\TridharmaService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TridharmaService::class, function () {
            return new TridharmaService(
                config('services.tridharma.base_url', env('TRIDHARMA_API_URL', env('API_BASE_URL', 'https://tridharma.fayra.my.id/api')))
            );
        });

        $this->app->singleton(TenagaKependidikanService::class, function () {
            return new TenagaKependidikanService(
                config('services.tridharma.base_url', env('TRIDHARMA_API_URL', env('API_BASE_URL', 'https://tridharma.fayra.my.id/api')))
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
