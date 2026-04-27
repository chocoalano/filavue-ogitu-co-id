<?php

namespace App\Http\Middleware;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Customer;
use App\Models\PaymentMethod;
use App\Models\Setting;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use App\Repositories\Pages\Contracts\PageRepositoryInterface;
use App\Support\Media\PublicMediaUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'appName' => config('app.name'),
            'csrf_token' => fn () => csrf_token(),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'wallet' => fn () => $request->session()->get('wallet'),
                'orders' => fn () => $request->session()->get('orders'),
                'checkout' => fn () => $request->session()->get('checkout'),
            ],
            'categories' => fn () => $this->categoriesData(),
            'footer' => fn () => $this->footerData(),
            'storeSettings' => fn () => $this->storeSettingsData(),
            'auth' => [
                'customer' => fn () => $this->authenticatedCustomer($request),
            ],
            'impersonation' => fn () => $this->impersonationData($request),
            'wishlistCount' => fn () => $this->wishlistCount(),
            'wishlistItems' => fn () => $this->wishlistItemsData(),
            'cartCount' => fn () => $this->cartCount(),
            'cartItems' => fn () => $this->cartItemsData(),
        ];
    }

    /**
     * Data customer yang sedang login, atau null jika belum login.
     *
     * @return array{id: int, name: string, email: string}|null
     */
    private function authenticatedCustomer(Request $request): ?array
    {
        /** @var Customer|null $customer */
        $customer = $request->user('customer');

        if (! $customer) {
            return null;
        }

        return [
            'id' => $customer->id,
            'name' => $customer->name,
            'email' => $customer->email,
        ];
    }

    /**
     * Data mode impersonation untuk banner storefront.
     *
     * @return array{active: bool, admin_name?: string|null, customer_name?: string|null, stop_url?: string|null}
     */
    private function impersonationData(Request $request): array
    {
        $impersonationSession = $request->session()->get('impersonation', []);

        if (! is_array($impersonationSession) || ! (bool) ($impersonationSession['is_active'] ?? false)) {
            return [
                'active' => false,
            ];
        }

        /** @var Customer|null $customer */
        $customer = $request->user('customer');

        return [
            'active' => true,
            'admin_name' => $impersonationSession['admin_name'] ?? null,
            'customer_name' => $customer?->name,
            'stop_url' => route('impersonation.stop'),
        ];
    }

    /**
     * Jumlah item wishlist customer yang sedang login.
     */
    private function wishlistCount(): int
    {
        /** @var Customer|null $customer */
        $customer = auth('customer')->user();

        if (! $customer) {
            return 0;
        }

        return WishlistItem::query()
            ->whereHas('wishlist', fn ($q) => $q->where('customer_id', $customer->id))
            ->count();
    }

    /**
     * Data item wishlist customer yang sedang login untuk header slider.
     *
     * @return array<int, array{
     *     id: int,
     *     productId: int,
     *     name: string,
     *     sku: string,
     *     price: float,
     *     image: string|null,
     *     inStock: bool,
     *     slug: string,
     * }>
     */
    private function wishlistItemsData(): array
    {
        /** @var Customer|null $customer */
        $customer = auth('customer')->user();

        if (! $customer) {
            return [];
        }

        /** @var Wishlist|null $wishlist */
        $wishlist = Wishlist::query()
            ->with(['items.product.primaryMedia'])
            ->where('customer_id', $customer->id)
            ->first();

        if (! $wishlist) {
            return [];
        }

        return $wishlist->items
            ->map(function (WishlistItem $item) {
                $product = $item->product;
                $primaryMedia = $product?->primaryMedia->first();

                return [
                    'id' => $item->id,
                    'productId' => $item->product_id,
                    'name' => $item->product_name,
                    'sku' => $item->product_sku,
                    'price' => (float) ($product?->base_price ?? 0),
                    'image' => PublicMediaUrl::resolve($primaryMedia?->url),
                    'inStock' => ($product?->stock ?? 0) > 0,
                    'slug' => $product?->slug ?? '',
                ];
            })
            ->toArray();
    }

    /**
     * Data item keranjang customer yang sedang login untuk header slider.
     *
     * @return array<int, array{
     *     id: int,
     *     productId: int,
     *     name: string,
     *     sku: string,
     *     variant: string|null,
     *     price: float,
     *     qty: int,
     *     rowTotal: float,
     *     image: string|null,
     *     inStock: bool,
     * }>
     */
    private function cartItemsData(): array
    {
        /** @var Customer|null $customer */
        $customer = auth('customer')->user();

        if (! $customer) {
            return [];
        }

        /** @var Cart|null $cart */
        $cart = Cart::query()
            ->with(['items.product.primaryMedia'])
            ->where('customer_id', $customer->id)
            ->first();

        if (! $cart) {
            return [];
        }

        return $cart->items
            ->map(function (CartItem $item) {
                $primaryMedia = $item->product?->primaryMedia->first();
                $meta = $item->meta_json ?? [];

                return [
                    'id' => $item->id,
                    'productId' => $item->product_id,
                    'name' => $item->product_name,
                    'sku' => $item->product_sku,
                    'variant' => $meta['variant'] ?? null,
                    'price' => (float) $item->unit_price,
                    'qty' => $item->qty,
                    'rowTotal' => (float) $item->row_total,
                    'image' => PublicMediaUrl::resolve($primaryMedia?->url),
                    'inStock' => ($item->product?->stock ?? 0) > 0,
                ];
            })
            ->toArray();
    }

    /**
     * Jumlah item di keranjang customer yang sedang login.
     */
    private function cartCount(): int
    {
        /** @var Customer|null $customer */
        $customer = auth('customer')->user();

        if (! $customer) {
            return 0;
        }

        return $customer->cart?->items()->count() ?? 0;
    }

    /**
     * Settings toko untuk storefront (branding, SEO, preferences), di-cache selama 1 jam.
     *
     * @return array<string, mixed>
     */
    private function storeSettingsData(): array
    {
        return Cache::remember('storefront_settings_v2', 3600, function () {
            $keys = [
                'store.name',
                'branding.logo',
                'branding.favicon',
                'branding.primary_color',
                'branding.secondary_color',
                'branding.tagline',
                'seo.meta_title',
                'seo.meta_description',
                'seo.meta_keywords',
                'seo.og_image',
                'preferences.currency',
                'preferences.language',
                'topbar.enabled',
                'topbar.message',
                'topbar.cta_label',
                'topbar.cta_url',
                'features.highlights',
                'home_cta.badge_label',
                'home_cta.heading_main',
                'home_cta.heading_gradient',
                'home_cta.description',
                'home_cta.primary_cta_label',
                'home_cta.primary_cta_url',
                'home_cta.secondary_cta_label',
                'home_cta.secondary_cta_url',
                'home_cta.floating_badge1_label',
                'home_cta.floating_badge1_value',
                'home_cta.floating_badge2_label',
                'home_cta.floating_badge2_value',
                'home_cta.features',
                'affiliate_cta.badge_label',
                'affiliate_cta.heading_main',
                'affiliate_cta.heading_sub',
                'affiliate_cta.description',
                'affiliate_cta.stat1_title',
                'affiliate_cta.stat1_description',
                'affiliate_cta.stat2_value',
                'affiliate_cta.stat2_label',
                'affiliate_cta.stat3_value',
                'affiliate_cta.stat3_label',
                'affiliate_cta.floating_label',
                'affiliate_cta.floating_value',
                'affiliate_cta.primary_cta_label',
                'affiliate_cta.primary_cta_url',
                'affiliate_cta.secondary_cta_label',
                'affiliate_cta.secondary_cta_url',
                'affiliate_cta.footer_note',
                'affiliate_cta.benefits',
            ];

            $settings = Setting::query()
                ->whereIn('key', $keys)
                ->pluck('value', 'key');

            $resolveImage = function (?string $path): ?string {
                return filled($path) ? PublicMediaUrl::resolve($path) : null;
            };

            $parseKeywords = function (?string $value): array {
                if (! filled($value)) {
                    return [];
                }

                $decoded = json_decode($value, true);

                return is_array($decoded) ? array_values($decoded) : [];
            };

            return [
                'store' => [
                    'name' => $settings->get('store.name'),
                ],
                'branding' => [
                    'logo' => $resolveImage($settings->get('branding.logo')),
                    'favicon' => $resolveImage($settings->get('branding.favicon')),
                    'primary_color' => $settings->get('branding.primary_color') ?? '#0ea5e9',
                    'secondary_color' => $settings->get('branding.secondary_color') ?? '#111827',
                    'tagline' => $settings->get('branding.tagline'),
                ],
                'seo' => [
                    'meta_title' => $settings->get('seo.meta_title'),
                    'meta_description' => $settings->get('seo.meta_description'),
                    'meta_keywords' => $parseKeywords($settings->get('seo.meta_keywords')),
                    'og_image' => $resolveImage($settings->get('seo.og_image')),
                ],
                'preferences' => [
                    'currency' => $settings->get('preferences.currency') ?? 'IDR',
                    'language' => $settings->get('preferences.language') ?? 'id',
                ],
                'topbar' => [
                    'enabled' => $settings->has('topbar.enabled')
                        ? in_array($settings->get('topbar.enabled'), ['1', 'true', 'yes', 'on'], true)
                        : true,
                    'message' => $settings->get('topbar.message'),
                    'cta_label' => $settings->get('topbar.cta_label'),
                    'cta_url' => $settings->get('topbar.cta_url'),
                ],
                'home_cta' => [
                    'badge_label' => $settings->get('home_cta.badge_label') ?? 'Koleksi Eksklusif',
                    'heading_main' => $settings->get('home_cta.heading_main') ?? 'Kesehatan & Kecantikan',
                    'heading_gradient' => $settings->get('home_cta.heading_gradient') ?? 'Tanpa Batas',
                    'description' => $settings->get('home_cta.description') ?? 'Masuk ke ekosistem wellness kami. Temukan produk revolusioner yang dirancang khusus untuk meningkatkan kualitas hidup Anda.',
                    'primary_cta_label' => $settings->get('home_cta.primary_cta_label') ?? 'Jelajahi Produk',
                    'primary_cta_url' => $settings->get('home_cta.primary_cta_url') ?? '/shop',
                    'secondary_cta_label' => $settings->get('home_cta.secondary_cta_label'),
                    'secondary_cta_url' => $settings->get('home_cta.secondary_cta_url'),
                    'floating_badge1_label' => $settings->get('home_cta.floating_badge1_label') ?? 'Terverifikasi',
                    'floating_badge1_value' => $settings->get('home_cta.floating_badge1_value') ?? 'BPOM & Halal',
                    'floating_badge2_label' => $settings->get('home_cta.floating_badge2_label') ?? 'Terlaris',
                    'floating_badge2_value' => $settings->get('home_cta.floating_badge2_value') ?? '100K+ Terjual',
                    'features' => (function () use ($settings, $parseKeywords): array {
                        $raw = $parseKeywords($settings->get('home_cta.features'));

                        if ($raw === []) {
                            return [
                                ['icon' => 'i-lucide-award', 'label' => 'Eksklusif', 'description' => 'Produk premium terakurasi'],
                                ['icon' => 'i-lucide-percent', 'label' => 'Hemat', 'description' => 'Diskon member hingga 30%'],
                                ['icon' => 'i-lucide-clock', 'label' => 'Terbatas', 'description' => 'Penawaran kilat mingguan'],
                            ];
                        }

                        return array_values(array_filter($raw, fn ($item): bool => is_array($item)
                            && filled($item['label'] ?? null)
                            && filled($item['icon'] ?? null)));
                    })(),
                ],
                'affiliate_cta' => [
                    'badge_label' => $settings->get('affiliate_cta.badge_label') ?? 'Entrepreneurship Program',
                    'heading_main' => $settings->get('affiliate_cta.heading_main') ?? 'Bangun Kerajaan',
                    'heading_sub' => $settings->get('affiliate_cta.heading_sub') ?? 'Bisnis Anda Sendiri',
                    'description' => $settings->get('affiliate_cta.description') ?? 'Bukan sekadar belanja, ini adalah peluang kemitraan. Manfaatkan sistem pemasaran jaringan kami yang sudah teruji untuk meraih kebebasan finansial dan waktu.',
                    'stat1_title' => $settings->get('affiliate_cta.stat1_title') ?? 'Penghasilan Tanpa Batas',
                    'stat1_description' => $settings->get('affiliate_cta.stat1_description') ?? 'Dapatkan profit retail dan bonus jaringan setiap hari. Sistem bagi hasil yang transparan dan otomatis masuk ke wallet Anda.',
                    'stat2_value' => $settings->get('affiliate_cta.stat2_value') ?? '75%',
                    'stat2_label' => $settings->get('affiliate_cta.stat2_label') ?? 'Payout Ratio',
                    'stat3_value' => $settings->get('affiliate_cta.stat3_value') ?? '100+',
                    'stat3_label' => $settings->get('affiliate_cta.stat3_label') ?? 'Kota Terjangkau',
                    'floating_label' => $settings->get('affiliate_cta.floating_label') ?? 'Target BV Reward',
                    'floating_value' => $settings->get('affiliate_cta.floating_value') ?? 'Raih Expander 2025',
                    'primary_cta_label' => $settings->get('affiliate_cta.primary_cta_label') ?? 'Gabung Sekarang',
                    'primary_cta_url' => $settings->get('affiliate_cta.primary_cta_url') ?? '/register',
                    'secondary_cta_label' => $settings->get('affiliate_cta.secondary_cta_label'),
                    'secondary_cta_url' => $settings->get('affiliate_cta.secondary_cta_url'),
                    'footer_note' => $settings->get('affiliate_cta.footer_note') ?? '* Syarat dan ketentuan berlaku. BV (Business Volume) dihitung otomatis per transaksi.',
                    'benefits' => (function () use ($settings, $parseKeywords): array {
                        $raw = $parseKeywords($settings->get('affiliate_cta.benefits'));

                        if ($raw === []) {
                            return [
                                ['icon' => 'i-lucide-badge-percent', 'label' => 'Komisi Langsung', 'value' => '20%', 'description' => 'Komisi retail langsung terhitung otomatis setiap transaksi tervalidasi.'],
                                ['icon' => 'i-lucide-users-2', 'label' => 'Bonus Jaringan', 'value' => 'Unlimited', 'description' => 'Bangun jaringan mitra tanpa batas wilayah dengan perhitungan bonus real-time.'],
                                ['icon' => 'i-lucide-trophy', 'label' => 'Reward Mewah', 'value' => 'Umroh/Mobil', 'description' => 'Capai milestone penjualan untuk membuka reward eksklusif bertingkat.'],
                            ];
                        }

                        return array_values(array_map(
                            fn (array $item): array => [
                                'icon' => (string) $item['icon'],
                                'label' => (string) $item['label'],
                                'value' => (string) $item['value'],
                                'description' => (string) ($item['description'] ?? 'Potensi keuntungan maksimal dengan modal minimal dan sistem yang terukur.'),
                            ],
                            array_filter($raw, fn ($item): bool => is_array($item)
                                && filled($item['label'] ?? null)
                                && filled($item['icon'] ?? null)
                                && filled($item['value'] ?? null))
                        ));
                    })(),
                ],
                'features' => [
                    'highlights' => (function () use ($settings, $parseKeywords): array {
                        $raw = $parseKeywords($settings->get('features.highlights'));

                        if ($raw === []) {
                            return [
                                ['icon' => 'i-lucide-truck', 'title' => 'Gratis Ongkir', 'description' => 'Untuk pembelian di atas Rp 150k'],
                                ['icon' => 'i-lucide-shield-check', 'title' => 'Pembayaran Aman', 'description' => 'Transaksi terenkripsi 100%'],
                                ['icon' => 'i-lucide-headset', 'title' => 'Support 24/7', 'description' => 'Tim kami siap membantu Anda'],
                                ['icon' => 'i-lucide-rotate-ccw', 'title' => 'Easy Returns', 'description' => 'Pengembalian gratis 30 hari'],
                            ];
                        }

                        return array_values(array_filter($raw, fn ($item): bool => is_array($item)
                            && filled($item['title'] ?? null)
                            && filled($item['icon'] ?? null)));
                    })(),
                ],
            ];
        });
    }

    /**
     * Data yang ditampilkan di footer, di-cache selama 1 jam.
     *
     * @return array{
     *     pages: Collection<int, array{title:string,slug:string,template:string|null,show_on:string|null}>,
     *     supportPages: Collection<int, array{title:string,slug:string,template:string|null,show_on:string|null}>,
     *     companyPages: Collection<int, array{title:string,slug:string,template:string|null,show_on:string|null}>,
     *     headerTopBarPages: Collection<int, array{title:string,slug:string,template:string|null,show_on:string|null}>,
     *     headerNavbarPages: Collection<int, array{title:string,slug:string,template:string|null,show_on:string|null}>,
     *     headerBottomBarPages: Collection<int, array{title:string,slug:string,template:string|null,show_on:string|null}>,
     *     bottomMainPages: Collection<int, array{title:string,slug:string,template:string|null,show_on:string|null}>,
     *     paymentMethods: Collection,
     *     categories: Collection,
     *     socialLinks: array<string, string>,
     *     store: array<string, string|null>,
     * }
     */
    private function footerData(): array
    {
        return Cache::remember('footer_data_v3', 3600, function () {
            /** @var PageRepositoryInterface $pageRepository */
            $pageRepository = app(PageRepositoryInterface::class);
            $settings = Setting::query()
                ->whereIn('key', [
                    'social.instagram',
                    'social.youtube',
                    'social.tiktok',
                    'social.whatsapp',
                    'social.x',
                    'social.facebook',
                    'store.name',
                    'store.description',
                    'store.email',
                    'store.phone',
                    'branding.tagline',
                ])
                ->pluck('value', 'key');

            $socialLinks = $settings
                ->filter(fn ($value, $key) => str_starts_with($key, 'social.') && filled($value))
                ->mapWithKeys(fn ($value, $key) => [str_replace('social.', '', $key) => $value])
                ->toArray();

            $pages = $pageRepository->getPublishedNavigationPages();
            $supportTemplates = ['faq', 'contact'];
            $footerPages = $pages
                ->filter(function (array $page): bool {
                    return ($page['show_on'] ?? null) === 'footer_main';
                })
                ->values();

            $supportPages = $footerPages
                ->filter(function (array $page) use ($supportTemplates): bool {
                    $template = strtolower((string) ($page['template'] ?? ''));

                    return in_array($template, $supportTemplates, true);
                })
                ->values();

            $companyPages = $footerPages
                ->reject(function (array $page) use ($supportTemplates): bool {
                    $template = strtolower((string) ($page['template'] ?? ''));

                    return in_array($template, $supportTemplates, true);
                })
                ->values();

            return [
                'pages' => $footerPages,
                'supportPages' => $supportPages,
                'companyPages' => $companyPages,
                'headerTopBarPages' => $pages
                    ->filter(fn (array $page): bool => ($page['show_on'] ?? null) === 'header_top_bar')
                    ->values(),
                'headerNavbarPages' => $pages
                    ->filter(fn (array $page): bool => ($page['show_on'] ?? null) === 'header_navbar')
                    ->values(),
                'headerBottomBarPages' => $pages
                    ->filter(fn (array $page): bool => ($page['show_on'] ?? null) === 'header_bottombar')
                    ->values(),
                'bottomMainPages' => $pages
                    ->filter(fn (array $page): bool => ($page['show_on'] ?? null) === 'bottom_main')
                    ->values(),
                'paymentMethods' => PaymentMethod::query()
                    ->where('is_active', true)
                    ->get(['code', 'name', 'logo', 'display_name']),
                'categories' => Category::query()
                    ->where('is_active', true)
                    ->whereNull('parent_id')
                    ->orderBy('sort_order')
                    ->get(['slug', 'name']),
                'socialLinks' => $socialLinks,
                'store' => [
                    'name' => $settings->get('store.name'),
                    'description' => $settings->get('store.description'),
                    'email' => $settings->get('store.email'),
                    'phone' => $settings->get('store.phone'),
                    'tagline' => $settings->get('branding.tagline'),
                ],
            ];
        });
    }

    /**
     * Kategori produk aktif (level 1), di-cache selama 1 jam.
     *
     * @return Collection
     */
    private function categoriesData()
    {
        return Cache::remember('shared_categories_v3', 3600, function () {
            return Category::query()
                ->where('is_active', true)
                ->whereNull('parent_id')
                ->orderBy('sort_order')
                ->get(['id', 'slug', 'name', 'description', 'image'])
                ->map(fn (Category $cat) => [
                    'id' => $cat->id,
                    'slug' => $cat->slug,
                    'name' => $cat->name,
                    'description' => $cat->description,
                    'image' => $this->resolveExistingCategoryImageUrl($cat->image),
                    'productCount' => $cat->products()->count(),
                ]);
        });
    }

    private function resolveExistingCategoryImageUrl(?string $imagePath): ?string
    {
        if (! filled($imagePath)) {
            return null;
        }

        $storageRelativePath = PublicMediaUrl::extractPublicStorageRelativePath((string) $imagePath);

        if ($storageRelativePath !== null && ! Storage::disk('public')->exists($storageRelativePath)) {
            return null;
        }

        return PublicMediaUrl::resolve($imagePath);
    }
}
