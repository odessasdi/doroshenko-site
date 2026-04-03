<?php

namespace App\Enums;

enum InteriorVisualizationPreset: string
{
    case LivingRoom = 'living-room';
    case Bedroom = 'bedroom';
    case Office = 'office';

    public function label(): string
    {
        return match ($this) {
            self::LivingRoom => 'Вітальня',
            self::Bedroom => 'Спальня',
            self::Office => 'Офіс',
        };
    }

    public function scenePrompt(): string
    {
        return match ($this) {
            self::LivingRoom => 'Create a calm, high-end living room with soft daylight, a clean wall, and tasteful decor.',
            self::Bedroom => 'Create a serene bedroom with soft natural light, restrained decor, and a believable wall area for the artwork.',
            self::Office => 'Create a modern office or study with refined furniture, balanced daylight, and a professional atmosphere.',
        };
    }

    public static function options(): array
    {
        return array_reduce(
            self::cases(),
            function (array $carry, self $preset): array {
                $carry[$preset->value] = $preset->label();

                return $carry;
            },
            []
        );
    }

    public static function values(): array
    {
        return array_map(
            static fn (self $preset): string => $preset->value,
            self::cases()
        );
    }
}
