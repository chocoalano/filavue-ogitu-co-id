<?php

namespace App\Filament\Resources\ReportAnalytics\Tables;

use App\Models\ReportAnalytic;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReportAnalyticsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('fullname')
            ->columns([
                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),

                TextColumn::make('tahun_pajak')
                    ->label('Tahun Pajak')
                    ->badge()
                    ->sortable(),

                TextColumn::make('username')
                    ->label('Username')
                    ->searchable(),

                TextColumn::make('fullname')
                    ->label('Nama Lengkap')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('no_telepon')
                    ->label('No. Telepon')
                    ->searchable()
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('npwp')
                    ->label('NPWP')
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('jumlah_bruto')
                    ->label('Jumlah Bruto')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('tarif')
                    ->label('Tarif')
                    ->formatStateUsing(fn (mixed $state): string => $state !== null ? $state . '%' : '-')
                    ->sortable(),

                TextColumn::make('pph21')
                    ->label('PPh21')
                    ->money('IDR')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('tahun_pajak')
                    ->label('Tahun Pajak')
                    ->options(fn (): array => ReportAnalytic::query()
                        ->select('tahun_pajak')
                        ->whereNotNull('tahun_pajak')
                        ->distinct()
                        ->orderByDesc('tahun_pajak')
                        ->pluck('tahun_pajak', 'tahun_pajak')
                        ->toArray())
                    ->placeholder('Semua tahun'),

                SelectFilter::make('npwp_status')
                    ->label('Status NPWP')
                    ->options([
                        'with_npwp' => 'Memiliki NPWP',
                        'without_npwp' => 'Tanpa NPWP',
                    ])
                    ->placeholder('Semua')
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when(
                            $data['value'] === 'with_npwp',
                            fn (Builder $builder): Builder => $builder->whereNotNull('npwp')->where('npwp', '!=', ''),
                        )
                        ->when(
                            $data['value'] === 'without_npwp',
                            fn (Builder $builder): Builder => $builder->where(function (Builder $nested): void {
                                $nested->whereNull('npwp')->orWhere('npwp', '=', '');
                            }),
                        )
                    ),

                Filter::make('tanggal_range')
                    ->label('Rentang Tanggal')
                    ->schema([
                        DatePicker::make('from')
                            ->label('Dari Tanggal'),
                        DatePicker::make('until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->columns(2)
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when(filled($data['from'] ?? null), fn (Builder $builder): Builder => $builder->whereDate('tanggal', '>=', $data['from']))
                        ->when(filled($data['until'] ?? null), fn (Builder $builder): Builder => $builder->whereDate('tanggal', '<=', $data['until']))
                    )
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if (filled($data['from'] ?? null)) {
                            $indicators[] = Indicator::make('Dari ' . $data['from'])->removeField('from');
                        }

                        if (filled($data['until'] ?? null)) {
                            $indicators[] = Indicator::make('Sampai ' . $data['until'])->removeField('until');
                        }

                        return $indicators;
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([]);
    }
}
