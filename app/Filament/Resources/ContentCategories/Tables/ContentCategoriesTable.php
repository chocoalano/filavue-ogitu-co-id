<?php

namespace App\Filament\Resources\ContentCategories\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ContentCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable()
                    ->width('60px'),

                ImageColumn::make('thumbnail_url')
                    ->label('Thumbnail')
                    ->square()
                    ->defaultImageUrl(fn () => null)
                    ->toggleable(),

                ColorColumn::make('accent_hex')
                    ->label('Aksen')
                    ->width('60px'),

                TextColumn::make('name')
                    ->label('Nama Kursus')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('icon_key')
                    ->label('Ikon')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('parent.name')
                    ->label('Induk')
                    ->placeholder('—')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('contents_count')
                    ->label('Modul')
                    ->counts('contents')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('parent_id')
                    ->label('Induk Kategori')
                    ->relationship('parent', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('Semua'),
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
