<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model CustomerNetworkMatrix (Matrix Sponsor).
 *
 * Menyimpan relasi sponsor-downline dalam bentuk matrix.
 * Berbeda dengan binary tree, ini berdasarkan sponsor (recruiter).
 *
 * @property int $id
 * @property int|null $member_id Member di matrix
 * @property int|null $sponsor_id Sponsor yang merekrut
 * @property int $level Level kedalaman dari sponsor
 * @property string|null $description Catatan
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class CustomerNetworkMatrix extends BaseModel
{
    use HasFactory;

    protected $table = 'customer_network_matrixes';

    /** @var list<string> */
    protected $fillable = [
        'member_id',
        'sponsor_id',
        'level',
        'description',
    ];

    /**
     * Member dalam matrix.
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'member_id');
    }

    /**
     * Sponsor yang merekrut member.
     */
    public function sponsor(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'sponsor_id');
    }
}
