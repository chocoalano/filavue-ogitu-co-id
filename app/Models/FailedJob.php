<?php

namespace App\Models;

class FailedJob extends BaseModel
{
    protected $table = 'failed_jobs';

    public $timestamps = false;

    /** @var list<string> */
    protected $guarded = [];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'failed_at' => 'datetime',
        ];
    }
}
