<?php

namespace App\Providers;

use App\Repositories\Interfaces\PostgresRepositoryInterface;
use App\Repositories\PostgresRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(
            PostgresRepositoryInterface::class,
            fn() => new PostgresRepository()
        );
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
