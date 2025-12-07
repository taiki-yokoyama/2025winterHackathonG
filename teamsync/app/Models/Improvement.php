<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Improvement extends Model
{
    protected $fillable = [
        'team_id',
        'week_number',
        'problem',
        'cause',
        'solution',
        'todo',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
