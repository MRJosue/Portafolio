<?php

namespace App\Models;

use Database\Factories\TalentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Talent extends Model
{
    /** @use HasFactory<TalentFactory> */
    use HasFactory;

    protected $table = 'talents';

    protected $fillable = [
        'user_id',
        'full_name',
        'email',
        'phone',
        'location',
        'headline',
        'summary',
        'skills',
        'languages',
        'links',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'skills' => 'array',
            'languages' => 'array',
            'links' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(CvDocument::class);
    }

    public function experiences(): HasMany
    {
        return $this->hasMany(CvExperience::class)->orderBy('sort_order');
    }

    public function educations(): HasMany
    {
        return $this->hasMany(CvEducation::class)->orderBy('sort_order');
    }
}
