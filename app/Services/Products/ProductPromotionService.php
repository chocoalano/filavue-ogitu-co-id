<?php

namespace App\Services\Products;

use App\Models\Product;
use App\Models\Promotion;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Menghitung harga efektif produk berdasarkan promosi aktif yang terikat.
 * Tidak mengubah kolom atau tabel database yang sudah ada.
 */
class ProductPromotionService
{
    /**
     * Muat relasi promosi aktif ke model Product jika belum di-load.
     */
    public function loadActivePromotions(Product $product): void
    {
        if ($product->relationLoaded('promotions')) {
            return;
        }

        $product->load([
            'promotions' => fn (BelongsToMany $q) => $q
                ->where('promotions.is_active', true)
                ->where('promotions.start_at', '<=', now())
                ->where('promotions.end_at', '>=', now())
                ->withPivot(['min_qty', 'discount_value', 'discount_percent', 'bundle_price']),
        ]);
    }

    /**
     * Tentukan harga efektif produk untuk qty tertentu.
     *
     * Tidak mengubah model — hanya mengembalikan data harga.
     *
     * @return array{
     *   unit_price: float,
     *   original_price: float,
     *   discount_amount: float,
     *   has_promo: bool,
     *   promo_label: string|null,
     *   promo_meta: array<string,mixed>|null
     * }
     */
    public function resolvePrice(Product $product, int $qty = 1): array
    {
        $basePrice = (float) $product->base_price;
        $promo = $this->findBestPromotion($product, $qty);

        if ($promo === null) {
            return [
                'unit_price' => $basePrice,
                'original_price' => $basePrice,
                'discount_amount' => 0.0,
                'has_promo' => false,
                'promo_label' => null,
                'promo_meta' => null,
            ];
        }

        $pivot = $promo->pivot;
        $effectivePrice = $this->calcEffectivePrice($basePrice, $pivot);
        $discountAmount = round($basePrice - $effectivePrice, 2);
        $promoLabel = $this->buildLabel($basePrice, $pivot, $promo);

        return [
            'unit_price' => $effectivePrice,
            'original_price' => $basePrice,
            'discount_amount' => $discountAmount,
            'has_promo' => true,
            'promo_label' => $promoLabel,
            'promo_meta' => [
                'promo_id' => (int) $promo->id,
                'promo_code' => $promo->code,
                'promo_name' => $promo->name,
                'promo_label' => $promoLabel,
                'original_price' => $basePrice,
                'effective_price' => $effectivePrice,
                'discount_amount' => $discountAmount,
                'min_qty' => (int) ($pivot->min_qty ?? 1),
            ],
        ];
    }

    /**
     * Cari promosi terbaik (diskon terbesar) yang aktif untuk produk dan qty tertentu.
     */
    private function findBestPromotion(Product $product, int $qty): ?Promotion
    {
        $this->loadActivePromotions($product);

        $now = now();

        /** @var Promotion|null $best */
        $best = $product->promotions
            ->filter(function (Promotion $promo) use ($now, $qty): bool {
                return $promo->is_active
                    && $promo->start_at !== null
                    && $promo->end_at !== null
                    && $promo->start_at <= $now
                    && $promo->end_at >= $now
                    && (int) ($promo->pivot->min_qty ?? 1) <= $qty;
            })
            ->sortByDesc(fn (Promotion $promo): float => $this->estimateDiscount(
                (float) $product->base_price,
                $promo->pivot
            ))
            ->first();

        return $best;
    }

    private function calcEffectivePrice(float $basePrice, mixed $pivot): float
    {
        if (filled($pivot->bundle_price) && (float) $pivot->bundle_price > 0) {
            return (float) $pivot->bundle_price;
        }

        if (filled($pivot->discount_percent) && (float) $pivot->discount_percent > 0) {
            return round($basePrice * (1 - (float) $pivot->discount_percent / 100), 2);
        }

        if (filled($pivot->discount_value) && (float) $pivot->discount_value > 0) {
            return max(0.0, $basePrice - (float) $pivot->discount_value);
        }

        return $basePrice;
    }

    private function estimateDiscount(float $basePrice, mixed $pivot): float
    {
        if (filled($pivot->bundle_price) && (float) $pivot->bundle_price > 0) {
            return max(0.0, $basePrice - (float) $pivot->bundle_price);
        }

        if (filled($pivot->discount_percent) && (float) $pivot->discount_percent > 0) {
            return $basePrice * (float) $pivot->discount_percent / 100;
        }

        if (filled($pivot->discount_value) && (float) $pivot->discount_value > 0) {
            return min($basePrice, (float) $pivot->discount_value);
        }

        return 0.0;
    }

    private function buildLabel(float $basePrice, mixed $pivot, Promotion $promo): string
    {
        if (filled($pivot->bundle_price) && (float) $pivot->bundle_price > 0) {
            return 'Harga Bundle';
        }

        if (filled($pivot->discount_percent) && (float) $pivot->discount_percent > 0) {
            $pct = rtrim(rtrim(number_format((float) $pivot->discount_percent, 2, '.', ''), '0'), '.');

            return "Diskon {$pct}%";
        }

        if (filled($pivot->discount_value) && (float) $pivot->discount_value > 0) {
            return 'Hemat Rp '.number_format((int) $pivot->discount_value, 0, ',', '.');
        }

        return $promo->name;
    }
}
