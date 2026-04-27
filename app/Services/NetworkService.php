<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Support\Collection;

class NetworkService
{
    /**
     * Build network tree for a customer with statistics.
     *
     * @return array<string, mixed>
     */
    public function getNetworkTree(Customer $customer): array
    {
        $allDownlines = $this->getAllDownlines($customer);
        $tree = $this->buildTreeStructure($customer);

        return [
            'statistics' => $this->calculateStatistics($customer, $allDownlines),
            'user' => $this->formatUser($customer),
            'tree' => $tree,
        ];
    }

    /**
     * Get all downlines recursively for a customer.
     *
     * @return Collection<int, Customer>
     */
    protected function getAllDownlines(Customer $customer): Collection
    {
        $downlines = collect();

        // Get direct downlines (sponsored members)
        $directDownlines = $customer->downlines()->get();

        foreach ($directDownlines as $downline) {
            $downlines->push($downline);
            // Recursively get their downlines
            $downlines = $downlines->merge($this->getAllDownlines($downline));
        }

        return $downlines;
    }

    /**
     * Calculate network statistics.
     *
     * @return array<string, int|mixed>
     */
    protected function calculateStatistics(Customer $customer, Collection $allDownlines): array
    {
        $totalDownline = $allDownlines->count();
        $activeMembers = $allDownlines->filter(fn (Customer $c) => $c->status === 3)->count();
        $inactiveMembers = $totalDownline - $activeMembers;

        // Calculate total levels (depth of tree)
        $totalLevels = $this->calculateTreeDepth($customer);

        // Sum all points from downlines
        $totalPoints = $allDownlines->sum('bonus_processed') ?? 0;
        $totalPoints += $customer->bonus_processed ?? 0;

        return [
            'total_downline' => $totalDownline,
            'active_members' => $activeMembers,
            'inactive_members' => $inactiveMembers,
            'total_levels' => $totalLevels,
            'total_points' => (int) $totalPoints,
        ];
    }

    /**
     * Calculate tree depth (maximum level).
     */
    protected function calculateTreeDepth(Customer $customer, int $depth = 0): int
    {
        $downlines = $customer->downlines()->get();

        if ($downlines->isEmpty()) {
            return $depth;
        }

        $maxDepth = $depth;
        foreach ($downlines as $downline) {
            $childDepth = $this->calculateTreeDepth($downline, $depth + 1);
            $maxDepth = max($maxDepth, $childDepth);
        }

        return $maxDepth;
    }

    /**
     * Format user data for response.
     *
     * @return array<string, mixed>
     */
    protected function formatUser(Customer $customer): array
    {
        return [
            'id' => "user_{$customer->id}",
            'username' => $customer->username,
            'name' => $customer->name,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'avatar_url' => null, // Adjust if your system stores avatar URLs
            'level' => 0,
            'is_active' => $customer->status === 3,
            'points' => (int) ($customer->bonus_processed ?? 0),
            'joined_at' => $customer->created_at?->toIso8601String(),
            'sponsor_id' => $customer->sponsor_id ? "user_{$customer->sponsor_id}" : null,
            'sponsor_name' => $customer->sponsor?->name,
        ];
    }

    /**
     * Build recursive tree structure up to a maximum depth.
     *
     * @return array<string, mixed>
     */
    protected function buildTreeStructure(Customer $customer, int $maxDepth = 5): array
    {
        $directDownlines = $customer->downlines()->get();
        $directReferralsCount = $directDownlines->count();

        return [
            'id' => "user_{$customer->id}",
            'name' => $customer->name,
            'username' => $customer->username,
            'level' => 0,
            'is_active' => $customer->status === 3,
            'points' => (int) ($customer->bonus_processed ?? 0),
            'total_downline' => $this->countAllDownlines($customer),
            'direct_referrals' => $directReferralsCount,
            'children' => $this->buildChildrenNodes($directDownlines, 1, $maxDepth),
        ];
    }

    /**
     * Count all downlines recursively.
     */
    protected function countAllDownlines(Customer $customer): int
    {
        $directDownlines = $customer->downlines()->get();
        $count = $directDownlines->count();

        foreach ($directDownlines as $downline) {
            $count += $this->countAllDownlines($downline);
        }

        return $count;
    }

    /**
     * Build children nodes recursively up to a maximum depth.
     *
     * @param  Collection<int, Customer>  $customers
     * @return array<int, array<string, mixed>>
     */
    protected function buildChildrenNodes(Collection $customers, int $level, int $maxDepth = 5): array
    {
        return $customers->map(function (Customer $customer) use ($level, $maxDepth) {
            $childDownlines = $customer->downlines()->get();
            $directReferralsCount = $childDownlines->count();

            return [
                'id' => "user_{$customer->id}",
                'name' => $customer->name,
                'username' => $customer->username,
                'level' => $level,
                'is_active' => $customer->status === 3,
                'points' => (int) ($customer->bonus_processed ?? 0),
                'total_downline' => $this->countAllDownlines($customer),
                'direct_referrals' => $directReferralsCount,
                'children' => $level < $maxDepth ? $this->buildChildrenNodes($childDownlines, $level + 1, $maxDepth) : [],
            ];
        })->values()->all();
    }
}
