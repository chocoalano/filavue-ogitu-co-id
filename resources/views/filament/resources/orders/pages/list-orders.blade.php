<x-filament-panels::page>
    <div class="space-y-4">
        <x-filament::callout
            icon="heroicon-o-information-circle"
            color="info"
        >
            <x-slot name="heading">
                Pusat Monitoring Pesanan Pelanggan
            </x-slot>

            <x-slot name="description">
                Halaman ini menampilkan seluruh pesanan pelanggan yang sudah masuk ke sistem dari proses checkout untuk menjaga konsistensi data item, promo, pembayaran, pengiriman, dan histori transaksi.
            </x-slot>

            <x-slot name="footer">
                <ul class="list-disc space-y-1 pl-5 text-sm">
                    <li>Pesanan berstatus <strong>PAID</strong> diprioritaskan di urutan atas, lalu diikuti data terbaru untuk mempercepat tindak lanjut admin.</li>
                    <li>Gunakan tab status untuk memantau progres pesanan dari <strong>pending</strong>, <strong>paid</strong>, <strong>shipped</strong>, <strong>delivered</strong>, sampai <strong>cancelled</strong>.</li>
                    <li>Buka detail order untuk memverifikasi pelanggan, item, pembayaran, alamat, dan pengiriman sebelum melakukan perubahan data.</li>
                </ul>
            </x-slot>
        </x-filament::callout>

        <div class="grid gap-4 xl:grid-cols-3">
            <x-filament::callout
                icon="heroicon-o-funnel"
                color="gray"
            >
                <x-slot name="heading">
                    Panduan Filter & Audit
                </x-slot>

                <x-slot name="description">
                    Gunakan fitur pencarian, filter, grouping, sorting, dan toggle kolom untuk mempersempit data order sesuai kebutuhan operasional.
                </x-slot>

                <x-slot name="footer">
                    <ul class="list-disc space-y-1 pl-5 text-sm">
                        <li>Filter pelanggan, tipe plan, metode pembayaran, dan nomor pesanan untuk audit transaksi tertentu.</li>
                        <li>Grouping membantu membaca order per status, pelanggan, mata uang, atau tanggal dibuat.</li>
                        <li>Toggle kolom berguna saat Anda ingin fokus ke data order, pembayaran, pengiriman, atau nominal.</li>
                    </ul>
                </x-slot>
            </x-filament::callout>

            <x-filament::callout
                icon="heroicon-o-arrows-right-left"
                color="success"
            >
                <x-slot name="heading">
                    Workflow Status Inline
                </x-slot>

                <x-slot name="description">
                    Kolom status sekarang memakai select inline. Transisi tetap dibatasi ketat agar hanya berjalan <strong>PAID -&gt; SHIPPED -&gt; DELIVERED</strong>.
                </x-slot>

                <x-slot name="footer">
                    <ul class="list-disc space-y-1 pl-5 text-sm">
                        <li>Hanya user dengan permission <strong>Update:Order</strong> yang dapat memakai select status.</li>
                        <li>Perubahan <strong>PAID ke SHIPPED</strong> dari tabel akan membuat shipment default tanpa resi.</li>
                        <li>Perubahan <strong>SHIPPED ke DELIVERED</strong> hanya tersedia jika data shipment sudah ada.</li>
                    </ul>
                </x-slot>
            </x-filament::callout>

            <x-filament::callout
                icon="heroicon-o-cursor-arrow-rays"
                color="warning"
            >
                <x-slot name="heading">
                    Action Penting
                </x-slot>

                <x-slot name="description">
                    Gunakan action per baris untuk kebutuhan lanjutan yang tidak cukup ditangani oleh filter atau update status inline.
                </x-slot>

                <x-slot name="footer">
                    <ul class="list-disc space-y-1 pl-5 text-sm">
                        <li>Action pengiriman tetap tersedia untuk booking Lion Parcel yang membutuhkan data pengiriman lengkap dan nomor resi.</li>
                        <li>Preview dan download invoice tersedia untuk pesanan yang sudah memiliki waktu pembayaran.</li>
                        <li>Action view dan edit dipakai saat Anda perlu memeriksa detail order secara menyeluruh sebelum memprosesnya.</li>
                    </ul>
                </x-slot>
            </x-filament::callout>
        </div>

        <div
            x-data="{
                isLoading: false,
                pendingTabCommits: 0,
                cleanupCommitHook: null,
                init() {
                    const componentId = this.$root.closest('[wire\\:id]')?.getAttribute('wire:id')

                    this.cleanupCommitHook = window.Livewire?.hook('commit', ({ component, commit, succeed, fail }) => {
                        if (! componentId || component?.id !== componentId) {
                            return
                        }

                        if (! Object.prototype.hasOwnProperty.call(commit?.updates ?? {}, 'activeTab')) {
                            return
                        }

                        this.pendingTabCommits += 1
                        this.isLoading = true

                        const stopLoading = () => {
                            this.pendingTabCommits = Math.max(0, this.pendingTabCommits - 1)
                            this.isLoading = this.pendingTabCommits > 0
                        }

                        succeed(() => {
                            requestAnimationFrame(() => {
                                queueMicrotask(stopLoading)
                            })
                        })

                        fail(stopLoading)
                    })
                },
                destroy() {
                    this.cleanupCommitHook?.()
                },
            }"
            class="relative"
        >
            <div
                x-cloak
                x-show="isLoading"
                x-transition.opacity.duration.150ms
                class="pointer-events-auto absolute inset-x-0 bottom-0 top-16 z-20 rounded-xl bg-white/70 backdrop-blur-sm dark:bg-gray-950/70"
            >
                <div
                    role="status"
                    aria-live="polite"
                    class="absolute left-1/2 top-1/2 inline-flex -translate-x-1/2 -translate-y-1/2 items-center gap-3 rounded-xl bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-lg ring-1 ring-gray-950/5 dark:bg-gray-900 dark:text-gray-100 dark:ring-white/10"
                >
                    <x-filament::loading-indicator class="h-5 w-5 text-primary-600" />
                    <span>Memuat data pesanan...</span>
                </div>
            </div>

            <div
                x-bind:class="isLoading ? 'pointer-events-none opacity-60' : ''"
                class="transition-opacity duration-150"
            >
                {{ $this->content }}
            </div>
        </div>
    </div>
</x-filament-panels::page>
