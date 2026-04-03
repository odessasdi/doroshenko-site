<?php

namespace App\Services;

use App\Enums\InteriorVisualizationPreset;
use App\Exceptions\WorkVisualizationGenerationException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use JsonException;

class WorkInteriorVisualizationService
{
    public function generateFromUpload(
        UploadedFile $file,
        InteriorVisualizationPreset $preset,
        ?int $widthMm = null,
        ?int $heightMm = null,
    ): string {
        $bytes = file_get_contents($file->getRealPath());

        if ($bytes === false) {
            throw new WorkVisualizationGenerationException('Не вдалося прочитати головне зображення.');
        }

        return $this->generate(
            $bytes,
            $file->getClientOriginalName() ?: 'main-image.jpg',
            $file->getMimeType() ?: 'image/jpeg',
            $preset,
            $widthMm,
            $heightMm,
        );
    }

    public function generateFromStoredImage(
        string $path,
        InteriorVisualizationPreset $preset,
        ?int $widthMm = null,
        ?int $heightMm = null,
        string $disk = 'public',
    ): string {
        $storage = Storage::disk($disk);

        if (! $storage->exists($path)) {
            throw new WorkVisualizationGenerationException('Головне зображення не знайдено.');
        }

        return $this->generate(
            $storage->get($path),
            basename($path) ?: 'main-image.jpg',
            $storage->mimeType($path) ?: 'image/jpeg',
            $preset,
            $widthMm,
            $heightMm,
        );
    }

    private function generate(
        string $imageBytes,
        string $filename,
        string $mimeType,
        InteriorVisualizationPreset $preset,
        ?int $widthMm,
        ?int $heightMm,
    ): string {
        $apiKey = (string) config('services.openai.key');
        $model = (string) config('services.openai.image_model');
        $quality = (string) config('services.openai.image_quality', 'medium');
        $size = (string) config('services.openai.image_size', '1024x1024');
        $baseUrl = rtrim((string) config('services.openai.base_url', 'https://api.openai.com/v1'), '/');

        if ($apiKey === '') {
            throw new WorkVisualizationGenerationException('OPENAI_API_KEY не налаштований.');
        }

        if ($model === '') {
            throw new WorkVisualizationGenerationException('OpenAI image model не налаштована.');
        }

        try {
            $response = Http::withToken($apiKey)
                ->acceptJson()
                ->timeout(120)
                ->attach('image[]', $imageBytes, $filename, ['Content-Type' => $mimeType])
                ->post($baseUrl . '/images/edits', [
                    'model' => $model,
                    'prompt' => $this->promptFor($preset, $widthMm, $heightMm),
                    'input_fidelity' => 'high',
                    'quality' => $quality,
                    'size' => $size,
                ])
                ->throw();
        } catch (ConnectionException $exception) {
            Log::warning('OpenAI visualization connection failed', [
                'message' => $exception->getMessage(),
            ]);

            throw new WorkVisualizationGenerationException('Не вдалося підключитися до OpenAI. Спробуйте ще раз трохи пізніше.', previous: $exception);
        } catch (RequestException $exception) {
            $responseBody = $exception->response?->json();
            $apiMessage = $this->extractApiErrorMessage(is_array($responseBody) ? $responseBody : []);

            Log::warning('OpenAI visualization request failed', [
                'status' => $exception->response?->status(),
                'body' => $exception->response?->body(),
                'model' => $model,
            ]);

            throw new WorkVisualizationGenerationException(
                $apiMessage !== null
                    ? 'OpenAI: ' . $apiMessage
                    : 'Не вдалося згенерувати візуалізацію. Спробуйте ще раз пізніше.',
                previous: $exception
            );
        }

        try {
            return $this->parseImageBytes($response->json());
        } catch (JsonException|WorkVisualizationGenerationException $exception) {
            throw new WorkVisualizationGenerationException('OpenAI повернув невалідну відповідь. Візуалізацію не збережено.', previous: $exception);
        }
    }

    private function promptFor(InteriorVisualizationPreset $preset, ?int $widthMm, ?int $heightMm): string
    {
        $sizeHint = '';
        $placementHint = '';

        if ($widthMm && $heightMm) {
            $widthCm = rtrim(rtrim(number_format($widthMm / 10, 1, '.', ''), '0'), '.');
            $heightCm = rtrim(rtrim(number_format($heightMm / 10, 1, '.', ''), '0'), '.');
            $largestSideCm = max($widthMm, $heightMm) / 10;
            $orientation = $widthMm > $heightMm ? 'landscape' : ($widthMm < $heightMm ? 'portrait' : 'square');

            $sizeHint = implode("\n", [
                "The artwork must keep its original {$orientation} proportions and must appear on the wall at a realistic physical size of {$widthCm} x {$heightCm} cm.",
                'Scale the painting relative to the furniture and wall so it does not look oversized or miniature.',
                'Use nearby objects such as sofa backs, bed width, desk width, consoles, frames, and wall height as visual references to preserve believable real-world scale.',
                'Show enough surrounding furniture and wall in the frame so the true size of the artwork is visually obvious.',
            ]);

            $placementHint = $this->placementHintFor($preset, $largestSideCm);
        }

        return trim(implode("\n\n", array_filter([
            'Use the uploaded artwork as the exact painting that must appear in the final image.',
            'Do not change the artwork composition, colors, brushwork, edges, orientation, or proportions. Do not repaint or reinterpret the painting.',
            'Create a photorealistic interior scene where this painting is mounted on a wall and clearly visible as a real physical artwork in the room.',
            $preset->scenePrompt(),
            $sizeHint,
            $placementHint,
            'Use tasteful realistic staging, natural lighting, and believable scale. Keep the room elegant and uncluttered.',
            'Do not add text, signatures, labels, watermarks, people, pets, extra artworks, or mirrored duplicates of the painting.',
            'Return a single finished interior visualization.',
        ])));
    }

    private function placementHintFor(InteriorVisualizationPreset $preset, float $largestSideCm): string
    {
        if ($largestSideCm <= 50) {
            return match ($preset) {
                InteriorVisualizationPreset::LivingRoom => implode("\n", [
                    'This is a small artwork.',
                    'Do not place it as a dominant centerpiece above a sofa.',
                    'Prefer a narrow wall section above a small console, side table, reading corner, or compact sideboard.',
                    'If a sofa is visible, the artwork must read as clearly much smaller than the sofa, around one quarter of the sofa width or less.',
                ]),
                InteriorVisualizationPreset::Bedroom => implode("\n", [
                    'This is a small artwork.',
                    'Do not place it centered above the bed or spanning most of the headboard width.',
                    'Prefer a side wall, a position above a nightstand, dresser, or a small writing desk.',
                    'If a bed is visible, the artwork must read as clearly much smaller than the bed, around one quarter of the bed width or less.',
                ]),
                InteriorVisualizationPreset::Office => implode("\n", [
                    'This is a small artwork.',
                    'Do not make it the dominant centerpiece on a large wall.',
                    'Prefer a compact wall zone above a small desk return, shelf, cabinet, or reading nook.',
                    'If a desk is visible, the artwork should read as clearly narrower than the desk surface.',
                ]),
            };
        }

        if ($largestSideCm <= 100) {
            return match ($preset) {
                InteriorVisualizationPreset::LivingRoom => 'This is a medium-sized artwork. It may sit above a console or sideboard, but if a sofa is visible it should remain clearly narrower than the sofa.',
                InteriorVisualizationPreset::Bedroom => 'This is a medium-sized artwork. It may be placed on a bedroom wall, but if a bed is visible it should remain clearly narrower than the bed and not dominate the entire headboard span.',
                InteriorVisualizationPreset::Office => 'This is a medium-sized artwork. It may be placed above a desk or cabinet, but it should remain clearly narrower than the supporting furniture.',
            };
        }

        return match ($preset) {
            InteriorVisualizationPreset::LivingRoom => 'This is a large artwork. It may act as a focal point, but it still must stay believable relative to the wall and surrounding furniture.',
            InteriorVisualizationPreset::Bedroom => 'This is a large artwork. It may become a focal point in the bedroom, but it still must stay believable relative to the bed and wall size.',
            InteriorVisualizationPreset::Office => 'This is a large artwork. It may become a focal point in the office, but it still must stay believable relative to the desk, cabinets, and wall.',
        };
    }

    private function parseImageBytes(array $payload): string
    {
        $image = $payload['data'][0]['b64_json'] ?? null;

        if (! is_string($image) || trim($image) === '') {
            throw new WorkVisualizationGenerationException('Empty OpenAI image output.');
        }

        $decoded = base64_decode($image, true);

        if ($decoded === false || $decoded === '') {
            throw new WorkVisualizationGenerationException('Invalid OpenAI image output.');
        }

        return $decoded;
    }

    private function extractApiErrorMessage(array $payload): ?string
    {
        $message = $payload['error']['message'] ?? null;

        if (! is_string($message) || trim($message) === '') {
            return null;
        }

        return trim($message);
    }
}
