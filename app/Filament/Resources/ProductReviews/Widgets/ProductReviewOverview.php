<?php

namespace App\Filament\Resources\ProductReviews\Widgets;

use App\Models\ProductReview;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Collection;

class ProductReviewOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Insight Review Produk';

    protected ?string $description = 'Ringkasan rating, ulasan, dan tingkat persetujuan produk.';

    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $reviewQuery = ProductReview::query();

        $totalReviews = (clone $reviewQuery)->count();
        $approvedReviews = (clone $reviewQuery)->where('is_approved', true)->count();
        $pendingReviews = max($totalReviews - $approvedReviews, 0);
        $verifiedReviews = (clone $reviewQuery)->where('is_verified_purchase', true)->count();
        $positiveReviews = (clone $reviewQuery)->where('rating', '>=', 4)->count();
        $averageRating = (float) (clone $reviewQuery)->avg('rating');

        $approvalRate = $totalReviews > 0 ? ($approvedReviews / $totalReviews) * 100 : 0;
        $verifiedRate = $totalReviews > 0 ? ($verifiedReviews / $totalReviews) * 100 : 0;
        $positiveRate = $totalReviews > 0 ? ($positiveReviews / $totalReviews) * 100 : 0;

        $chart = $this->getLast7DaysReviewTrend();

        return [
            Stat::make('Total Review', $this->formatNumber($totalReviews))
                ->description('Approved: ' . $this->formatNumber($approvedReviews) . ' | Pending: ' . $this->formatNumber($pendingReviews))
                ->descriptionIcon('heroicon-m-chat-bubble-left-right', IconPosition::Before)
                ->color('primary')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->chart($chart)
                ->extraAttributes([
                    'class' => 'cp-zinc-stat cp-zinc-stat--north',
                ]),

            Stat::make('Rata-rata Rating', $this->formatNumber($averageRating, 2))
                ->description('Rating positif (4-5): ' . $this->formatNumber($positiveRate, 2) . '%')
                ->descriptionIcon('heroicon-m-star', IconPosition::Before)
                ->color('primary')
                ->icon('heroicon-o-star')
                ->extraAttributes([
                    'class' => 'cp-zinc-stat cp-zinc-stat--graphite',
                ]),

            Stat::make('Verified Purchase', $this->formatNumber($verifiedReviews))
                ->description('Persentase verified: ' . $this->formatNumber($verifiedRate, 2) . '%')
                ->descriptionIcon('heroicon-m-check-badge', IconPosition::Before)
                ->color('primary')
                ->icon('heroicon-o-check-badge')
                ->extraAttributes([
                    'class' => 'cp-zinc-stat cp-zinc-stat--alloy',
                ]),

            Stat::make('Approval Rate', $this->formatNumber($approvalRate, 2) . '%')
                ->description('Review disetujui: ' . $this->formatNumber($approvedReviews))
                ->descriptionIcon('heroicon-m-shield-check', IconPosition::Before)
                ->color('primary')
                ->icon('heroicon-o-shield-check')
                ->extraAttributes([
                    'class' => 'cp-zinc-stat cp-zinc-stat--chrome',
                ]),
        ];
    }

    /**
     * @return array<int, int>
     */
    protected function getLast7DaysReviewTrend(): array
    {
        $startDate = now()->subDays(6)->startOfDay();

        /** @var Collection<string, int> $dailyCounts */
        $dailyCounts = ProductReview::query()
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
}
