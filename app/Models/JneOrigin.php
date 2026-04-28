<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JneOrigin extends Model
{
    use HasFactory;

    protected $table = 'jne_origins';

    protected $fillable = [
        'origin_code',
        'origin_name',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Filter by origin code.
     */
    public function scopeByCode(Builder $query, string $originCode): Builder
    {
        return $query->where('origin_code', $originCode);
    }

    /**
     * Search origin by code or name.
     */
    public function scopeSearch(Builder $query, ?string $keyword): Builder
    {
        if (blank($keyword)) {
            return $query;
        }

        return $query->where(function (Builder $query) use ($keyword) {
            $query->where('origin_code', 'like', "%{$keyword}%")
                ->orWhere('origin_name', 'like', "%{$keyword}%");
        });
    }
}
