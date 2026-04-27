<div class="space-y-6">
    <x-filament::callout
        icon="heroicon-o-information-circle"
        color="info"
    >
        <x-slot name="heading">
            Rekapitulasi Lifetime Cash Reward
        </x-slot>

        <x-slot name="description">
            Data rekap yang sudah klaim diambil dari lifetime cash reward dengan status <strong>Sudah Dirilis</strong>. Untuk meninjau seluruh record, gunakan filter <strong>Status Reward</strong> pada tabel utama.
        </x-slot>
    </x-filament::callout>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Total Catatan Reward</p>
            <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format((int) $summary['total_records'], 0, ',', '.') }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Nominal: Rp {{ number_format((float) $summary['total_amount'], 2, ',', '.') }}</p>
        </div>

        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 shadow-sm dark:border-amber-500/20 dark:bg-amber-500/10">
            <p class="text-xs font-medium uppercase tracking-wide text-amber-700 dark:text-amber-300">Menunggu Pencairan</p>
            <p class="mt-2 text-2xl font-semibold text-amber-900 dark:text-amber-100">{{ number_format((int) $summary['pending_count'], 0, ',', '.') }}</p>
            <p class="mt-1 text-sm text-amber-700 dark:text-amber-300">Nominal: Rp {{ number_format((float) $summary['pending_amount'], 2, ',', '.') }}</p>
        </div>

        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 shadow-sm dark:border-emerald-500/20 dark:bg-emerald-500/10">
            <p class="text-xs font-medium uppercase tracking-wide text-emerald-700 dark:text-emerald-300">Sudah Klaim</p>
            <p class="mt-2 text-2xl font-semibold text-emerald-900 dark:text-emerald-100">{{ number_format((int) $summary['claimed_count'], 0, ',', '.') }}</p>
            <p class="mt-1 text-sm text-emerald-700 dark:text-emerald-300">Nominal: Rp {{ number_format((float) $summary['claimed_amount'], 2, ',', '.') }}</p>
        </div>

        <div class="rounded-xl border border-sky-200 bg-sky-50 p-4 shadow-sm dark:border-sky-500/20 dark:bg-sky-500/10">
            <p class="text-xs font-medium uppercase tracking-wide text-sky-700 dark:text-sky-300">Member Klaim Unik</p>
            <p class="mt-2 text-2xl font-semibold text-sky-900 dark:text-sky-100">{{ number_format((int) $summary['unique_claimed_members'], 0, ',', '.') }}</p>
            <p class="mt-1 text-sm text-sky-700 dark:text-sky-300">
                Klaim terakhir: {{ $summary['latest_claimed_at'] ?: '-' }}
            </p>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
        <div class="flex flex-col gap-2 border-b border-gray-200 px-4 py-4 dark:border-white/10 md:flex-row md:items-center md:justify-between">
            <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Rekap Sudah Klaim</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Menampilkan {{ number_format((int) $displayedClaimedCount, 0, ',', '.') }} data klaim terbaru dari {{ number_format((int) $summary['claimed_count'], 0, ',', '.') }} total data.
                </p>
            </div>

            @if ($summary['claimed_count'] > $displayedClaimedCount)
                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-white/10 dark:text-gray-200">
                    Sisanya dapat dilihat dari filter status pada tabel utama.
                </span>
            @endif
        </div>

        @if ($claimedRewards === [])
            <div class="px-4 py-8 text-sm text-gray-500 dark:text-gray-400">
                Belum ada lifetime cash reward yang berstatus <strong>Sudah Dirilis</strong>.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-white/10">
                    <thead class="bg-gray-50 dark:bg-white/5">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            <th class="px-4 py-3">Member</th>
                            <th class="px-4 py-3">Reward</th>
                            <th class="px-4 py-3">Target</th>
                            <th class="px-4 py-3">BV</th>
                            <th class="px-4 py-3">Nominal</th>
                            <th class="px-4 py-3">Diklaim</th>
                            <th class="px-4 py-3">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                        @foreach ($claimedRewards as $claimedReward)
                            <tr class="align-top text-gray-700 dark:text-gray-200">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $claimedReward['member_name'] ?: '-' }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $claimedReward['member_username'] ?: '-' }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $claimedReward['reward_name'] }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">ID #{{ $claimedReward['id'] }}</div>
                                </td>
                                <td class="px-4 py-3">Rp {{ number_format((float) $claimedReward['reward'], 2, ',', '.') }}</td>
                                <td class="px-4 py-3">{{ number_format((float) $claimedReward['bv'], 2, ',', '.') }}</td>
                                <td class="px-4 py-3 font-medium text-emerald-700 dark:text-emerald-300">
                                    Rp {{ number_format((float) $claimedReward['amount'], 2, ',', '.') }}
                                </td>
                                <td class="px-4 py-3">{{ $claimedReward['created_at'] ?: '-' }}</td>
                                <td class="px-4 py-3">{{ $claimedReward['description'] ?: '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
