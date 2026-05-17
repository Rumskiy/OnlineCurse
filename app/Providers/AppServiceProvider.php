<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind Test Repository
        $this->app->bind(
            \App\Repositories\Contracts\TestRepositoryInterface::class,
            \App\Repositories\Eloquent\TestRepository::class
        );

        // Bind Course Repository
        $this->app->bind(
            \App\Repositories\Contracts\CourseRepositoryInterface::class,
            \App\Repositories\Eloquent\CourseRepository::class
        );

        // Bind Section Repository
        $this->app->bind(
            \App\Repositories\Contracts\SectionRepositoryInterface::class,
            \App\Repositories\Eloquent\SectionRepository::class
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
