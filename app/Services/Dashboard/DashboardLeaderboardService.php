<?php

namespace App\Services\Dashboard;

use App\Models\Customer;
use App\Models\CustomerBonus;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class DashboardLeaderboardService
{
    /**
     * @var array<int, string>
     */
    private const TABS = [
        1 => 'Harian',
        2 => 'Mingguan',
        3 => 'Bulanan',
    ];

    private const LEADERBOARD_LIMIT = 100;

    /**
     * @return array{
     *   tabs:list<string>,
     *   selected_tab:int,
     *   my_rank:array{
     *     rank:int,
     *     name:string,
     *     avatar:string,
     *     level:string,
     *     trend:string,
     *     streak:int,
     *     points:int
     *   },
     *   leaderboard:list<array{
     *     id:int,
     *     name:string,
     *     avatar:string,
     *     level:string,
     *     trend:string,
     *     streak:int,
     *     points:int
     *   }>
     * }
     */
    public function getLeaderboardData(Customer $authenticatedCustomer, string $periodKey, int $selectedTab): array
    {
        $window = $this->resolvePeriodWindow($periodKey);
        $leaderboardContext = $this->buildLeaderboardContext(
            $authenticatedCustomer,
            $window['start'],
            $window['end'],
        );

        $fallbackToAllTime = ! $leaderboardContext['has_positive_points'];

        if ($fallbackToAllTime) {
            $fallbackContext = $this->buildLeaderboardContext(
                $authenticatedCustomer,
                null,
                null,
            );

            if ($fallbackContext['has_positive_points']) {
                $leaderboardContext = $fallbackContext;
            } else {
                $fallbackToAllTime = false;
            }
        }

        $leaderboardRows = $leaderboardContext['leaderboard_rows'];
        $myRow = $leaderboardContext['my_row'];
        $rank = $leaderboardContext['rank'];

        $relevantCustomerIds = $this->resolveRelevantCustomerIds($leaderboardRows, (int) $authenticatedCustomer->id);

        $trendMap = $fallbackToAllTime
            ? $this->buildNeutralTrendMap($relevantCustomerIds)
            : $this->buildTrendMap(
                $relevantCustomerIds,
                $this->buildPointsMap($relevantCustomerIds, $window['start'], $window['end']),
                $this->buildPointsMap($relevantCustomerIds, $window['previous_start'], $window['previous_end']),
            );

        $streakMap = $this->buildStreakMap($relevantCustomerIds, $window['reference_date']);

        return [
            'tabs' => array_values(self::TABS),
            'selected_tab' => $selectedTab,
            'my_rank' => $this->formatMyRank(
                $myRow,
                $rank,
                $trendMap[(int) $authenticatedCustomer->id] ?? 'neutral',
                $streakMap[(int) $authenticatedCustomer->id] ?? 0
            ),
            'leaderboard' => $this->formatLeaderboardRows($leaderboardRows, $trendMap, $streakMap),
        ];
    }

    /**
     * @return array{
     *   start:CarbonInterface,
     *   end:CarbonInterface,
     *   previous_start:CarbonInterface,
     *   previous_end:CarbonInterface,
     *   reference_date:CarbonInterface
     * }
     */
    private function resolvePeriodWindow(string $periodKey): array
    {
        $today = CarbonImmutable::now()->startOfDay();

        return match ($periodKey) {
            'weekly' => [
                'start' => $today->startOfWeek(CarbonInterface::MONDAY),
                'end' => $today->endOfWeek(CarbonInterface::SUNDAY),
                'previous_start' => $today->startOfWeek(CarbonInterface::MONDAY)->subWeek(),
                'previous_end' => $today->endOfWeek(CarbonInterface::SUNDAY)->subWeek(),
                'reference_date' => $today,
            ],
            'monthly' => [
                'start' => $today->startOfMonth(),
                'end' => $today->endOfMonth(),
                'previous_start' => $today->subMonthNoOverflow()->startOfMonth(),
                'previous_end' => $today->subMonthNoOverflow()->endOfMonth(),
                'reference_date' => $today,
            ],
            default => [
                'start' => $today,
                'end' => $today,
                'previous_start' => $today->subDay(),
                'previous_end' => $today->subDay(),
                'reference_date' => $today,
            ],
        };
    }

    /**
     * @return Builder<Customer>
     */
    private function scoreQuery(?CarbonInterface $start, ?CarbonInterface $end): Builder
    {
        $periodBonusQuery = CustomerBonus::query()
            ->selectRaw('member_id, COALESCE(SUM(index_value), 0) as points')
            ->groupBy('member_id');

        if ($start !== null && $end !== null) {
            $periodBonusQuery
                ->whereDate('date', '>=', $start->toDateString())
                ->whereDate('date', '<=', $end->toDateString());
        }

        return Customer::query()
            ->leftJoinSub($periodBonusQuery, 'period_bonus', function ($join): void {
                $join->on('period_bonus.member_id', '=', 'customers.id');
            })
            ->select([
                'customers.id',
                'customers.name',
                'customers.level',
                'customers.package_id',
                'customers.status',
            ])
            ->selectRaw('COALESCE(period_bonus.points, 0) as points');
    }

    /**
     * @return Builder<Customer>
     */
    private function rankBaseQuery(?CarbonInterface $start, ?CarbonInterface $end): Builder
    {
        return $this->scoreQuery($start, $end);
    }

    /**
     * @param  Collection<int, Customer>  $leaderboardRows
     * @return list<int>
     */
    private function resolveRelevantCustomerIds(Collection $leaderboardRows, int $authenticatedCustomerId): array
    {
        return $leaderboardRows
            ->pluck('id')
            ->push($authenticatedCustomerId)
            ->map(static fn (mixed $id): int => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  list<int>  $customerIds
     * @return array<int, float>
     */
    private function buildPointsMap(array $customerIds, ?CarbonInterface $start, ?CarbonInterface $end): array
    {
        if ($customerIds === []) {
            return [];
        }

        $pointsQuery = CustomerBonus::query()
            ->selectRaw('member_id, COALESCE(SUM(index_value), 0) as points')
            ->whereIn('member_id', $customerIds)
            ->groupBy('member_id');

        if ($start !== null && $end !== null) {
            $pointsQuery
                ->whereDate('date', '>=', $start->toDateString())
                ->whereDate('date', '<=', $end->toDateString());
        }

        /** @var Collection<int|string, float|int|string> $pointsByCustomer */
        $pointsByCustomer = $pointsQuery->pluck('points', 'member_id');

        /** @var array<int, float> $normalized */
        $normalized = $pointsByCustomer
            ->mapWithKeys(static fn (mixed $points, mixed $memberId): array => [
                (int) $memberId => (float) $points,
            ])
            ->all();

        return $normalized;
    }

    /**
     * @param  list<int>  $customerIds
     * @param  array<int, float>  $currentPeriodPoints
     * @param  array<int, float>  $previousPeriodPoints
     * @return array<int, string>
     */
    private function buildTrendMap(array $customerIds, array $currentPeriodPoints, array $previousPeriodPoints): array
    {
        $trendMap = [];

        foreach ($customerIds as $customerId) {
            $currentPoints = $currentPeriodPoints[$customerId] ?? 0.0;
            $previousPoints = $previousPeriodPoints[$customerId] ?? 0.0;

            $trendMap[$customerId] = $this->resolveTrend($currentPoints, $previousPoints);
        }

        return $trendMap;
    }

    /**
     * @param  list<int>  $customerIds
     * @return array<int, string>
     */
    private function buildNeutralTrendMap(array $customerIds): array
    {
        $trendMap = [];

        foreach ($customerIds as $customerId) {
            $trendMap[$customerId] = 'neutral';
        }

        return $trendMap;
    }

    /**
     * @param  list<int>  $customerIds
     * @return array<int, int>
     */
    private function buildStreakMap(array $customerIds, CarbonInterface $referenceDate): array
    {
        if ($customerIds === []) {
            return [];
        }

        $rows = CustomerBonus::query()
            ->whereIn('member_id', $customerIds)
            ->whereDate('date', '<=', $referenceDate->toDateString())
            ->where('index_value', '>', 0)
            ->orderByDesc('date')
            ->get([
                'member_id',
                'date',
            ]);

        /** @var array<int, int> $streakMap */
        $streakMap = [];

        foreach ($customerIds as $customerId) {
            $dates = $rows
                ->where('member_id', $customerId)
                ->pluck('date')
                ->filter()
                ->map(static fn (mixed $date): string => CarbonImmutable::parse((string) $date)->toDateString())
                ->unique()
                ->values()
                ->all();

            $streakMap[$customerId] = $this->countConsecutiveStreak($dates, $referenceDate);
        }

        return $streakMap;
    }

    /**
     * @param  list<string>  $dates
     */
    private function countConsecutiveStreak(array $dates, CarbonInterface $referenceDate): int
    {
        $streak = 0;
        $expectedDate = CarbonImmutable::parse($referenceDate->toDateString());

        foreach ($dates as $date) {
            if ($date === $expectedDate->toDateString()) {
                $streak++;
                $expectedDate = $expectedDate->subDay();

                continue;
            }

            if ($date > $expectedDate->toDateString()) {
                continue;
            }

            break;
        }

        return $streak;
    }

    /**
     * @param  Collection<int, Customer>  $rows
     * @param  array<int, string>  $trendMap
     * @param  array<int, int>  $streakMap
     * @return list<array{
     *   id:int,
     *   name:string,
     *   avatar:string,
     *   level:string,
     *   trend:string,
     *   streak:int,
     *   points:int
     * }>
     */
    private function formatLeaderboardRows(Collection $rows, array $trendMap, array $streakMap): array
    {
        return $rows
            ->map(function (Customer $customer) use ($trendMap, $streakMap): array {
                $customerId = (int) $customer->id;

                return [
                    'id' => $customerId,
                    'name' => $this->resolveDisplayName($customer),
                    'avatar' => $this->resolveAvatar($customer),
                    'level' => $this->resolveLevel($customer),
                    'trend' => $trendMap[$customerId] ?? 'neutral',
                    'streak' => $streakMap[$customerId] ?? 0,
                    'points' => $this->resolvePoints($customer),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array{
     *   rank:int,
     *   name:string,
     *   avatar:string,
     *   level:string,
     *   trend:string,
     *   streak:int,
     *   points:int
     * }
     */
    private function formatMyRank(Customer $customer, int $rank, string $trend, int $streak): array
    {
        return [
            'rank' => $rank,
            'name' => $this->resolveDisplayName($customer),
            'avatar' => $this->resolveAvatar($customer),
            'level' => $this->resolveLevel($customer),
            'trend' => $trend,
            'streak' => $streak,
            'points' => $this->resolvePoints($customer),
        ];
    }

    private function resolveDisplayName(Customer $customer): string
    {
        $name = trim((string) ($customer->name ?? ''));

        if ($name !== '') {
            return $name;
        }

        return 'Member #'.(int) $customer->id;
    }

    private function resolveAvatar(Customer $customer): string
    {
        $name = $this->resolveDisplayName($customer);
        $segments = collect(preg_split('/\s+/', trim($name)) ?: [])
            ->filter(static fn (mixed $segment): bool => trim((string) $segment) !== '')
            ->values();

        if ($segments->count() >= 2) {
            return strtoupper(
                Str::substr((string) $segments->get(0), 0, 1).
                Str::substr((string) $segments->get(1), 0, 1)
            );
        }

        return strtoupper(Str::substr((string) ($segments->first() ?? 'M'), 0, 2));
    }

    private function resolveLevel(Customer $customer): string
    {
        $customerLevel = trim((string) ($customer->level ?? ''));

        if ($customerLevel !== '') {
            return $customerLevel;
        }

        $packageName = trim((string) ($customer->package?->name ?? ''));

        if ($packageName !== '') {
            return $packageName;
        }

        return match ((int) ($customer->status ?? 1)) {
            3 => 'Active Member',
            2 => 'Passive Member',
            default => 'Prospect Member',
        };
    }

    private function resolvePoints(Customer $customer): int
    {
        return (int) round((float) ($customer->getAttribute('points') ?? 0));
    }

    private function resolveTrend(float $currentPoints, float $previousPoints): string
    {
        if ($currentPoints > $previousPoints) {
            return 'up';
        }

        if ($currentPoints < $previousPoints) {
            return 'down';
        }

        return 'neutral';
    }

    /**
     * @return array{
     *   leaderboard_rows:Collection<int, Customer>,
     *   my_row:Customer,
     *   rank:int,
     *   has_positive_points:bool
     * }
     */
    private function buildLeaderboardContext(
        Customer $authenticatedCustomer,
        ?CarbonInterface $start,
        ?CarbonInterface $end,
    ): array {
        $rankBaseQuery = $this->rankBaseQuery($start, $end);

        $leaderboardRows = (clone $rankBaseQuery)
            ->with('package:id,name')
            ->orderByDesc('points')
            ->orderBy('customers.id')
            ->limit(self::LEADERBOARD_LIMIT)
            ->get();

        $myRow = $leaderboardRows
            ->first(fn (Customer $customer): bool => (int) $customer->id === (int) $authenticatedCustomer->id);

        if (! $myRow instanceof Customer) {
            $myRow = $authenticatedCustomer;
            $myRow->setAttribute('points', 0);
        }

        $rank = (int) ($leaderboardRows
            ->search(fn (Customer $customer): bool => (int) $customer->id === (int) $authenticatedCustomer->id) ?? 0) + 1;

        $hasPositivePoints = $leaderboardRows
            ->contains(fn (Customer $customer): bool => $this->resolvePoints($customer) > 0);

        return [
            'leaderboard_rows' => $leaderboardRows,
            'my_row' => $myRow,
            'rank' => $rank,
            'has_positive_points' => $hasPositivePoints,
        ];
    }
}
