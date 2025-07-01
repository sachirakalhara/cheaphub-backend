<?php

namespace App\Providers;

use App\Repositories\Cart\CartItemRepository;
use App\Repositories\Cart\CartRepository;
use App\Repositories\Cart\Interface\CartItemRepositoryInterface;
use App\Repositories\Cart\Interface\CartRepositoryInterface;
use App\Repositories\Category\CategoryRepository;
use App\Repositories\Category\Interface\CategoryRepositoryInterface;
use App\Repositories\Package\Interface\PackageRepositoryInterface;
use App\Repositories\Package\PackageRepository;
use App\Repositories\Payment\Interface\MarxPaymentRepositoryInterface;
use App\Repositories\Payment\Interface\OrderRepositoryInterface;
use App\Repositories\Payment\Interface\WalletRepositoryInterface;
use App\Repositories\Payment\MarxPaymentRepository;
use App\Repositories\Payment\OrderRepository;
use App\Repositories\Payment\WalletRepository;
use App\Repositories\Product\BulkProductRepository;
use App\Repositories\Product\Interface\BulkProductRepositoryInterface;
use App\Repositories\Product\Interface\ContributionProductRepositoryInterface;
use App\Repositories\Product\ContributionProductRepository;
use App\Repositories\Subscription\Interface\SubscriptionRepositoryInterface;
use App\Repositories\Subscription\SubscriptionRepository;
use App\Repositories\Tag\Interface\TagRepositoryInterface;
use App\Repositories\Tag\TagRepository;
use App\Repositories\User\Interface\UserLevelRepositoryInterface;
use App\Repositories\User\Interface\UserRepositoryInterface;
use App\Repositories\User\UserLevelRepository;
use App\Repositories\User\UserRepository;
use Illuminate\Support\ServiceProvider;
use App\Repositories\Coupon\CouponRepository;
use App\Repositories\Coupon\Interface\CouponRepositoryInterface;
use App\Repositories\Payment\Interface\OrderNoteRepositoryInterface;
use App\Repositories\Payment\OrderNoteRepository;
use App\Repositories\Product\Interface\ProductReplacementRepositoryInterface;
use App\Repositories\Product\ProductReplacementRepository;
use App\Repositories\Review\Interface\ReviewRepositoryInterface;
use App\Repositories\Review\ReviewRepository;
use App\Repositories\Ticket\Interface\TicketRepositoryInterface;
use App\Repositories\Ticket\TicketRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(UserLevelRepositoryInterface::class, UserLevelRepository::class);
        $this->app->bind(ContributionProductRepositoryInterface::class, ContributionProductRepository::class);
        $this->app->bind(BulkProductRepositoryInterface::class, BulkProductRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(SubscriptionRepositoryInterface::class, SubscriptionRepository::class);
        $this->app->bind(TagRepositoryInterface::class, TagRepository::class);
        $this->app->bind(PackageRepositoryInterface::class, PackageRepository::class);
        $this->app->bind(MarxPaymentRepositoryInterface::class, MarxPaymentRepository::class);
        $this->app->bind(WalletRepositoryInterface::class, WalletRepository::class);
        $this->app->bind(CartRepositoryInterface::class, CartRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
        $this->app->bind(CouponRepositoryInterface::class, CouponRepository::class);
        $this->app->bind(TicketRepositoryInterface::class, TicketRepository::class);
        $this->app->bind(CartItemRepositoryInterface::class, CartItemRepository::class);
        $this->app->bind(ProductReplacementRepositoryInterface::class, ProductReplacementRepository::class);
        $this->app->bind(ReviewRepositoryInterface::class, ReviewRepository::class);
        $this->app->bind(OrderNoteRepositoryInterface::class, OrderNoteRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
