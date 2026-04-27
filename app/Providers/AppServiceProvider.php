<?php

namespace App\Providers;

use App\Filament\Resources\Products\Livewire\ProductOrderItemsTable;
use App\Repositories\Articles\Contracts\ArticleRepositoryInterface;
use App\Repositories\Articles\EloquentArticleRepository;
use App\Repositories\Auth\Contracts\CustomerAuthRepositoryInterface;
use App\Repositories\Auth\Contracts\CustomerProfileRepositoryInterface;
use App\Repositories\Auth\Contracts\CustomerRegistrationRepositoryInterface;
use App\Repositories\Auth\CustomerAuthRepository;
use App\Repositories\Auth\CustomerProfileRepository;
use App\Repositories\Auth\CustomerRegistrationRepository;
use App\Repositories\Cart\Contracts\CartRepositoryInterface;
use App\Repositories\Cart\EloquentCartRepository;
use App\Repositories\Checkout\Contracts\CheckoutRepositoryInterface;
use App\Repositories\Checkout\EloquentCheckoutRepository;
use App\Repositories\CustomerAddress\Contracts\CustomerAddressRepositoryInterface;
use App\Repositories\CustomerAddress\EloquentCustomerAddressRepository;
use App\Repositories\Dashboard\Contracts\DashboardRepositoryInterface;
use App\Repositories\Dashboard\EloquentDashboardRepository;
use App\Repositories\Home\Contracts\HomeRepositoryInterface;
use App\Repositories\Home\EloquentHomeRepository;
use App\Repositories\Pages\Contracts\PageRepositoryInterface;
use App\Repositories\Pages\EloquentPageRepository;
use App\Repositories\Payments\Contracts\MidtransCallbackRepositoryInterface;
use App\Repositories\Payments\EloquentMidtransCallbackRepository;
use App\Repositories\Products\Contracts\ProductRepositoryInterface;
use App\Repositories\Products\EloquentProductRepository;
use App\Repositories\Shipping\Contracts\ShippingTargetRepositoryInterface;
use App\Repositories\Shipping\EloquentShippingTargetRepository;
use App\Repositories\WhatsApp\Contracts\WhatsAppBroadcastRepositoryInterface;
use App\Repositories\WhatsApp\EloquentWhatsAppBroadcastRepository;
use App\Repositories\Wishlist\Contracts\WishlistRepositoryInterface;
use App\Repositories\Wishlist\EloquentWishlistRepository;
use App\Repositories\ZennerAcademy\Contracts\AcademyDashboardRepositoryInterface;
use App\Repositories\ZennerAcademy\Contracts\ContentCategoryRepositoryInterface;
use App\Repositories\ZennerAcademy\Contracts\ContentRepositoryInterface;
use App\Repositories\ZennerAcademy\EloquentAcademyDashboardRepository;
use App\Repositories\ZennerAcademy\EloquentContentCategoryRepository;
use App\Repositories\ZennerAcademy\EloquentContentRepository;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(CustomerAuthRepositoryInterface::class, CustomerAuthRepository::class);
        $this->app->bind(CustomerProfileRepositoryInterface::class, CustomerProfileRepository::class);
        $this->app->bind(CustomerRegistrationRepositoryInterface::class, CustomerRegistrationRepository::class);
        $this->app->bind(
            ProductRepositoryInterface::class,
            EloquentProductRepository::class
        );
        $this->app->bind(CartRepositoryInterface::class, EloquentCartRepository::class);
        $this->app->bind(WishlistRepositoryInterface::class, EloquentWishlistRepository::class);
        $this->app->bind(
            CheckoutRepositoryInterface::class,
            EloquentCheckoutRepository::class
        );
        $this->app->bind(
            ShippingTargetRepositoryInterface::class,
            EloquentShippingTargetRepository::class
        );
        $this->app->bind(
            DashboardRepositoryInterface::class,
            EloquentDashboardRepository::class
        );
        $this->app->bind(HomeRepositoryInterface::class, EloquentHomeRepository::class);
        $this->app->bind(
            CustomerAddressRepositoryInterface::class,
            EloquentCustomerAddressRepository::class
        );
        $this->app->bind(MidtransCallbackRepositoryInterface::class, EloquentMidtransCallbackRepository::class);
        $this->app->bind(ArticleRepositoryInterface::class, EloquentArticleRepository::class);
        $this->app->bind(PageRepositoryInterface::class, EloquentPageRepository::class);
        $this->app->bind(WhatsAppBroadcastRepositoryInterface::class, EloquentWhatsAppBroadcastRepository::class);
        $this->app->bind(ContentCategoryRepositoryInterface::class, EloquentContentCategoryRepository::class);
        $this->app->bind(ContentRepositoryInterface::class, EloquentContentRepository::class);
        $this->app->bind(AcademyDashboardRepositoryInterface::class, EloquentAcademyDashboardRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureRateLimiters();

        $this->app->booted(function (): void {
            Livewire::component('filament.products.order-items-table', ProductOrderItemsTable::class);
        });

        Gate::before(function ($user, string $ability) {
            if (! is_object($user)) {
                return null;
            }

            $roleColumn = strtolower(trim((string) data_get($user, 'role', '')));

            if (in_array($roleColumn, ['developer', 'super_admin', 'superadmin'], true)) {
                return true;
            }

            if (method_exists($user, 'hasRole')) {
                try {
                    return $user->hasRole(['super_admin', 'developer']) ? true : null;
                } catch (\Throwable) {
                    return null;
                }
            }

            return null;
        });

    }

    private function configureRateLimiters(): void
    {
        // Halaman publik (browse): 120 req/menit per IP
        RateLimiter::for('public', function (Request $request): Limit {
            return Limit::perMinute(120)->by($request->ip());
        });

        // Upaya autentikasi (login, register, forgot-password): 5 req/menit per IP
        RateLimiter::for('auth-attempts', function (Request $request): Limit {
            return Limit::perMinute(5)->by($request->ip());
        });

        // Reset password & konfirmasi WA: 5 req/menit per IP
        RateLimiter::for('password-reset', function (Request $request): Limit {
            return Limit::perMinute(5)->by($request->ip());
        });

        // Aksi user yang sudah login (cart, wishlist, dashboard): 60 req/menit per user+IP
        RateLimiter::for('authenticated', function (Request $request): Limit {
            $key = optional($request->user('customer') ?? $request->user('sanctum') ?? $request->user())->getKey()
                ?? $request->ip();

            return Limit::perMinute(60)->by($key);
        });

        // Endpoint pembayaran sensitif (token midtrans, pay saldo, topup, tarik dana): 10 req/menit per user+IP
        RateLimiter::for('payment', function (Request $request): Limit {
            $key = optional($request->user('customer') ?? $request->user('sanctum') ?? $request->user())->getKey()
                ?? $request->ip();

            return Limit::perMinute(10)->by('payment|'.$key);
        });

        // Webhook eksternal (Midtrans, Qontak): 300 req/menit per IP
        RateLimiter::for('webhook', function (Request $request): Limit {
            return Limit::perMinute(300)->by($request->ip());
        });

        // Media publik (file gambar/dokumen): 200 req/menit per IP
        RateLimiter::for('media', function (Request $request): Limit {
            return Limit::perMinute(200)->by($request->ip());
        });

        // Newsletter subscription: 10 req/menit per IP
        RateLimiter::for('newsletter', function (Request $request): Limit {
            return Limit::perMinute(10)->by($request->ip());
        });

        // Kalkulasi ongkir (hit API eksternal): 30 req/menit per user+IP
        RateLimiter::for('shipping-cost', function (Request $request): Limit {
            $key = optional($request->user('customer') ?? $request->user('sanctum') ?? $request->user())->getKey()
                ?? $request->ip();

            return Limit::perMinute(30)->by('shipping|'.$key);
        });

        FilamentIcon::register([
            // Mengganti icon toggle sidebar saat terbuka
            'panels::sidebar.collapse-button' => 'heroicon-m-bars-3-bottom-right',

            // Mengganti icon toggle sidebar saat tertutup (mobile/collapsed)
            'panels::sidebar.expand-button' => 'heroicon-m-bars-3',
        ]);
    }
}
