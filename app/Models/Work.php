<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Work extends Model
{
    protected $fillable = [
        'technique_id',
        'year',
        'size_w_mm',
        'size_h_mm',
        'main_image_path',
        'price_cents',
        'currency',
        'description_en',
        'description_de',
        'description_ua',
        'is_published',
        'sort_order',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'year' => 'integer',
        'size_w_mm' => 'integer',
        'size_h_mm' => 'integer',
        'price_cents' => 'integer',
        'sort_order' => 'integer',
    ];

    public function technique(): BelongsTo
    {
        return $this->belongsTo(Technique::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(WorkImage::class)->orderBy('sort_order');
    }

    public function getMainImageUrlAttribute(): string
    {
        return Storage::url($this->main_image_path);
    }

    public function getSizeLabelAttribute(): ?string
    {
        if (!$this->size_w_mm || !$this->size_h_mm) {
            return null;
        }

        $w = $this->formatCentimeters($this->size_w_mm / 10);
        $h = $this->formatCentimeters($this->size_h_mm / 10);

        return $w . ' × ' . $h . ' cm';
    }

    public function getPriceLabelAttribute(): ?string
    {
        if (!$this->price_cents || !$this->currency) {
            return null;
        }

        $amount = number_format($this->price_cents / 100, 2, '.', '');

        return $amount . ' ' . $this->currency;
    }

    public function description(string $locale): ?string
    {
        $field = match ($locale) {
            'de' => 'description_de',
            'ua' => 'description_ua',
            'en' => 'description_en',
            default => 'description_en',
        };

        return $this->{$field} ?: $this->description_en;
    }

    private function formatCentimeters(float $value): string
    {
        $formatted = number_format($value, 1, '.', '');

        return rtrim(rtrim($formatted, '0'), '.');
    }
}
