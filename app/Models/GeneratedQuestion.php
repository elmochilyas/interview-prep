<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GeneratedQuestion extends Model
{
    protected $fillable = ['concept_id', 'questions'];

    protected $casts = [
        'questions' => 'array',
    ];

    public function concept(): BelongsTo
    {
        return $this->belongsTo(Concept::class);
    }
}
