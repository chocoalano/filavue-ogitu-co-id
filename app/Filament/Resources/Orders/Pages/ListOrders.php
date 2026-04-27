<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Support\Orders\OrderTabCountsCache;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ListOrders extends ListRecords
{
    protected string $view = 'filament.resources.orders.pages.list-orders';

    protected static string $resource = OrderResource::class;

    /**
     * @return array<string, Tab>
     */
    public function getTabs(): array
    {
        $tabCounts = OrderTabCountsCache::counts();

        return [
            'all' => Tab::make('Semua')
                ->badge((string) ($tabCounts['all'] ?? 0)),

            'pending' => Tab::make('Pending')
                ->badge((string) ($tabCounts['pending'] ?? 0))
                ->badgeColor('warning'),

            'paid' => Tab::make('Paid')
                ->badge((string) ($tabCounts['paid'] ?? 0))
                ->badgeColor('success'),

            'shipped' => Tab::make('Shipped')
                ->badge((string) ($tabCounts['shipped'] ?? 0))
                ->badgeColor('info'),

            'delivered' => Tab::make('Delivered')
                ->badge((string) ($tabCounts['delivered'] ?? 0))
                ->badgeColor('success'),

            'cancelled' => Tab::make('Cancelled')
                ->badge((string) ($tabCounts['cancelled'] ?? 0))
                ->badgeColor('danger'),
        ];
    }

    public function table(Table $table): Table
    {
        return parent::table($table)
            ->deferLoading()
            ->modifyQueryUsing(function (Builder $query): Builder {
                $statuses = OrderTabCountsCache::statusesForTab($this->activeTab);

                if ($statuses === []) {
                    return $query;
                }

                return $this->applyStatusFilter($query, $statuses);
            });
    }

    /**
     * @param  array<int, string>  $statuses
     */
    private function applyStatusFilter(Builder $query, array $statuses): Builder
    {
        $placeholders = implode(', ', array_fill(0, count($statuses), '?'));

        return $query->whereRaw("LOWER(TRIM(status)) IN ({$placeholders})", $statuses);
    }
}
