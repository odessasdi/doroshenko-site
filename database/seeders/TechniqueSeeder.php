<?php

namespace Database\Seeders;

use App\Models\Technique;
use Illuminate\Database\Seeder;

class TechniqueSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['en' => 'Oil', 'de' => 'Öl', 'ua' => 'Олія'],
            ['en' => 'Acrylic', 'de' => 'Acryl', 'ua' => 'Акрил'],
            ['en' => 'Watercolor', 'de' => 'Aquarell', 'ua' => 'Акварель'],
            ['en' => 'Mixed media', 'de' => 'Mixed Media', 'ua' => 'Змішана техніка'],
            ['en' => 'Charcoal', 'de' => 'Kohle', 'ua' => 'Вугілля'],
            ['en' => 'Pencil', 'de' => 'Bleistift', 'ua' => 'Олівець'],
            ['en' => 'Pastel', 'de' => 'Pastell', 'ua' => 'Пастель'],
            ['en' => 'Ink', 'de' => 'Tusche', 'ua' => 'Туш'],
            ['en' => 'Gouache', 'de' => 'Gouache', 'ua' => 'Гуаш'],
            ['en' => 'Tempera', 'de' => 'Tempera', 'ua' => 'Темпера'],
        ];

        foreach ($items as $item) {
            Technique::create([
                'name_en' => $item['en'],
                'name_de' => $item['de'],
                'name_ua' => $item['ua'],
            ]);
        }
    }
}
