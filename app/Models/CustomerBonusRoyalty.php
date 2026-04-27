<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model CustomerBonusRoyalty.
 *
 * Bonus royalty customer sesuai tabel `customer_bonus_royalty`.
 *
 * @property int $id
 * @property int|null $member_id
 * @property int|null $from_member_id
 * @property float $amount
 * @property float $index_value
 * @property int $status
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class CustomerBonusRoyalty extends BaseModel
{
    use HasFactory;

    protected $table = 'customer_bonus_royalty';

    /** @var list<string> */
    protected $fillable = [
        'member_id',
        'from_member_id',
        'amount',
        'index_value',
        'status',
        'description',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'index_value' => 'decimal:2',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'member_id');
    }

    public function fromMember(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'from_member_id');
    }
}
