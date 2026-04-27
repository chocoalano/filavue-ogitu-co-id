<?php

namespace App\Filament\Resources\ReportAnalytics\Widgets;

use App\Models\CustomerBonusCashback;
use App\Models\CustomerBonusReward;
use App\Models\CustomerBonusSponsor;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportAnalyticWorkbookData
{
    /**
     * @return array{
     *   order_summary:array{rows:list<array<string,mixed>>, total_qty:int, total_amount:float},
     *   omzet_summary:array{rows:list<array<string,mixed>>, register_member:float, upgrade_member:float, retail_order:float, total:float, plan_a_total:float, plan_b_total:float},
     *   bonus_summary:array<string, array<string, mixed>>
     * }
     */
    public static function build(): array
    {
        return once(function (): array {
            $omzetSummary = self::buildOmzetSummary();

            return [
                'order_summary' => self::buildOrderSummary(),
                'omzet_summary' => $omzetSummary,
                'bonus_summary' => self::buildBonusSummary($omzetSummary),
            ];
        });
    }

    /**
     * @return array{rows:list<array<string,mixed>>, total_qty:int, total_amount:float}
     */
    public static function buildOrderSummary(): array
    {
        $items = OrderItem::query()
            ->selectRaw('sku, name, SUM(qty) as total_qty, SUM(row_total) as total_amount')
            ->whereHas('order', fn (Builder $query): Builder => self::qualifyingOrdersQuery($query))
            ->groupBy('sku', 'name')
            ->orderByDesc('total_amount')
            ->orderBy('name')
            ->get();

        $displayRows = $items;
        $maxDisplayRows = 6;

        if ($items->count() > $maxDisplayRows) {
            $displayRows = $items->take($maxDisplayRows - 1);
            $remainingRows = $items->slice($maxDisplayRows - 1);

            $displayRows->push((object) [
                'sku' => '',
                'name' => 'dst',
                'total_qty' => (int) $remainingRows->sum(fn (object $row): int => (int) ($row->total_qty ?? 0)),
                'total_amount' => (float) $remainingRows->sum(fn (object $row): float => (float) ($row->total_amount ?? 0)),
            ]);
        }

        $rows = $displayRows
            ->values()
            ->map(fn (object $row, int $index): array => [
                'number' => $row->name === 'dst' ? null : $index + 1,
                'sku' => trim((string) ($row->sku ?? '')),
                'name' => trim((string) ($row->name ?? '')),
                'qty' => (int) ($row->total_qty ?? 0),
                'amount' => (float) ($row->total_amount ?? 0),
            ])
            ->all();

        while (count($rows) < $maxDisplayRows) {
            $rows[] = [
                'number' => null,
                'sku' => '',
                'name' => '',
                'qty' => null,
                'amount' => null,
            ];
        }

        return [
            'rows' => $rows,
            'total_qty' => (int) $items->sum(fn (object $row): int => (int) ($row->total_qty ?? 0)),
            'total_amount' => (float) $items->sum(fn (object $row): float => (float) ($row->total_amount ?? 0)),
        ];
    }

    /**
     * @return array{rows:list<array<string,mixed>>, register_member:float, upgrade_member:float, retail_order:float, total:float, plan_a_total:float, plan_b_total:float}
     */
    public static function buildOmzetSummary(): array
    {
        $qualifyingOrders = self::qualifyingOrdersQuery(
            Order::query()->select(['id', 'customer_id', 'type', 'grand_total', 'paid_at', 'created_at'])
        )->get();

        $registerMember = 0.0;
        $upgradeMember = 0.0;

        $qualifyingOrders
            ->where('type', 'planA')
            ->groupBy(fn (Order $order): string => (string) ($order->customer_id ?? 'guest-'.$order->id))
            ->each(function (Collection $orders) use (&$registerMember, &$upgradeMember): void {
                $sortedOrders = $orders
                    ->sortBy(fn (Order $order): int => ($order->paid_at ?? $order->created_at ?? now())->getTimestamp())
                    ->values();

                $firstOrder = $sortedOrders->shift();

                if ($firstOrder instanceof Order) {
                    $registerMember += (float) ($firstOrder->grand_total ?? 0);
                }

                $upgradeMember += (float) $sortedOrders->sum(fn (Order $order): float => (float) ($order->grand_total ?? 0));
            });

        $retailOrder = (float) $qualifyingOrders
            ->where('type', 'planB')
            ->sum(fn (Order $order): float => (float) ($order->grand_total ?? 0));

        $totalOmzet = $registerMember + $upgradeMember + $retailOrder;

        return [
            'rows' => [
                [
                    'description' => 'Omzet Register Member (Join Pembelian pertama)',
                    'amount' => $registerMember,
                ],
                [
                    'description' => 'Omzet Upgrade (Penjualan Pribadi ke Member)',
                    'amount' => $upgradeMember,
                ],
                [
                    'description' => 'Omzet Retail Order',
                    'amount' => $retailOrder,
                ],
            ],
            'register_member' => $registerMember,
            'upgrade_member' => $upgradeMember,
            'retail_order' => $retailOrder,
            'plan_a_total' => $registerMember + $upgradeMember,
            'plan_b_total' => $retailOrder,
            'total' => $totalOmzet,
        ];
    }

    /**
     * @param  array{plan_a_total:float, plan_b_total:float}  $omzetSummary
     * @return array<string, array<string, mixed>>
     */
    public static function buildBonusSummary(array $omzetSummary): array
    {
        $planARows = self::decorateBonusRows(
            [
                [
                    'description' => 'Bonus Sponsor (Referral Incentive)',
                    'amount' => (float) CustomerBonusSponsor::query()->where('status', 1)->sum('amount'),
                ],
                [
                    'description' => 'Klaim Promo Reward',
                    'amount' => (float) CustomerBonusReward::query()
                        ->where('status', 1)
                        ->where(function (Builder $query): void {
                            $query->where('reward_type', 'promotion')
                                ->orWhereNull('reward_type');
                        })
                        ->sum('amount'),
                ],
            ],
            (float) ($omzetSummary['plan_a_total'] ?? 0)
        );

        $planBRows = self::decorateBonusRows(
            [
                [
                    'description' => 'Bonus Cashback (Cashback Commission)',
                    'amount' => (float) CustomerBonusCashback::query()->where('status', 1)->sum('amount'),
                ],
            ],
            (float) ($omzetSummary['plan_b_total'] ?? 0)
        );

        return [
            'planA' => [
                'title' => 'Plan A (Network Builder Plan)',
                'rows' => $planARows['rows'],
                'bonus_total' => $planARows['bonus_total'],
                'omzet_total' => (float) ($omzetSummary['plan_a_total'] ?? 0),
                'payout_total' => $planARows['payout_total'],
            ],
            'planB' => [
                'title' => 'Plan B (Retail Plan)',
                'rows' => $planBRows['rows'],
                'bonus_total' => $planBRows['bonus_total'],
                'omzet_total' => (float) ($omzetSummary['plan_b_total'] ?? 0),
                'payout_total' => $planBRows['payout_total'],
            ],
        ];
    }

    /**
     * @param  list<array{description:string, amount:float}>  $rows
     * @return array{rows:list<array<string,mixed>>, bonus_total:float, payout_total:float}
     */
    public static function decorateBonusRows(array $rows, float $omzetTotal): array
    {
        $bonusTotal = collect($rows)->sum(fn (array $row): float => (float) ($row['amount'] ?? 0));

        return [
            'rows' => collect($rows)
                ->map(fn (array $row): array => [
                    'description' => $row['description'],
                    'amount' => (float) ($row['amount'] ?? 0),
                    'bonus_percent' => self::percentage((float) ($row['amount'] ?? 0), $bonusTotal),
                    'payout_percent' => self::percentage((float) ($row['amount'] ?? 0), $omzetTotal),
                ])
                ->all(),
            'bonus_total' => $bonusTotal,
            'payout_total' => self::percentage($bonusTotal, $omzetTotal),
        ];
    }

    public static function qualifyingOrdersQuery(Builder $query): Builder
    {
        $paidLikeStatuses = ['paid', 'processing', 'processed', 'shipped', 'ready_to_ship', 'delivered'];
        $excludedStatuses = ['cancelled', 'canceled', 'cancel', 'refunded', 'refund'];

        return $query
            ->where(function (Builder $builder) use ($paidLikeStatuses): void {
                $builder->whereNotNull('paid_at')
                    ->orWhereIn(DB::raw('LOWER(status)'), $paidLikeStatuses);
            })
            ->whereNotIn(DB::raw('LOWER(status)'), $excludedStatuses);
    }

    public static function formatAmount(float|int|null $amount): string
    {
        return number_format((float) ($amount ?? 0), 0, ',', '.');
    }

    public static function formatPercent(float|int|null $percent): string
    {
        return number_format((float) ($percent ?? 0), 2, ',', '.').'%';
    }

    public static function percentage(float $amount, float $total): float
    {
        if ($total <= 0) {
            return 0.0;
        }

        return round(($amount / $total) * 100, 2);
    }
}
