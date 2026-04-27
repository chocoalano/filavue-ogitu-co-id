<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Pengguna')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email Address')
                    ->searchable()
                    ->copyable() // Memudahkan admin menyalin email
                    ->sortable(),

                // Menggunakan Badge agar role terlihat lebih menonjol
                TextColumn::make('roles.name')
                    ->label('Hak Akses (Roles)')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'super_admin' => 'danger',
                        'admin' => 'warning',
                        'user' => 'success',
                        default => 'gray',
                    })
                    ->searchable(),

                TextColumn::make('email_verified_at')
                    ->label('Status Verifikasi')
                    ->dateTime('d M Y H:i')
                    ->placeholder('Belum Verifikasi')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Tgl Bergabung')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // 1. Filter Berdasarkan Role (Spatie)
                SelectFilter::make('roles')
                    ->label('Filter Peran')
                    ->relationship('roles', 'name')
                    ->preload()
                    ->multiple(), // Memungkinkan filter beberapa role sekaligus

                // 2. Filter Status Verifikasi Email
                Filter::make('email_verified_at')
                    ->label('Hanya Terverifikasi')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('email_verified_at'))
                    ->toggle(),

                // 3. Filter User yang mengaktifkan 2FA
                Filter::make('two_factor_confirmed_at')
                    ->label('2FA Aktif')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('two_factor_confirmed_at'))
                    ->toggle(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Tidak ada user ditemukan')
            ->defaultSort('created_at', 'desc');
    }
}
