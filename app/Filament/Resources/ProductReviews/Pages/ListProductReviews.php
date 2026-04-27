<?php

namespace App\Filament\Resources\ProductReviews\Pages;

use App\Filament\Resources\ProductReviews\ProductReviewResource;
use App\Filament\Resources\ProductReviews\Widgets\ProductReviewOverview;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\RenderHook;
use Filament\Schemas\Schema;
use Filament\View\PanelsRenderHook;

class ListProductReviews extends ListRecords
{
    protected static string $resource = ProductReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ProductReviewOverview::class,
        ];
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Callout::make('Alur Moderasi Review Produk')
                ->info()
                ->description(
                    'Setiap review yang dikirim oleh customer akan masuk ke halaman ini dalam status BELUM DISETUJUI. '
                    .'Admin wajib meninjau isi review — meliputi konten, rating, dan relevansinya — sebelum memutuskan untuk menyetujui atau menolaknya. '
                    .'Gunakan tombol Edit pada baris review lalu aktifkan toggle "Disetujui Tayang" untuk mempublikasikan review tersebut ke halaman produk di toko.'
                ),

            Callout::make('Aturan Tampil di Halaman Toko')
                ->warning()
                ->description(
                    'Hanya review dengan status "Disetujui" yang akan tampil di halaman detail produk pada toko. '
                    .'Review yang belum atau tidak disetujui tidak akan terlihat oleh pengunjung toko sama sekali. '
                    .'Pastikan untuk segera meninjau review baru agar pengalaman belanja pelanggan tetap informatif dan terpercaya.'
                ),

            RenderHook::make(PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_BEFORE),
            EmbeddedTable::make(),
            RenderHook::make(PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_AFTER),
        ]);
    }
}
