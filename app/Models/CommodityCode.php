<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommodityCode extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terkait dengan model.
     * Secara default Laravel akan mencari tabel 'commodity_codes',
     * jadi ini opsional namun baik untuk kejelasan.
     */
    protected $table = 'commodity_codes';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'name',
        'dangerous_good',
        'is_quarantine',
    ];

    /**
     * Atribut yang harus dikonversi ke tipe data tertentu.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'dangerous_good' => 'boolean',
        'is_quarantine' => 'boolean',
    ];
}
