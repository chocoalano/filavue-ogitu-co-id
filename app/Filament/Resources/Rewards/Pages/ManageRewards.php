<?php

namespace App\Filament\Resources\Rewards\Pages;

use App\Filament\Resources\Rewards\RewardResource;
use App\Filament\Resources\Rewards\Widgets\RewardOverview;
use App\Models\CustomerReward;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;

class ManageRewards extends ManageRecords
{
    protected static string $resource = RewardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view_rekap_klaim')
                ->label('Rekap Sudah Klaim')
                ->icon('heroicon-o-clipboard-document-list')
                ->color('gray')
                ->outlined()
                ->action(static fn (): null => null)
                ->slideOver()
                ->modalHeading('Rekap Reward yang Sudah Diklaim')
                ->modalDescription('Ringkasan pencapaian reward oleh member dan daftar yang sudah berstatus achieved.')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Tutup')
                ->modalContent(fn (): View => view(
                    'filament.resources.rewards.modals.rekapitulasi',
                    $this->getRekapViewData(),
                )),

            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            RewardOverview::class,
        ];
    }

    /**
     * @return array{
     *     summary: array{
     *         total_records: int,
     *         pending_count: int,
     *         achieved_count: int,
     *         unique_achieved_members: int,
     *         total_bv_achieved: float,
     *         latest_achieved_at: string|null
     *     },
     *     claimedRewards: list<array{
     *         id: int,
     *         member_username: string|null,
     *         member_name: string|null,
     *         reward_name: string,
     *         total_bv_achieved: float,
     *         type_label: string,
     *         created_at: string|null
     *     }>,
     *     displayedClaimedCount: int
     * }
     */
    protected function getRekapViewData(): array
    {
        $summary = $this->getRekapSummary();
        $claimedRewards = $this->getClaimedRewardRecords();

        return [
            'summary' => $summary,
            'claimedRewards' => $claimedRewards
                ->map(fn (CustomerReward $cr): array => [
                    'id' => (int) $cr->id,
                    'member_username' => $cr->member?->username,
                    'member_name' => $cr->member?->name,
                    'reward_name' => (string) $cr->reward,
                    'total_bv_achieved' => (float) ($cr->total_bv_achieved ?? 0),
                    'type_label' => (int) $cr->type === 1 ? 'Permanen' : 'Periode',
                    'created_at' => $cr->created_at?->format('d M Y H:i'),
                ])
                ->all(),
            'displayedClaimedCount' => $claimedRewards->count(),
        ];
    }

    /**
     * @return array{
     *     total_records: int,
     *     pending_count: int,
     *     achieved_count: int,
     *     unique_achieved_members: int,
     *     total_bv_achieved: float,
     *     latest_achieved_at: string|null
     * }
     */
    protected function getRekapSummary(): array
    {
        $achievedQuery = CustomerReward::query()->where('status', 1);

        $latestAchieved = (clone $achievedQuery)
            ->latest('created_at')
            ->first(['id', 'created_at']);

        return [
            'total_records' => CustomerReward::query()->count(),
            'pending_count' => CustomerReward::query()->where('status', 0)->count(),
            'achieved_count' => (clone $achievedQuery)->count(),
            'unique_achieved_members' => (clone $achievedQuery)
                ->whereNotNull('member_id')
                ->distinct()
                ->count('member_id'),
            'total_bv_achieved' => (float) (clone $achievedQuery)->sum('total_bv_achieved'),
            'latest_achieved_at' => $latestAchieved?->created_at?->format('d M Y H:i'),
        ];
    }

    /**
     * @return Collection<int, CustomerReward>
     */
    protected function getClaimedRewardRecords(int $limit = 25): Collection
    {
        return CustomerReward::query()
            ->with(['member:id,name,username'])
            ->where('status', 1)
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }
}
