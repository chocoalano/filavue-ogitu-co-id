<?php

namespace App\Filament\Resources\Contents\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ContentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(12)->schema([

                Section::make('Informasi Modul')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        TextEntry::make('category.name')
                            ->label('Kursus'),

                        TextEntry::make('sort_order')
                            ->label('Urutan Modul'),

                        TextEntry::make('title')
                            ->label('Judul')
                            ->columnSpanFull(),

                        TextEntry::make('slug')
                            ->label('Slug'),

                        TextEntry::make('content_type')
                            ->label('Tipe Konten')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'video' => 'info',
                                'article' => 'success',
                                'pdf' => 'warning',
                                default => 'gray',
                            }),

                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'published' => 'success',
                                'draft' => 'gray',
                                'archived' => 'danger',
                                default => 'gray',
                            }),

                        TextEntry::make('duration_sec')
                            ->label('Durasi Video')
                            ->formatStateUsing(fn (?int $state): string => $state
                                ? gmdate('H:i:s', $state)
                                : '—'
                            ),

                        TextEntry::make('creator.name')
                            ->label('Dibuat Oleh')
                            ->placeholder('—'),

                        TextEntry::make('created_at')
                            ->label('Dibuat')
                            ->dateTime()
                            ->placeholder('—'),

                        TextEntry::make('updated_at')
                            ->label('Diperbarui')
                            ->dateTime()
                            ->placeholder('—'),
                    ])
                    ->columns(2)
                    ->columnSpan(['default' => 12, 'lg' => 8]),

                Section::make('Media')
                    ->icon('heroicon-o-photo')
                    ->schema([
                        ImageEntry::make('thumbnail_url')
                            ->label('Thumbnail')
                            ->columnSpanFull(),

                        TextEntry::make('vlink')
                            ->label('URL Video')
                            ->url(fn (?string $state): ?string => filled($state) ? $state : null)
                            ->openUrlInNewTab()
                            ->placeholder('—')
                            ->columnSpanFull(),

                        TextEntry::make('file')
                            ->label('File Lampiran')
                            ->placeholder('—'),
                    ])
                    ->columnSpan(['default' => 12, 'lg' => 4]),

                Section::make('Isi Konten')
                    ->icon('heroicon-o-document')
                    ->schema([
                        TextEntry::make('content')
                            ->label('')
                            ->html()
                            ->placeholder('Tidak ada konten teks.')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull()
                    ->collapsed(),
            ])->columnSpanFull(),
        ]);
    }
}
