<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Wishlist (Daftar Keinginan).
 *
 * Wishlist produk milik customer.
 *
 * @property int $id
 * @property int $customer_id Pemilik wishlist
 * @property string $name Nama wishlist
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Wishlist extends BaseModel
{
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'customer_id',
        'name',
    ];

    /**
     * Customer pemilik wishlist.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Item-item dalam wishlist.
     */
    public function items(): HasMany
    {
        return $this->hasMany(WishlistItem::class);
    }
}
