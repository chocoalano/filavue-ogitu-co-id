<?php

namespace App\Filament\Resources\Contents\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ContentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable()
                    ->width('55px'),

                ImageColumn::make('thumbnail_url')
                    ->label('Thumb')
                    ->square()
                    ->toggleable(),

                TextColumn::make('category.name')
                    ->label('Kursus')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('title')
                    ->label('Judul Modul')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('content_type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'video' => 'info',
                        'article' => 'success',
                        'pdf' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'published' => 'success',
                        'draft' => 'gray',
                        'archived' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('duration_sec')
                    ->label('Durasi')
                    ->formatStateUsing(fn (?int $state): string => $state
                        ? gmdate('i:s', $state)
                        : '—'
                    )
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('creator.name')
                    ->label('Dibuat Oleh')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

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
                SelectFilter::make('category_id')
                    ->label('Kursus')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('Semua kursus'),

                SelectFilter::make('content_type')
                    ->label('Tipe Konten')
                    ->options([
                        'video' => 'Video',
                        'article' => 'Artikel',
                        'pdf' => 'PDF',
                        'xlsx' => 'XLSX',
                    ])
                    ->placeholder('Semua tipe'),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                        'archived' => 'Archived',
                    ])
                    ->placeholder('Semua status'),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
