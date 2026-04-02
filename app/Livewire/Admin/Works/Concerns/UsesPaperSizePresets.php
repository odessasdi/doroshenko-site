<?php

namespace App\Livewire\Admin\Works\Concerns;

trait UsesPaperSizePresets
{
    protected const PAPER_SIZE_PRESETS = [
        'a3_landscape' => [
            'label' => 'A3 горизонтальний',
            'width' => 420,
            'height' => 297,
        ],
        'a3_portrait' => [
            'label' => 'A3 вертикальний',
            'width' => 297,
            'height' => 420,
        ],
        'a4_landscape' => [
            'label' => 'A4 горизонтальний',
            'width' => 297,
            'height' => 210,
        ],
        'a4_portrait' => [
            'label' => 'A4 вертикальний',
            'width' => 210,
            'height' => 297,
        ],
    ];

    public function applyPaperPreset(string $preset): void
    {
        $dimensions = static::PAPER_SIZE_PRESETS[$preset] ?? null;

        if (! is_array($dimensions)) {
            return;
        }

        $this->size_w_mm = $dimensions['width'];
        $this->size_h_mm = $dimensions['height'];
    }

    protected function paperPresets(): array
    {
        return static::PAPER_SIZE_PRESETS;
    }
}
