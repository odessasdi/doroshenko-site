<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Works\Create;
use App\Livewire\Admin\Works\Edit;
use App\Models\Technique;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class WorkDescriptionGenerationTest extends TestCase
{
    use RefreshDatabase;

    private Technique $technique;

    protected function setUp(): void
    {
        parent::setUp();

        $this->technique = Technique::create([
            'name_en' => 'Oil',
            'name_de' => 'Ol',
            'name_ua' => 'Олія',
        ]);

        config([
            'services.openai.key' => 'test-key',
            'services.openai.model' => 'gpt-test',
            'services.openai.base_url' => 'https://api.openai.test/v1',
        ]);
    }

    public function test_create_generates_descriptions_from_uploaded_main_image(): void
    {
        Http::fake([
            'https://api.openai.test/v1/responses' => Http::response([
                'output_text' => json_encode([
                    'ua' => "Назва\n\nАбзац один\n\nАбзац два\n\nСтиль: мінімалізм\nНастрій: баланс",
                    'en' => "Title\n\nParagraph one\n\nParagraph two\n\nStyle: minimalism\nMood: balance",
                    'de' => "Titel\n\nAbsatz eins\n\nAbsatz zwei\n\nStil: Minimalismus\nStimmung: Balance",
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]),
        ]);

        Livewire::test(Create::class)
            ->set('main_image', UploadedFile::fake()->image('main.jpg', 1200, 900))
            ->call('generateDescriptions')
            ->assertSet('description_ua', "Назва\n\nАбзац один\n\nАбзац два\n\nСтиль: мінімалізм\nНастрій: баланс")
            ->assertSet('description_en', "Title\n\nParagraph one\n\nParagraph two\n\nStyle: minimalism\nMood: balance")
            ->assertSet('description_de', "Titel\n\nAbsatz eins\n\nAbsatz zwei\n\nStil: Minimalismus\nStimmung: Balance");

        Http::assertSent(function (Request $request) {
            $payload = $request->data();
            $prompt = $payload['input'][0]['content'][0]['text'] ?? '';

            return $request->url() === 'https://api.openai.test/v1/responses'
                && str_contains($prompt, 'Style: ...')
                && str_contains($prompt, 'Mood: ...')
                && str_contains($prompt, 'Do not combine multiple languages in one field.')
                && str_contains($prompt, 'Each field must contain the complete structure for its language, not just a title.')
                && str_contains($prompt, 'Return only valid JSON');
        });
    }

    public function test_edit_generates_descriptions_from_existing_main_image(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('works/main/existing.jpg', $this->imageBytes(1200, 900));

        $work = Work::create([
            'technique_id' => $this->technique->id,
            'main_image_path' => 'works/main/existing.jpg',
            'is_published' => true,
            'sort_order' => 0,
        ]);

        Http::fake([
            'https://api.openai.test/v1/responses' => Http::response([
                'output' => [[
                    'content' => [[
                        'text' => json_encode([
                            'ua' => "Назва\n\nАбзац один\n\nАбзац два\n\nСтиль: мінімалізм\nНастрій: баланс",
                            'en' => "Title\n\nParagraph one\n\nParagraph two\n\nStyle: minimalism\nMood: balance",
                            'de' => "Titel\n\nAbsatz eins\n\nAbsatz zwei\n\nStil: Minimalismus\nStimmung: Balance",
                        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    ]],
                ]],
            ]),
        ]);

        Livewire::test(Edit::class, ['work' => $work])
            ->call('generateDescriptions')
            ->assertSet('description_ua', "Назва\n\nАбзац один\n\nАбзац два\n\nСтиль: мінімалізм\nНастрій: баланс")
            ->assertSet('description_en', "Title\n\nParagraph one\n\nParagraph two\n\nStyle: minimalism\nMood: balance")
            ->assertSet('description_de', "Titel\n\nAbsatz eins\n\nAbsatz zwei\n\nStil: Minimalismus\nStimmung: Balance");
    }

    public function test_invalid_openai_response_does_not_overwrite_existing_descriptions(): void
    {
        Http::fake([
            'https://api.openai.test/v1/responses' => Http::response([
                'output_text' => 'not valid json',
            ]),
        ]);

        Livewire::test(Create::class)
            ->set('main_image', UploadedFile::fake()->image('main.jpg', 1200, 900))
            ->set('description_ua', 'Existing UA')
            ->set('description_en', 'Existing EN')
            ->set('description_de', 'Existing DE')
            ->call('generateDescriptions')
            ->assertSet('description_ua', 'Existing UA')
            ->assertSet('description_en', 'Existing EN')
            ->assertSet('description_de', 'Existing DE');
    }

    public function test_mixed_language_openai_response_does_not_overwrite_existing_descriptions(): void
    {
        Http::fake([
            'https://api.openai.test/v1/responses' => Http::response([
                'output_text' => json_encode([
                    'ua' => 'Гірський пейзаж у блакиті',
                    'en' => 'Mountain Landscape in Blue',
                    'de' => "UA: Картина зображує гірський пейзаж.\n\nEN: This artwork portrays a mountainous landscape.\n\nDE: Das Bild zeigt eine Berglandschaft.\n\nStyle: impressionism\nMood: calm",
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]),
        ]);

        Livewire::test(Create::class)
            ->set('main_image', UploadedFile::fake()->image('main.jpg', 1200, 900))
            ->set('description_ua', 'Existing UA')
            ->set('description_en', 'Existing EN')
            ->set('description_de', 'Existing DE')
            ->call('generateDescriptions')
            ->assertSet('description_ua', 'Existing UA')
            ->assertSet('description_en', 'Existing EN')
            ->assertSet('description_de', 'Existing DE');
    }

    private function imageBytes(int $width, int $height): string
    {
        $path = tempnam(sys_get_temp_dir(), 'work-description-');
        $image = imagecreatetruecolor($width, $height);
        $color = imagecolorallocate($image, 180, 120, 90);

        imagefilledrectangle($image, 0, 0, $width, $height, $color);
        imagejpeg($image, $path, 90);
        imagedestroy($image);

        $contents = file_get_contents($path) ?: '';
        unlink($path);

        return $contents;
    }
}
