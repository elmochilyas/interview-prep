<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Concept extends Model
{
    use SoftDeletes;

    protected $fillable = ['domain_id', 'title', 'explanation', 'difficulty', 'status'];

    // Relationships
    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }

    public function generatedQuestions(): HasMany
    {
        return $this->hasMany(GeneratedQuestion::class);
    }

    // Accessors — MANDATORY — Laravel 9+ syntax
    protected function statusLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => match($this->status) {
                'to_review'   => 'À revoir',
                'in_progress' => 'En cours',
                'mastered'    => 'Maîtrisé',
            }
        );
    }

    protected function difficultyLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => match($this->difficulty) {
                'junior' => 'Junior',
                'mid'    => 'Mid',
                'senior' => 'Senior',
            }
        );
    }

    // Local scope for filtering
    public function scopeFilter($query, array $filters): void
    {
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['difficulty'])) {
            $query->where('difficulty', $filters['difficulty']);
        }
    }
}
