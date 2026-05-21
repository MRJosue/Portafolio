<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CvExperience extends Model
{
    protected $table = 'cv_experiences';

    protected $fillable = [
        'talent_id',
        'role',
        'company',
        'period',
        'description',
        'sort_order',
    ];

    public function talent(): BelongsTo
    {
        return $this->belongsTo(Talent::class);
    }
}
