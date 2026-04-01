<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class WorkImageStorageService
{
    private const TARGET_MAX_BYTES = 2_000_000;

    private const MAIN_MAX_INPUT_KB = 51_200;

    private const ADDITIONAL_MAX_INPUT_KB = 51_200;

    private const MAX_DIMENSION = 2560;

    private const MIN_DIMENSION = 1400;

    private const JPEG_QUALITIES = [88, 84, 80, 76, 72, 68];

    private const WEBP_QUALITIES = [86, 82, 78, 74, 70, 66];

    public static function requiredRules(): array
    {
        return ['required', 'image', 'max:' . self::MAIN_MAX_INPUT_KB];
    }

    public static function optionalRules(): array
    {
        return ['nullable', 'image', 'max:' . self::MAIN_MAX_INPUT_KB];
    }

    public static function itemRules(): array
    {
        return ['image', 'mimes:jpg,jpeg,png,webp,heic,heif', 'max:' . self::ADDITIONAL_MAX_INPUT_KB];
    }

    public function store(UploadedFile $file, string $directory, string $fallbackName = 'image'): string
    {
        $format = $this->targetFormat($file);
        $encoded = $this->encodeCompressed($file, $format);
        $extension = $format === 'webp' ? 'webp' : 'jpg';
        $slug = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) ?: $fallbackName) ?: $fallbackName;
        $name = Str::uuid() . '-' . $slug;
        $path = trim($directory, '/') . '/' . $name . '.' . $extension;

        Storage::disk('public')->put($path, (string) $encoded);

        return $path;
    }

    private function encodeCompressed(UploadedFile $file, string $format): string
    {
        $image = Image::read($file->getRealPath());
        $qualities = $format === 'webp' ? self::WEBP_QUALITIES : self::JPEG_QUALITIES;
        $dimension = self::MAX_DIMENSION;
        $best = null;

        do {
            $working = clone $image;
            $working->scaleDown(width: $dimension, height: $dimension);

            foreach ($qualities as $quality) {
                $encoded = $format === 'webp'
                    ? $working->toWebp($quality)
                    : $working->toJpeg($quality, progressive: true);

                $best = (string) $encoded;

                if (strlen($best) <= self::TARGET_MAX_BYTES) {
                    return $best;
                }
            }

            $dimension = (int) floor($dimension * 0.85);
        } while ($dimension >= self::MIN_DIMENSION);

        return $best ?? (string) $image->toJpeg(72, progressive: true);
    }

    private function targetFormat(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());

        return in_array($extension, ['png', 'webp'], true) ? 'webp' : 'jpg';
    }
}
