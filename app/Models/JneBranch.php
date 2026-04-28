<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JneBranch extends Model
{
    use HasFactory;

    protected $table = 'jne_branches';

    protected $fillable = [
        'branch_code',
        'branch_name',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Filter by branch code.
     */
    public function scopeByCode(Builder $query, string $branchCode): Builder
    {
        return $query->where('branch_code', $branchCode);
    }

    /**
     * Search branch by code or name.
     */
    public function scopeSearch(Builder $query, ?string $keyword): Builder
    {
        if (blank($keyword)) {
            return $query;
        }

        return $query->where(function (Builder $query) use ($keyword) {
            $query->where('branch_code', 'like', "%{$keyword}%")
                ->orWhere('branch_name', 'like', "%{$keyword}%");
        });
    }
}
