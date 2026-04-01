<?php

namespace App\Services;

use App\Exceptions\WorkDescriptionGenerationException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use JsonException;
use Throwable;

class WorkDescriptionGenerationService
{
    private const PREVIEW_MAX_DIMENSION = 2048;

    private const PREVIEW_QUALITY = 82;

    private const OUTPUT_SCHEMA = [
        'type' => 'object',
        'additionalProperties' => false,
        'required' => ['ua', 'en', 'de'],
        'properties' => [
            'ua' => ['type' => 'string'],
            'en' => ['type' => 'string'],
            'de' => ['type' => 'string'],
        ],
    ];

    private const PROMPT = <<<'TEXT'
You are generating artwork descriptions for an admin panel from a single image.

Analyze only what is clearly visible in the image.
Do not invent materials, location, dimensions, brand names, technical details, symbolism, authorship, historical context, or function unless they are obvious from the image.
Do not copy fixed facts from this instruction.
Generate a new description from the image itself.

Generate descriptions in three languages with the same meaning:
- Ukrainian
- English
- German

For each language, follow this structure strictly:

1. Line 1:
A short proposed title for the artwork only.

2. Paragraph 1:
A polished artistic description of the overall composition and character of the image.

3. Paragraph 2:
A polished artistic description of visible color accents, rhythm, shapes, interaction of elements, and general visual impression.

4. Final short line:
Style: ...

5. Final short line:
Mood: ...

Important rules:
- Keep the structure exactly as specified.
- Do not skip the title.
- Do not merge the two paragraphs.
- "Style" and "Mood" lines are mandatory in every language.
- Keep the tone elegant, professional, and suitable for an art catalog.
- If visual information is limited, stay neutral and concise.
- Use only visual facts supported by the image.

Use localized labels:
- Ukrainian: "Стиль:", "Настрій:"
- English: "Style:", "Mood:"
- German: "Stil:", "Stimmung:"

Return only valid JSON with exactly these keys:
{
  "ua": "...",
  "en": "...",
  "de": "..."
}

Do not use markdown.
Do not use code fences.
Do not add any extra text outside JSON.
TEXT;

    public function generateFromUpload(UploadedFile $file): array
    {
        return $this->generateFromLocalImage(
            $file->getRealPath(),
            $file->getMimeType() ?: 'image/jpeg'
        );
    }

    public function generateFromStoredImage(string $path, string $disk = 'public'): array
    {
        $storage = Storage::disk($disk);

        if (! $storage->exists($path)) {
            throw new WorkDescriptionGenerationException('Головне зображення не знайдено.');
        }

        return $this->generateFromLocalImage(
            $storage->path($path),
            $storage->mimeType($path) ?: 'image/jpeg'
        );
    }

    private function generateFromLocalImage(string $path, string $mimeType): array
    {
        $apiKey = (string) config('services.openai.key');
        $model = (string) config('services.openai.model');
        $baseUrl = rtrim((string) config('services.openai.base_url', 'https://api.openai.com/v1'), '/');

        if ($apiKey === '') {
            throw new WorkDescriptionGenerationException('OPENAI_API_KEY не налаштований.');
        }

        if ($model === '') {
            throw new WorkDescriptionGenerationException('OpenAI model не налаштована.');
        }

        try {
            $response = Http::withToken($apiKey)
                ->acceptJson()
                ->timeout(60)
                ->post($baseUrl . '/responses', [
                    'model' => $model,
                    'input' => [[
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'input_text',
                                'text' => self::PROMPT,
                            ],
                            [
                                'type' => 'input_image',
                                'image_url' => $this->buildImageDataUrl($path, $mimeType),
                            ],
                        ],
                    ]],
                    'text' => [
                        'format' => [
                            'type' => 'json_schema',
                            'name' => 'work_descriptions',
                            'strict' => true,
                            'schema' => self::OUTPUT_SCHEMA,
                        ],
                    ],
                ])
                ->throw();
        } catch (ConnectionException|RequestException $exception) {
            throw new WorkDescriptionGenerationException('Не вдалося звернутися до OpenAI. Спробуйте ще раз пізніше.', previous: $exception);
        }

        try {
            return $this->parseDescriptions($response->json());
        } catch (JsonException|WorkDescriptionGenerationException $exception) {
            throw new WorkDescriptionGenerationException('OpenAI повернув невалідну відповідь. Опис не оновлено.', previous: $exception);
        }
    }

    private function buildImageDataUrl(string $path, string $mimeType): string
    {
        try {
            $encoded = Image::read($path)
                ->scaleDown(width: self::PREVIEW_MAX_DIMENSION, height: self::PREVIEW_MAX_DIMENSION)
                ->toJpeg(self::PREVIEW_QUALITY, progressive: true);

            return 'data:image/jpeg;base64,' . base64_encode((string) $encoded);
        } catch (Throwable) {
            $bytes = file_get_contents($path);

            if ($bytes === false) {
                throw new WorkDescriptionGenerationException('Не вдалося прочитати зображення для генерації опису.');
            }

            return 'data:' . $mimeType . ';base64,' . base64_encode($bytes);
        }
    }

    private function parseDescriptions(array $payload): array
    {
        $outputText = $this->extractOutputText($payload);

        if (! is_string($outputText) || trim($outputText) === '') {
            throw new WorkDescriptionGenerationException('Empty OpenAI output.');
        }

        $decoded = json_decode($this->sanitizeOutputText($outputText), true, 512, JSON_THROW_ON_ERROR);

        foreach (['ua', 'en', 'de'] as $locale) {
            if (! isset($decoded[$locale]) || ! is_string($decoded[$locale]) || trim($decoded[$locale]) === '') {
                throw new WorkDescriptionGenerationException('Missing locale in OpenAI output.');
            }
        }

        return [
            'ua' => trim($decoded['ua']),
            'en' => trim($decoded['en']),
            'de' => trim($decoded['de']),
        ];
    }

    private function extractOutputText(array $payload): ?string
    {
        if (is_string($payload['output_text'] ?? null) && trim($payload['output_text']) !== '') {
            return $payload['output_text'];
        }

        foreach (($payload['output'] ?? []) as $output) {
            foreach (($output['content'] ?? []) as $content) {
                $text = $content['text'] ?? null;

                if (is_string($text) && trim($text) !== '') {
                    return $text;
                }
            }
        }

        return null;
    }

    private function sanitizeOutputText(string $outputText): string
    {
        $outputText = trim($outputText);

        if (preg_match('/^```(?:json)?\s*(.*?)\s*```$/is', $outputText, $matches) === 1) {
            $outputText = $matches[1];
        }

        return trim($outputText);
    }
}
