<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CvEducation extends Model
{
    protected $table = 'cv_educations';

    protected $fillable = [
        'talent_id',
        'degree',
        'institution',
        'period',
        'description',
        'sort_order',
    ];

    public function talent(): BelongsTo
    {
        return $this->belongsTo(Talent::class);
    }
}
