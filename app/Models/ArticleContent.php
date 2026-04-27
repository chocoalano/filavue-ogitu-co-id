<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model ArticleContent (Konten Artikel).
 *
 * Body/konten dari artikel, mendukung rich content (block editor)
 * dan tags dalam format JSON.
 *
 * @property int $id
 * @property int $article_id Relasi ke artikel
 * @property string $content Konten HTML/JSON
 * @property array|null $tags Tags artikel (JSON)
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class ArticleContent extends BaseModel
{
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'article_id',
        'content',
        'tags',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'content' => 'array',
            'tags' => 'array',
        ];
    }

    /**
     * Artikel induk.
     */
    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }
}
