<?php

namespace App\Filament\Resources\CustomerEwallets\Pages;

use App\Filament\Resources\CustomerEwallets\CustomerEwalletResource;
use App\Filament\Resources\CustomerEwallets\Exports\CustomerEwalletExporter;
use App\Filament\Resources\CustomerEwallets\Widgets\CustomerEwalletOverview;
use Filament\Actions\ExportAction;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Database\Eloquent\Builder;

class ManageCustomerEwallets extends ManageRecords
{
    protected static string $resource = CustomerEwalletResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExportAction::make()
                ->label('Download Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->exporter(CustomerEwalletExporter::class)
                ->formats([ExportFormat::Xlsx])
                ->modifyQueryUsing(fn (Builder $query, array $options): Builder => self::applyExportFilters($query, $options)),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CustomerEwalletOverview::class,
        ];
    }

    protected static function applyExportFilters(Builder $query, array $options): Builder
    {
        return $query
            ->when(
                filled($options['has_wallet'] ?? null),
                fn (Builder $builder): Builder => match ($options['has_wallet']) {
                    'yes' => $builder->whereNotNull('ewallet_id'),
                    'no' => $builder->whereNull('ewallet_id'),
                    default => $builder,
                }
            )
            ->when(
                filled($options['status'] ?? null),
                fn (Builder $builder): Builder => $builder->where('status', (int) $options['status'])
            )
            ->when(
                filled($options['min_wallet_balance'] ?? null),
                fn (Builder $builder): Builder => $builder->where('ewallet_saldo', '>=', $options['min_wallet_balance'])
            )
            ->when(
                filled($options['max_wallet_balance'] ?? null),
                fn (Builder $builder): Builder => $builder->where('ewallet_saldo', '<=', $options['max_wallet_balance'])
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
