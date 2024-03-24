<?php

namespace App\Providers;
use App\Repositories\Category\CategoryRepository;
use App\Repositories\Category\Interface\CategoryRepositoryInterface;
use App\Repositories\Product\BulkProductRepository;
use App\Repositories\Product\Interface\BulkProductRepositoryInterface;
use App\Repositories\Product\Interface\ProductRepositoryInterface;
use App\Repositories\Product\ProductRepository;
use App\Repositories\Subscription\Interface\MonthRepositoryInterface;
use App\Repositories\Subscription\Interface\RegionRepositoryInterface;
use App\Repositories\Subscription\Interface\SubscriptionRepositoryInterface;
use App\Repositories\Subscription\MonthRepository;
use App\Repositories\Subscription\RegionRepository;
use App\Repositories\Subscription\SubscriptionRepository;
use App\Repositories\Tag\Interface\TagRepositoryInterface;
use App\Repositories\Tag\TagRepository;
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
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(BulkProductRepositoryInterface::class, BulkProductRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(SubscriptionRepositoryInterface::class, SubscriptionRepository::class);
        $this->app->bind(MonthRepositoryInterface::class, MonthRepository::class);
        $this->app->bind(RegionRepositoryInterface::class, RegionRepository::class);
        $this->app->bind(TagRepositoryInterface::class, TagRepository::class);

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
