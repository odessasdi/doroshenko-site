<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DemoImages extends Command
{
    protected $signature = 'demo:images {--count=36 : Number of works to generate}';

    protected $description = 'Generate demo placeholder images for works';

    public function handle(): int
    {
        if (!extension_loaded('gd')) {
            $this->error('GD extension is not available.');
            return self::FAILURE;
        }

        $count = (int) $this->option('count');
        if ($count < 1) {
            $this->error('Count must be a positive integer.');
            return self::FAILURE;
        }

        $baseDir = storage_path('app/public/works/demo');
        if (!is_dir($baseDir) && !mkdir($baseDir, 0755, true) && !is_dir($baseDir)) {
            $this->error('Unable to create demo image directory.');
            return self::FAILURE;
        }

        for ($i = 1; $i <= $count; $i++) {
            $index = str_pad((string) $i, 2, '0', STR_PAD_LEFT);
            $mainPath = $baseDir . "/work-{$index}-main.jpg";
            $this->createImage($mainPath, "WORK {$index}\nMAIN");

            if ($i % 3 === 0) {
                $extraCount = ($i % 9 === 0) ? 3 : (($i % 6 === 0) ? 2 : 1);
                for ($j = 1; $j <= $extraCount; $j++) {
                    $extraPath = $baseDir . "/work-{$index}-{$j}.jpg";
                    $this->createImage($extraPath, "WORK {$index}\nEXTRA {$j}");
                }
            }
        }

        $this->info("Generated demo images in {$baseDir}");

        return self::SUCCESS;
    }

    private function createImage(string $path, string $label): void
    {
        $width = 1600;
        $height = 1200;

        $image = imagecreatetruecolor($width, $height);

        $bg = $this->randomPastelColor($image);
        imagefill($image, 0, 0, $bg);

        $border = imagecolorallocate($image, 255, 255, 255);
        imagerectangle($image, 10, 10, $width - 10, $height - 10, $border);

        $textColor = imagecolorallocate($image, 20, 20, 20);
        $lines = explode("\n", $label);
        $font = 5;
        $lineHeight = imagefontheight($font) + 8;
        $totalHeight = count($lines) * $lineHeight;
        $y = (int) (($height - $totalHeight) / 2);

        foreach ($lines as $line) {
            $lineWidth = imagefontwidth($font) * strlen($line);
            $x = (int) (($width - $lineWidth) / 2);
            imagestring($image, $font, $x, $y, $line, $textColor);
            $y += $lineHeight;
        }

        imagejpeg($image, $path, 90);
        imagedestroy($image);
    }

    private function randomPastelColor($image): int
    {
        $r = 180 + random_int(0, 60);
        $g = 180 + random_int(0, 60);
        $b = 180 + random_int(0, 60);

        return imagecolorallocate($image, $r, $g, $b);
    }
}
