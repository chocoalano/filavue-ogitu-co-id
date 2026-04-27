<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model CustomerContentProgress (Progres Konten Customer).
 *
 * Menyimpan progres menonton/membaca konten Zenner Academy per customer.
 * Satu record mewakili satu pasangan (customer, course/category).
 *
 * @property int $id
 * @property int $customer_id Customer yang sedang belajar
 * @property int $content_category_id Kategori kursus (course)
 * @property int|null $content_id Konten terakhir ditonton (module)
 * @property float $progress Progres keseluruhan (0.0 – 1.0)
 * @property int $position_sec Posisi video terakhir dalam detik
 * @property \Illuminate\Support\Carbon|null $last_watched_at Waktu terakhir ditonton
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class CustomerContentProgress extends BaseModel
{
    /** @var list<string> */
    protected $fillable = [
        'customer_id',
        'content_category_id',
        'content_id',
        'progress',
        'position_sec',
        'last_watched_at',
    ];

    protected function casts(): array
    {
        return [
            'progress' => 'float',
            'position_sec' => 'integer',
            'last_watched_at' => 'datetime',
        ];
    }

    /**
     * Customer yang memiliki progres ini.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Kategori kursus (course).
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(ContentCategory::class, 'content_category_id');
    }

    /**
     * Konten/modul terakhir ditonton.
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Content::class, 'content_id');
    }
}
