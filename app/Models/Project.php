<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Project extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'client',
        'service',
        'year',
        'role',
        'summary',
        'description',
        'challenge',
        'solution',
        'results',
        'technologies',
        'image_theme',
        'image_path',
        'is_featured',
        'status',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'technologies' => 'array',
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        if (Str::startsWith($this->image_path, 'uploads/projects/')) {
            return asset($this->image_path);
        }

        return Storage::disk('public')->url($this->image_path);
    }
}
