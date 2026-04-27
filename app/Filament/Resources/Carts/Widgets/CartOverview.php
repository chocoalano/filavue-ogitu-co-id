<?php

namespace App\Filament\Resources\Carts\Widgets;

use App\Models\Cart;
use App\Models\CartItem;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Collection;

class CartOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Insight Keranjang';

    protected ?string $description = 'Ringkasan keranjang belanja, item, dan potensi transaksi.';

    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $cartQuery = Cart::query();

        $totalCarts = (clone $cartQuery)->count();
        $memberCarts = (clone $cartQuery)->whereHas('customer')->count();
        $guestCarts = max($totalCarts - $memberCarts, 0);

        $cartsWithItems = (clone $cartQuery)->has('items')->count();
        $emptyCarts = max($totalCarts - $cartsWithItems, 0);

        $totalItemQty = (int) CartItem::query()->sum('qty');
        $totalGrand = (float) (clone $cartQuery)->sum('grand_total');
        $averageGrand = (float) (clone $cartQuery)->has('items')->avg('grand_total');
        $averageQtyPerFilledCart = $cartsWithItems > 0
            ? $totalItemQty / $cartsWithItems
            : 0;

        $chart = $this->getLast7DaysCartTrend();

        return [
            Stat::make('Total Keranjang', $this->formatNumber($totalCarts))
                ->description('Dengan item: ' . $this->formatNumber($cartsWithItems) . ' | Kosong: ' . $this->formatNumber($emptyCarts))
                ->descriptionIcon('heroicon-m-shopping-cart', IconPosition::Before)
                ->color('primary')
                ->icon('heroicon-o-shopping-cart')
                ->chart($chart)
                ->extraAttributes([
                    'class' => 'cp-zinc-stat cp-zinc-stat--north',
                ]),

            Stat::make('Keranjang Member', $this->formatNumber($memberCarts))
                ->description('Guest cart: ' . $this->formatNumber($guestCarts))
                ->descriptionIcon('heroicon-m-user', IconPosition::Before)
                ->color('primary')
                ->icon('heroicon-o-user')
                ->extraAttributes([
                    'class' => 'cp-zinc-stat cp-zinc-stat--graphite',
                ]),

            Stat::make('Total Qty Item', $this->formatNumber($totalItemQty))
                ->description('Rata-rata per cart aktif: ' . $this->formatNumber($averageQtyPerFilledCart, 2))
                ->descriptionIcon('heroicon-m-squares-2x2', IconPosition::Before)
                ->color('primary')
                ->icon('heroicon-o-squares-2x2')
                ->extraAttributes([
                    'class' => 'cp-zinc-stat cp-zinc-stat--alloy',
                ]),

            Stat::make('Potensi Nilai Cart', $this->formatCurrencyIdr($totalGrand))
                ->description('Rata-rata nilai cart aktif: ' . $this->formatCurrencyIdr($averageGrand))
                ->descriptionIcon('heroicon-m-banknotes', IconPosition::Before)
                ->color('primary')
                ->icon('heroicon-o-banknotes')
                ->extraAttributes([
                    'class' => 'cp-zinc-stat cp-zinc-stat--chrome',
                ]),
        ];
    }

    /**
     * @return array<int, int>
     */
    protected function getLast7DaysCartTrend(): array
    {
        $startDate = now()->subDays(6)->startOfDay();

        /** @var Collection<string, int> $dailyCounts */
        $dailyCounts = Cart::query()
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->pluck('total', 'date')
            ->map(fn (mixed $total): int => (int) $total);

        return collect(range(0, 6))
            ->map(
                fn (int $dayOffset): int => $dailyCounts->get(
                    now()->subDays(6 - $dayOffset)->toDateString(),
                    0,
                ),
            )
            ->all();
    }

    protected function formatNumber(float|int $number, int $precision = 0): string
    {
        return number_format($number, $precision, ',', '.');
    }

    protected function formatCurrencyIdr(float|int $number): string
    {
        return 'Rp ' . number_format($number, 2, ',', '.');
    }
}
