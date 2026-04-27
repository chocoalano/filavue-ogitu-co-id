<?php

namespace App\Filament\Resources\Promotions\Pages;

use App\Filament\Resources\Promotions\PromotionResource;
use App\Filament\Resources\Promotions\Widgets\PromotionOverview;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\RenderHook;
use Filament\Schemas\Schema;
use Filament\View\PanelsRenderHook;

class ListPromotions extends ListRecords
{
    protected static string $resource = PromotionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            PromotionOverview::class,
        ];
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Callout::make('Modul Promosi — Slider Banner & Promo Produk Terpadu')
                ->info()
                ->description(
                    'Modul ini digunakan untuk mengelola semua jenis promosi toko: banner slider halaman utama, '
                    .'voucher kode kupon, diskon produk (persen/nominal), flash sale, bundle, gratis ongkir, dan promo khusus member. '
                    .'Setiap promosi dapat dikaitkan dengan beberapa produk sekaligus, masing-masing dengan setelan diskon berbeda.'
                ),

            Callout::make('Cara Kerja Promosi')
                ->warning()
                ->description(
                    '1. Buat promosi baru → isi data umum (kode, nama, tipe, periode, show_on). '
                    .'2. Isi "Kondisi Global" jika ada minimum pembelian, kuota total, atau syarat & ketentuan. '
                    .'3. Buka detail promosi → tab "Produk dalam Promosi" → tambahkan produk yang ikut promo beserta diskon/harga bundle per produk. '
                    .'4. Promosi akan otomatis tampil di dashboard member sesuai lokasi (show_on) dan periode yang ditentukan.'
                ),

            RenderHook::make(PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_BEFORE),
            EmbeddedTable::make(),
            RenderHook::make(PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_AFTER),
        ]);
    }
}
