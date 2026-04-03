<?php

namespace App\Models;

use App\Enums\InteriorVisualizationPreset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class WorkImage extends Model
{
    protected $fillable = [
        'work_id',
        'image_path',
        'preset',
        'sort_order',
    ];

    public function work(): BelongsTo
    {
        return $this->belongsTo(Work::class);
    }

    public function getUrlAttribute(): string
    {
        return Storage::url($this->image_path);
    }

    public function getPresetLabelAttribute(): ?string
    {
        if (! $this->preset) {
            return null;
        }

        return InteriorVisualizationPreset::tryFrom($this->preset)?->label();
    }
}
