<?php

namespace App\Filament\Resources\CustomerTopups\Pages;

use App\Filament\Resources\CustomerTopups\CustomerTopupResource;
use App\Filament\Resources\CustomerTopups\Exports\CustomerTopupExporter;
use App\Filament\Resources\CustomerTopups\Widgets\CustomerTopupOverview;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Resources\Pages\ManageRecords;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\RenderHook;
use Filament\Schemas\Schema;
use Filament\View\PanelsRenderHook;
use Illuminate\Database\Eloquent\Builder;

class ManageCustomerTopups extends ManageRecords
{
    protected static string $resource = CustomerTopupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExportAction::make()
                ->label('Download Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->exporter(CustomerTopupExporter::class)
                ->formats([ExportFormat::Xlsx])
                ->modifyQueryUsing(fn (Builder $query, array $options): Builder => self::applyExportFilters($query, $options)),
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CustomerTopupOverview::class,
        ];
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Callout::make('Top-Up Saldo (Otomatis via Midtrans)')
                ->success()
                ->description(
                    'Top-up saldo diproses otomatis melalui Midtrans (QRIS/Transfer Bank). '
                    .'Saldo bertambah segera setelah pembayaran dikonfirmasi oleh Midtrans — tidak ada notifikasi WA untuk top-up. '
                    .'Pelanggan dapat memantau saldo langsung di Dashboard aplikasi. '
                    .'Catatan: tombol top-up hanya aktif jika nomor WA pelanggan sudah terkonfirmasi.'
                ),

            RenderHook::make(PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_BEFORE),
            EmbeddedTable::make(),
            RenderHook::make(PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_AFTER),
        ]);
    }

    protected static function applyExportFilters(Builder $query, array $options): Builder
    {
        return $query
            ->where('type', 'topup')
            ->when(
                filled($options['status'] ?? null),
                fn (Builder $builder): Builder => $builder->where('status', (string) $options['status'])
            )
            ->when(
                filled($options['min_wallet_balance'] ?? null),
                fn (Builder $builder): Builder => $builder->whereHas(
                    'customer',
                    fn (Builder $customerQuery): Builder => $customerQuery->where('ewallet_saldo', '>=', $options['min_wallet_balance'])
                )
            )
            ->when(
                filled($options['max_wallet_balance'] ?? null),
                fn (Builder $builder): Builder => $builder->whereHas(
                    'customer',
                    fn (Builder $customerQuery): Builder => $customerQuery->where('ewallet_saldo', '<=', $options['max_wallet_balance'])
                )
            )
            ->when(
                filled($options['date_from'] ?? null),
                fn (Builder $builder): Builder => $builder->where('created_at', '>=', $options['date_from'])
            )
            ->when(
                filled($options['date_until'] ?? null),
                fn (Builder $builder): Builder => $builder->where('created_at', '<=', $options['date_until'])
            );
    }
}
