<?php

namespace Database\Seeders;

use App\Models\Technique;
use App\Models\Work;
use App\Models\WorkImage;
use Illuminate\Database\Seeder;

class WorkSeeder extends Seeder
{
    public function run(): void
    {
        $techniques = Technique::all();
        if ($techniques->isEmpty()) {
            return;
        }

        $descriptions = [
            [
                'en' => 'A calm composition with soft light and a contemplative mood.',
                'de' => 'Eine ruhige Komposition mit weichem Licht und nachdenklicher Stimmung.',
                'ua' => 'Спокійна композиція з м’яким світлом і задумливим настроєм.',
            ],
            [
                'en' => 'Dynamic brushwork captures the energy of the scene.',
                'de' => 'Dynamischer Pinselstrich fängt die Energie der Szene ein.',
                'ua' => 'Динамічний мазок передає енергію сцени.',
            ],
            [
                'en' => 'A warm palette and gentle contrasts create depth and atmosphere.',
                'de' => 'Eine warme Palette und sanfte Kontraste schaffen Tiefe und Atmosphäre.',
                'ua' => 'Тепла палітра та м’які контрасти створюють глибину й атмосферу.',
            ],
            [
                'en' => 'A minimal motif with an emphasis on texture and rhythm.',
                'de' => 'Ein minimalistisches Motiv mit Fokus auf Textur und Rhythmus.',
                'ua' => 'Мінімалістичний мотив з акцентом на текстурі та ритмі.',
            ],
            [
                'en' => 'Light and shadow build a quiet narrative in muted tones.',
                'de' => 'Licht und Schatten formen eine ruhige Erzählung in gedämpften Tönen.',
                'ua' => 'Світло і тінь формують спокійну історію в приглушених тонах.',
            ],
            [
                'en' => 'An expressive study of form and balance.',
                'de' => 'Eine expressive Studie von Form und Balance.',
                'ua' => 'Експресивне дослідження форми та балансу.',
            ],
        ];

        $sizes = [
            [30, 40], [40, 40], [50, 70], [60, 80], [70, 50], [80, 60],
            [90, 90], [40, 60], [60, 40], [100, 70], [70, 100], [55, 75],
        ];

        $count = 30;
        $yearStart = 2008;
        $yearEnd = 2025;
        $sort = 10;

        for ($i = 1; $i <= $count; $i++) {
            $technique = $techniques[($i - 1) % $techniques->count()];
            $year = $yearStart + (($i - 1) % ($yearEnd - $yearStart + 1));
            $size = $sizes[($i - 1) % count($sizes)];
            $desc = $descriptions[($i - 1) % count($descriptions)];

            $hasPrice = $i % 3 !== 0;
            $priceCents = $hasPrice ? (random_int(120, 680) * 100) : null;
            $currency = $hasPrice ? 'EUR' : null;
            $isPublished = $i % 10 !== 0 && $i % 5 !== 0; // ~70%

            $work = Work::create([
                'technique_id' => $technique->id,
                'year' => $year,
                'size_w_mm' => $size[0] * 10,
                'size_h_mm' => $size[1] * 10,
                'main_image_path' => sprintf('works/demo/work-%02d-main.jpg', $i),
                'price_cents' => $priceCents,
                'currency' => $currency,
                'description_en' => $desc['en'],
                'description_de' => $desc['de'],
                'description_ua' => $desc['ua'],
                'is_published' => $isPublished,
                'sort_order' => $sort,
            ]);

            if ($i % 3 === 0) {
                $extraCount = ($i % 9 === 0) ? 3 : (($i % 6 === 0) ? 2 : 1);
                for ($j = 1; $j <= $extraCount; $j++) {
                    WorkImage::create([
                        'work_id' => $work->id,
                        'image_path' => sprintf('works/demo/work-%02d-%d.jpg', $i, $j),
                        'sort_order' => $j,
                    ]);
                }
            }

            $sort += 10;
        }
    }
}
