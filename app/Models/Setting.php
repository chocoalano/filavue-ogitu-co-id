<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Model Setting (Pengaturan Situs).
 *
 * Key-value store untuk konfigurasi situs
 * (logo, deskripsi, social media links, dll).
 *
 * @property int $id
 * @property string $key Kunci pengaturan
 * @property string|null $value Nilai pengaturan
 * @property string $type Tipe data (text|image|html)
 * @property string $group Grup pengaturan (general|social)
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Setting extends BaseModel
{
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
    ];
}
