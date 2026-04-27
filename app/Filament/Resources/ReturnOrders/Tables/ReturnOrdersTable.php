<?php

namespace App\Filament\Resources\ReturnOrders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReturnOrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('ReturnOrder')
            ->defaultSort('requested_at', 'desc')
            ->columns([
                TextColumn::make('order.order_no')
                    ->label('Order')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(fn (?string $state): string => $state ? ucfirst($state) : '-')
                    ->color(fn (?string $state): string => match (strtolower((string) $state)) {
                        'pending' => 'warning',
                        'approved' => 'info',
                        'rejected' => 'danger',
                        'received' => 'primary',
                        'inspected' => 'primary',
                        'completed' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('requested_at')
                    ->label('Diajukan')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('processed_at')
                    ->label('Diproses')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status Retur')
                    ->placeholder('Semua status')
                    ->options(self::statusOptions()),

                SelectFilter::make('order_id')
                    ->label('Nomor Pesanan')
                    ->relationship('order', 'order_no')
                    ->searchable()
                    ->preload()
                    ->placeholder('Semua pesanan'),

                TernaryFilter::make('has_processed_at')
                    ->label('Status Proses')
                    ->placeholder('Semua')
                    ->trueLabel('Sudah diproses')
                    ->falseLabel('Belum diproses')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->whereNotNull('processed_at'),
                        false: fn (Builder $query): Builder => $query->whereNull('processed_at'),
                        blank: fn (Builder $query): Builder => $query,
                    ),

                Filter::make('requested_between')
                    ->label('Rentang Waktu Pengajuan')
                    ->schema([
                        DateTimePicker::make('from')->label('Dari Tanggal Pengajuan')->seconds(false),
                        DateTimePicker::make('until')->label('Sampai Tanggal Pengajuan')->seconds(false),
                    ])
                    ->columns(2)
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when(filled($data['from'] ?? null), fn (Builder $builder): Builder => $builder->where('requested_at', '>=', $data['from']))
                        ->when(filled($data['until'] ?? null), fn (Builder $builder): Builder => $builder->where('requested_at', '<=', $data['until']))
                    )
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if (filled($data['from'] ?? null)) {
                            $indicators[] = Indicator::make('Diajukan ≥ ' . $data['from'])->removeField('from');
                        }

                        if (filled($data['until'] ?? null)) {
                            $indicators[] = Indicator::make('Diajukan ≤ ' . $data['until'])->removeField('until');
                        }

                        return $indicators;
                    }),

                Filter::make('processed_between')
                    ->label('Rentang Waktu Diproses')
                    ->schema([
                        DateTimePicker::make('from')->label('Dari Tanggal Diproses')->seconds(false),
                        DateTimePicker::make('until')->label('Sampai Tanggal Diproses')->seconds(false),
                    ])
                    ->columns(2)
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when(filled($data['from'] ?? null), fn (Builder $builder): Builder => $builder->where('processed_at', '>=', $data['from']))
                        ->when(filled($data['until'] ?? null), fn (Builder $builder): Builder => $builder->where('processed_at', '<=', $data['until']))
                    )
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if (filled($data['from'] ?? null)) {
                            $indicators[] = Indicator::make('Diproses ≥ ' . $data['from'])->removeField('from');
                        }

                        if (filled($data['until'] ?? null)) {
                            $indicators[] = Indicator::make('Diproses ≤ ' . $data['until'])->removeField('until');
                        }

                        return $indicators;
                    }),

                Filter::make('reason_keyword')
                    ->label('Kata Kunci Alasan')
                    ->schema([
                        TextInput::make('q')
                            ->label('Cari di Alasan Retur')
                            ->placeholder('contoh: rusak / salah kirim / ukuran')
                            ->maxLength(200),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when(
                            filled($data['q'] ?? null),
                            fn (Builder $builder): Builder => $builder->where('reason', 'like', '%' . trim((string) $data['q']) . '%')
                        )
                    )
                    ->indicateUsing(function (array $data): array {
                        $keyword = trim((string) ($data['q'] ?? ''));

                        return $keyword !== ''
                            ? [Indicator::make("Alasan: {$keyword}")->removeField('q')]
                            : [];
                    }),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->filtersFormColumns(6)
            ->filtersFormSchema(fn (array $filters): array => [
                $filters['order_id']->columnSpan(1),
                $filters['status']->columnSpan(1),
                $filters['has_processed_at']->columnSpan(1),
                $filters['reason_keyword']->columnSpan(3),
                $filters['requested_between']->columnSpan(3),
                $filters['processed_between']->columnSpan(3),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    private static function statusOptions(): array
    {
        return [
            'pending' => 'Menunggu',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'received' => 'Barang Diterima',
            'inspected' => 'Dicek',
            'completed' => 'Selesai',
        ];
    }
}
