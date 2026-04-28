<?php

namespace App\Providers\Filament;

use App\Filament\Resources\Products\Livewire\ProductOrderItemsTable;
use App\Filament\Widgets\AccountWidget;
use App\Filament\Widgets\AppInfoWidget;
use App\Filament\Widgets\QueueListWidget;
use App\Filament\Widgets\QueueSystemMonitorWidget;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Caresome\FilamentAuthDesigner\AuthDesignerPlugin;
use Caresome\FilamentAuthDesigner\Data\AuthPageConfig;
use Caresome\FilamentAuthDesigner\Enums\MediaPosition;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Livewire\Livewire;

class ControlPanelPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('control-panel')
            ->path('control-panel')
            ->viteTheme('resources/css/filament/control-panel/theme.css')
            ->login()
            ->plugins([
                AuthDesignerPlugin::make()
                    ->login(fn (AuthPageConfig $config) => $config
                        ->media(asset('assets/login.webp'))
                        ->mediaPosition(MediaPosition::Left)
                        ->blur(8)
                    ),
                FilamentShieldPlugin::make()
                    ->navigationGroup('Kelola'),
            ])
            ->databaseNotifications()
            ->maxContentWidth(Width::Full)
            ->colors([
                'primary' => Color::Zinc,
            ])
            ->sidebarWidth('17rem')
            ->sidebarCollapsibleOnDesktop()
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Kelola')
                    ->icon(Heroicon::Squares2x2),
                NavigationGroup::make()
                    ->label('Toko')
                    ->icon(Heroicon::ShoppingBag),
                NavigationGroup::make()
                    ->label('Pesanan')
                    ->icon(Heroicon::ShoppingCart),
                NavigationGroup::make()
                    ->label('Bonus & Komisi MLM')
                    ->icon(Heroicon::Gift),
                NavigationGroup::make()
                    ->label('Ewallet & Keuangan')
                    ->icon(Heroicon::CurrencyDollar),
                NavigationGroup::make()
                    ->label('Affiliate')
                    ->icon(Heroicon::UserGroup),
                NavigationGroup::make()
                    ->label('Laporan')
                    ->icon(Heroicon::DocumentText),
                NavigationGroup::make()
                    ->label('Ogitu Club')
                    ->icon('hugeicons-party'),
                NavigationGroup::make()
                    ->label('Gatcha System')
                    ->icon('css-games'),
                NavigationGroup::make()
                    ->label('Pengaturan')
                    ->icon(Heroicon::Cog6Tooth),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                AppInfoWidget::class,
                QueueSystemMonitorWidget::class,
                QueueListWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->bootUsing(function (): void {
                Livewire::component('filament.products.order-items-table', ProductOrderItemsTable::class);
            })
            ->authGuard('web');
    }
}
