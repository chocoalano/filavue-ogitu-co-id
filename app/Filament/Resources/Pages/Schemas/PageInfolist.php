<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Models\Page;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Throwable;

class PageInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Halaman')
                    ->description('Data utama halaman statis.')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('title')
                            ->label('Judul')
                            ->columnSpan(3),

                        TextEntry::make('slug')
                            ->label('Slug')
                            ->columnSpan(3)
                            ->placeholder('-'),

                        IconEntry::make('is_published')
                            ->label('Dipublikasikan')
                            ->boolean(),

                        TextEntry::make('template')
                            ->label('Template')
                            ->placeholder('-'),

                        TextEntry::make('show_on')
                            ->label('Show On')
                            ->badge()
                            ->placeholder('-'),

                        TextEntry::make('order')
                            ->label('Urutan')
                            ->numeric()
                            ->placeholder('-'),
                    ]),

                Section::make('SEO')
                    ->description('Data SEO halaman.')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('seo_title')
                            ->label('SEO Title')
                            ->placeholder('-')
                            ->columnSpanFull(),

                        TextEntry::make('seo_description')
                            ->label('SEO Description')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),

                Section::make('Informasi Sistem')
                    ->description('Metadata pembuatan dan pembaruan data.')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Dibuat Pada')
                            ->dateTime()
                            ->placeholder('-'),

                        TextEntry::make('updated_at')
                            ->label('Diperbarui Pada')
                            ->dateTime()
                            ->placeholder('-'),

                        TextEntry::make('deleted_at')
                            ->label('Dihapus Pada')
                            ->dateTime()
                            ->placeholder('-')
                            ->visible(fn (Page $record): bool => $record->trashed()),
                    ]),

                Section::make('Konten Fallback')
                    ->description('Konten utama (non builder).')
                    ->schema([
                        TextEntry::make('content')
                            ->label('Konten')
                            ->formatStateUsing(fn (mixed $state): HtmlString => new HtmlString(self::renderFallbackContent($state)))
                            ->prose()
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                Section::make('Page Builder')
                    ->description('Preview blok konten sesuai konfigurasi PageForm, halaman bisa tampil berbeda pada toko karna sistem render styling dan komponen yang berbeda.')
                    ->schema([
                        View::make('filament.infolists.page-blocks-preview')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ])
            ->columns(3);
    }

    protected static function renderFallbackContent(mixed $content): string
    {
        if (blank($content) || ! is_string($content)) {
            return '-';
        }

        return Str::sanitizeHtml($content);
    }

    public static function resolveImageUrl(string $imagePath): ?string
    {
        if (Str::startsWith($imagePath, ['http://', 'https://', 'data:', '/'])) {
            return $imagePath;
        }

        $disk = (string) (config('filament.default_filesystem_disk') ?? config('filesystems.default'));
        $normalizedImagePath = ltrim($imagePath, '/');

        if (blank($disk)) {
            return $normalizedImagePath;
        }

        try {
            $filesystem = Storage::disk($disk);

            if (is_object($filesystem) && method_exists($filesystem, 'url')) {
                return $filesystem->url($normalizedImagePath);
            }
        } catch (Throwable) {
            //
        }

        if ($disk === 'public') {
            return Str::startsWith($normalizedImagePath, 'storage/')
                ? asset($normalizedImagePath)
                : asset('storage/' . $normalizedImagePath);
        }

        return $normalizedImagePath;
    }
}
