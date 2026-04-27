<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model Page (Halaman Statis).
 *
 * Halaman CMS yang bisa menggunakan template dan block editor.
 *
 * @property int $id
 * @property string $title Judul halaman
 * @property string $slug URL-friendly identifier
 * @property string|null $content Konten HTML
 * @property array|null $blocks Block editor content (JSON)
 * @property string|null $seo_title Judul SEO
 * @property string|null $seo_description Deskripsi SEO
 * @property bool $is_published Status publikasi
 * @property string $template Template yang digunakan
 * @property string|null $show_on Penempatan link halaman di storefront
 * @property int $order Urutan tampil
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class Page extends BaseModel
{
    use HasFactory, SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'title',
        'slug',
        'content',
        'blocks',
        'seo_title',
        'seo_description',
        'is_published',
        'template',
        'show_on',
        'order',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'blocks' => 'array',
            'is_published' => 'boolean',
        ];
    }
}
