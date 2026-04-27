<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Cart (Keranjang Belanja).
 *
 * Keranjang belanja per customer. Menyimpan subtotal, diskon,
 * ongkir, pajak, dan grand total secara denormalisasi.
 *
 * @property int $id
 * @property int|null $customer_id Pemilik keranjang
 * @property string|null $session_id Session untuk guest cart
 * @property string $currency Mata uang (default IDR)
 * @property float $subtotal_amount Subtotal sebelum diskon
 * @property float $discount_amount Total diskon
 * @property float $shipping_amount Ongkos kirim
 * @property float $tax_amount Pajak
 * @property float $grand_total Total akhir
 * @property array|null $applied_promos Promo yang diaplikasikan (JSON)
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Cart extends BaseModel
{
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'customer_id',
        'session_id',
        'currency',
        'subtotal_amount',
        'discount_amount',
        'shipping_amount',
        'tax_amount',
        'grand_total',
        'applied_promos',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'subtotal_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'shipping_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'applied_promos' => 'array',
        ];
    }

    /**
     * Customer pemilik keranjang.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Item-item dalam keranjang.
     */
    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }
}
