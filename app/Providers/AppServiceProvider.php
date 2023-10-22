<?php

namespace App\Providers;
use App\Repositories\User\Interface\UserLevelRepositoryInterface;
use App\Repositories\User\Interface\UserRepositoryInterface;
use App\Repositories\User\UserLevelRepository;
use App\Repositories\User\UserRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(UserLevelRepositoryInterface::class, UserLevelRepository::class);

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
