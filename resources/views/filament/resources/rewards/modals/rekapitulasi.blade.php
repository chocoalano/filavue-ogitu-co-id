<div class="space-y-6">
    <x-filament::callout
        icon="heroicon-o-information-circle"
        color="info"
    >
        <x-slot name="heading">
            Rekap Reward yang Sudah Diklaim
        </x-slot>

        <x-slot name="description">
            Data rekap diambil dari pencapaian reward member dengan status <strong>Achieved</strong>. Untuk meninjau seluruh record, gunakan tabel master reward dan relasi <strong>Customer Rewards</strong>.
        </x-slot>
    </x-filament::callout>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Total Pencapaian</p>
            <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">
                {{ number_format((int) $summary['total_records'], 0, ',', '.') }}
            </p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Semua catatan pencapaian reward
            </p>
        </div>

        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 shadow-sm dark:border-amber-500/20 dark:bg-amber-500/10">
            <p class="text-xs font-medium uppercase tracking-wide text-amber-700 dark:text-amber-300">Menunggu (Pending)</p>
            <p class="mt-2 text-2xl font-semibold text-amber-900 dark:text-amber-100">
                {{ number_format((int) $summary['pending_count'], 0, ',', '.') }}
            </p>
            <p class="mt-1 text-sm text-amber-700 dark:text-amber-300">
                Belum mencapai target reward
            </p>
        </div>

        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 shadow-sm dark:border-emerald-500/20 dark:bg-emerald-500/10">
            <p class="text-xs font-medium uppercase tracking-wide text-emerald-700 dark:text-emerald-300">Sudah Klaim (Achieved)</p>
            <p class="mt-2 text-2xl font-semibold text-emerald-900 dark:text-emerald-100">
                {{ number_format((int) $summary['achieved_count'], 0, ',', '.') }}
            </p>
            <p class="mt-1 text-sm text-emerald-700 dark:text-emerald-300">
                Klaim terakhir: {{ $summary['latest_achieved_at'] ?: '-' }}
            </p>
        </div>

        <div class="rounded-xl border border-sky-200 bg-sky-50 p-4 shadow-sm dark:border-sky-500/20 dark:bg-sky-500/10">
            <p class="text-xs font-medium uppercase tracking-wide text-sky-700 dark:text-sky-300">Member Unik (Achieved)</p>
            <p class="mt-2 text-2xl font-semibold text-sky-900 dark:text-sky-100">
                {{ number_format((int) $summary['unique_achieved_members'], 0, ',', '.') }}
            </p>
            <p class="mt-1 text-sm text-sky-700 dark:text-sky-300">
                Member berbeda yang meraih reward
            </p>
        </div>

        <div class="rounded-xl border border-violet-200 bg-violet-50 p-4 shadow-sm sm:col-span-2 dark:border-violet-500/20 dark:bg-violet-500/10 xl:col-span-2">
            <p class="text-xs font-medium uppercase tracking-wide text-violet-700 dark:text-violet-300">Total BV Tercapai</p>
            <p class="mt-2 text-2xl font-semibold text-violet-900 dark:text-violet-100">
                {{ number_format((float) $summary['total_bv_achieved'], 2, ',', '.') }}
            </p>
            <p class="mt-1 text-sm text-violet-700 dark:text-violet-300">
                Akumulasi Business Volume dari semua pencapaian
            </p>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
        <div class="flex flex-col gap-2 border-b border-gray-200 px-4 py-4 dark:border-white/10 md:flex-row md:items-center md:justify-between">
            <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Daftar Klaim Terbaru</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Menampilkan {{ number_format((int) $displayedClaimedCount, 0, ',', '.') }} data klaim terbaru
                    dari {{ number_format((int) $summary['achieved_count'], 0, ',', '.') }} total klaim.
                </p>
            </div>

            @if ($summary['achieved_count'] > $displayedClaimedCount)
                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-white/10 dark:text-gray-200">
                    Sisa data dapat dilihat dari tabel reward master.
                </span>
            @endif
        </div>

        @if ($claimedRewards === [])
            <div class="px-4 py-8 text-sm text-gray-500 dark:text-gray-400">
                Belum ada reward yang berstatus <strong>Achieved</strong>.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-white/10">
                    <thead class="bg-gray-50 dark:bg-white/5">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            <th class="px-4 py-3">Member</th>
                            <th class="px-4 py-3">Reward</th>
                            <th class="px-4 py-3">Total BV Dicapai</th>
                            <th class="px-4 py-3">Tipe</th>
                            <th class="px-4 py-3">Dicapai Pada</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                        @foreach ($claimedRewards as $cr)
                            <tr class="align-top text-gray-700 dark:text-gray-200">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $cr['member_name'] ?: '-' }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $cr['member_username'] ?: '-' }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $cr['reward_name'] }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">ID #{{ $cr['id'] }}</div>
                                </td>
                                <td class="px-4 py-3 font-medium text-violet-700 dark:text-violet-300">
                                    {{ number_format((float) $cr['total_bv_achieved'], 2, ',', '.') }}
                                </td>
                                <td class="px-4 py-3">
                                    <span @class([
                                        'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium',
                                        'bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-300' => $cr['type_label'] === 'Permanen',
                                        'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300' => $cr['type_label'] === 'Periode',
                                    ])>
                                        {{ $cr['type_label'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">{{ $cr['created_at'] ?: '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
