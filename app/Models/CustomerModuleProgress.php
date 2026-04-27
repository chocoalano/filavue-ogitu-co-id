<?php

namespace App\Models;

use App\Observers\CustomerModuleProgressObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model CustomerModuleProgress (Progres Per Modul).
 *
 * Menyimpan status penyelesaian setiap modul (Content) oleh customer.
 * Digunakan untuk menghitung persentase progres kursus secara akurat.
 *
 * @property int $id
 * @property int $customer_id
 * @property int $content_id ID modul (Content)
 * @property bool $is_completed Apakah modul sudah selesai ditonton/dibaca
 * @property int $position_sec Posisi terakhir video dalam detik
 * @property \Illuminate\Support\Carbon|null $completed_at Waktu penyelesaian modul
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
#[ObservedBy(CustomerModuleProgressObserver::class)]
class CustomerModuleProgress extends BaseModel
{
    /** @var list<string> */
    protected $fillable = [
        'customer_id',
        'content_id',
        'is_completed',
        'position_sec',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'is_completed' => 'boolean',
            'position_sec' => 'integer',
            'completed_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }
}
