<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model CustomerBonus (Ringkasan Bonus Harian).
 *
 * Akumulasi bonus harian per member, termasuk perhitungan pajak.
 * Data ini merupakan summary dari berbagai jenis bonus.
 *
 * @property int $id
 * @property int|null $member_id Member penerima bonus
 * @property float $amount Nominal bonus kotor
 * @property float $index_value Nilai index/point
 * @property float $tax_netto Nominal bersih setelah pajak
 * @property int $tax_percent Persentase pajak
 * @property float $tax_value Nominal pajak
 * @property int $status 0=pending, 1=released
 * @property string|null $description Catatan
 * @property \Illuminate\Support\Carbon|null $date Tanggal bonus
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class CustomerBonus extends BaseModel
{
    use HasFactory;

    protected $table = 'customer_bonuses';

    /** @var list<string> */
    protected $fillable = [
        'member_id',
        'amount',
        'index_value',
        'tax_netto',
        'tax_percent',
        'tax_value',
        'status',
        'description',
        'date',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'index_value' => 'decimal:2',
            'tax_netto' => 'decimal:2',
            'tax_value' => 'decimal:2',
            'date' => 'date',
        ];
    }

    /**
     * Member penerima bonus.
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'member_id');
    }
}
