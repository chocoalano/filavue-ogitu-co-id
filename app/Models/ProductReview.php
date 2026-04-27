<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model ProductReview (Review Produk).
 *
 * Ulasan produk dari customer yang sudah membeli.
 *
 * @property int $id
 * @property int $customer_id Customer yang mereview
 * @property int $product_id Produk yang direview
 * @property int|null $order_item_id Order item terkait
 * @property int $rating Rating 1-5
 * @property string|null $title Judul review
 * @property string|null $comment Isi review
 * @property bool $is_approved Sudah disetujui admin
 * @property bool $is_verified_purchase Pembelian terverifikasi
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class ProductReview extends BaseModel
{
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'customer_id',
        'product_id',
        'order_item_id',
        'rating',
        'title',
        'comment',
        'is_approved',
        'is_verified_purchase',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_approved' => 'boolean',
            'is_verified_purchase' => 'boolean',
        ];
    }

    /**
     * Customer yang menulis review.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Produk yang direview.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Order item yang terkait (bukti pembelian).
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }
}
