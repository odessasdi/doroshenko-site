<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class Work extends Model
{
    protected $fillable = [
        'technique_id',
        'genre_id',
        'surface_id',
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

    public function genre(): BelongsTo
    {
        return $this->belongsTo(Genre::class);
    }

    public function surface(): BelongsTo
    {
        return $this->belongsTo(Surface::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(WorkImage::class)->orderBy('sort_order');
    }

    public function getMainImageUrlAttribute(): string
    {
        return $this->mainImageUrl();
    }

    public function mainImageUrl(): string
    {
        return $this->pathToUrl($this->main_image_path);
    }

    public function imageUrls(): array
    {
        $urls = [];
        $hasReal = false;

        if ($this->main_image_path && Storage::disk('public')->exists($this->main_image_path)) {
            $urls[] = Storage::url($this->main_image_path);
            $hasReal = true;
        }

        $images = $this->relationLoaded('images') ? $this->images : $this->images()->get();
        foreach ($images->sortBy('sort_order') as $image) {
            if ($image->image_path && Storage::disk('public')->exists($image->image_path)) {
                $urls[] = Storage::url($image->image_path);
                $hasReal = true;
            }
        }

        if (! $hasReal) {
            $urls[] = $this->placeholderUrl();
        }

        return $urls;
    }

    public function hasRealImages(): bool
    {
        if ($this->main_image_path && Storage::disk('public')->exists($this->main_image_path)) {
            return true;
        }

        $images = $this->relationLoaded('images') ? $this->images : $this->images()->get();
        foreach ($images as $image) {
            if ($image->image_path && Storage::disk('public')->exists($image->image_path)) {
                return true;
            }
        }

        return false;
    }

    public function getSizeLabelAttribute(): ?string
    {
        if (! $this->size_w_mm || ! $this->size_h_mm) {
            return null;
        }

        $w = $this->formatCentimeters($this->size_w_mm / 10);
        $h = $this->formatCentimeters($this->size_h_mm / 10);

        return $w.' × '.$h.' cm';
    }

    public function getPriceLabelAttribute(): ?string
    {
        if (! $this->price_cents || ! $this->currency) {
            return null;
        }

        $amount = number_format($this->price_cents / 100, 2, '.', '');

        return $amount.' '.$this->currency;
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

    public function title(string $locale): string
    {
        $description = trim($this->cleanUtf8($this->description($locale)));
        $parts = explode("\n", str_replace(["\r\n", "\r"], "\n", $description), 2);
        $title = trim($parts[0] ?? '');

        if ($title !== '') {
            return $title;
        }

        return $this->cleanUtf8($this->genre?->name($locale) ?? $this->technique?->name($locale) ?? __('ui.artwork'));
    }

    private function formatCentimeters(float $value): string
    {
        $formatted = number_format($value, 1, '.', '');

        return rtrim(rtrim($formatted, '0'), '.');
    }

    private function cleanUtf8(mixed $value): string
    {
        return mb_scrub((string) $value, 'UTF-8');
    }

    private function pathToUrl(?string $path): string
    {
        if ($path && Storage::disk('public')->exists($path)) {
            return Storage::url($path);
        }

        if ($path && app()->environment('local')) {
            Log::warning('Work image missing on disk', ['path' => $path, 'work_id' => $this->id]);
        }

        return $this->placeholderUrl();
    }

    private function placeholderUrl(): string
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="900" viewBox="0 0 1200 900"><rect width="1200" height="900" fill="#f4f4f5"/><rect x="40" y="40" width="1120" height="820" fill="none" stroke="#d4d4d8" stroke-width="3"/><text x="600" y="455" text-anchor="middle" font-family="Arial, sans-serif" font-size="36" fill="#71717a">Image not available</text></svg>';

        return 'data:image/svg+xml;utf8,'.rawurlencode($svg);
    }
}
