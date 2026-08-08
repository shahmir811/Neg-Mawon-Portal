<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable(['cleaning_job_id', 'path'])]
class CleaningJobPhoto extends Model
{
    /** @return BelongsTo<CleaningJob, $this> */
    public function cleaningJob(): BelongsTo
    {
        return $this->belongsTo(CleaningJob::class);
    }

    public function url(): string
    {
        return Storage::url($this->path);
    }
}
