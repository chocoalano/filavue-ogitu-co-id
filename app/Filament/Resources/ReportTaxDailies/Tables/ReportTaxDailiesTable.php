<?php

namespace App\Filament\Resources\ReportTaxDailies\Tables;

use App\Models\ReportTaxDaily;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReportTaxDailiesTable
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
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Nama')
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
                    ->searchable()
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('nik')
                    ->label('NIK')
                    ->searchable()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

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
                    ->label('Tahun')
                    ->options(fn (): array => ReportTaxDaily::query()
                        ->select('tahun_pajak')
                        ->whereNotNull('tahun_pajak')
                        ->distinct()
                        ->orderByDesc('tahun_pajak')
                        ->pluck('tahun_pajak', 'tahun_pajak')
                        ->toArray())
                    ->placeholder('Semua tahun')
                    ->indicateUsing(fn (array $data): ?string => filled($data['value'] ?? null)
                        ? 'Tahun ' . $data['value']
                        : null
                    ),

                SelectFilter::make('bulan')
                    ->label('Bulan')
                    ->options([
                        1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
                        4 => 'April', 5 => 'Mei', 6 => 'Juni',
                        7 => 'Juli', 8 => 'Agustus', 9 => 'September',
                        10 => 'Oktober', 11 => 'November', 12 => 'Desember',
                    ])
                    ->placeholder('Semua bulan')
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when(filled($data['value'] ?? null), fn (Builder $builder): Builder => $builder->whereMonth('tanggal', (int) $data['value']))
                    )
                    ->indicateUsing(function (array $data): ?Indicator {
                        if (! filled($data['value'] ?? null)) {
                            return null;
                        }

                        $months = [
                            1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
                            4 => 'April', 5 => 'Mei', 6 => 'Juni',
                            7 => 'Juli', 8 => 'Agustus', 9 => 'September',
                            10 => 'Oktober', 11 => 'November', 12 => 'Desember',
                        ];

                        return Indicator::make($months[(int) $data['value']] ?? '')->removeField('bulan');
                    }),

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
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([]);
    }
}
