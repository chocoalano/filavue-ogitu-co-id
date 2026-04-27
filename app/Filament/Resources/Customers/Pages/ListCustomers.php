<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Exports\CustomerExporter;
use App\Filament\Resources\Customers\CustomerResource;
use App\Filament\Resources\Customers\Widgets\CustomerOverview;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\RenderHook;
use Filament\Schemas\Schema;
use Filament\View\PanelsRenderHook;
use Illuminate\Database\Eloquent\Builder;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExportAction::make()
                ->exporter(CustomerExporter::class)
                ->label('Export Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->formats([ExportFormat::Xlsx])
                ->modifyQueryUsing(function (Builder $query, array $options): Builder {
                    if (! empty($options['date_from'])) {
                        $query->whereDate('created_at', '>=', $options['date_from']);
                    }

                    if (! empty($options['date_to'])) {
                        $query->whereDate('created_at', '<=', $options['date_to']);
                    }

                    return $query;
                }),

            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CustomerOverview::class,
        ];
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Callout::make('Registrasi & Verifikasi Nomor WA Pelanggan')
                ->info()
                ->description(
                    'Sebelum bisa mengajukan penarikan, pelanggan wajib memverifikasi nomor WhatsApp-nya. Saat mendaftar, pelanggan diarahkan ke WhatsApp dengan teks pesan yang sudah terisi otomatis (nama & link konfirmasi). Pelanggan wajib menekan tombol Kirim — sistem mencatat nomor tersebut valid saat pesan masuk ke gateway. Lalu pelanggan klik link di dalam pesan WA untuk mengaktifkan akun. Jika nomor belum terdeteksi, admin dapat mengkonfirmasi secara manual melalui tabel Data Member.'
                ),

            RenderHook::make(PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_BEFORE),
            EmbeddedTable::make(),
            RenderHook::make(PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_AFTER),
        ]);
    }
}
