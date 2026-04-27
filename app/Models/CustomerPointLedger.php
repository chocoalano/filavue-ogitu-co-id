<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerPointLedger extends Model
{
    public const ENTRY_CREDIT = 'credit';
    public const ENTRY_DEBIT = 'debit';
    public const ENTRY_REVERSAL = 'reversal';
    public const ENTRY_EXPIRE = 'expire';
    public const ENTRY_ADJUSTMENT = 'adjustment';
    public const ENTRY_HOLD = 'hold';
    public const ENTRY_RELEASE = 'release';

    protected $fillable = [
        'customer_id',
        'point_account_id',
        'entry_type',
        'source_type',
        'source_id',
        'delta_points',
        'balance_before',
        'balance_after',
        'reference_no',
        'idempotency_key',
        'note',
        'meta_json',
        'created_by',
    ];

    protected $casts = [
        'customer_id' => 'integer',
        'point_account_id' => 'integer',
        'source_id' => 'integer',
        'delta_points' => 'integer',
        'balance_before' => 'integer',
        'balance_after' => 'integer',
        'meta_json' => 'array',
        'created_by' => 'integer',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function pointAccount(): BelongsTo
    {
        return $this->belongsTo(CustomerPointAccount::class, 'point_account_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
