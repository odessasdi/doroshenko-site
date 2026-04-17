<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Surface extends Model
{
    protected $fillable = [
        'name_en',
        'name_de',
        'name_ua',
    ];

    public function works(): HasMany
    {
        return $this->hasMany(Work::class);
    }

    public function name(string $locale): string
    {
        $field = match ($locale) {
            'de' => 'name_de',
            'ua' => 'name_ua',
            'en' => 'name_en',
            default => 'name_en',
        };

        return $this->{$field} ?: $this->name_en;
    }
}
