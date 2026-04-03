<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Works\Create;
use App\Livewire\Admin\Works\Edit;
use App\Models\Technique;
use App\Models\Work;
use App\Models\WorkImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class WorksAdditionalImagesLimitTest extends TestCase
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
            'services.openai.image_model' => 'gpt-image-test',
            'services.openai.base_url' => 'https://api.openai.test/v1',
        ]);
    }

    public function test_edit_adds_generated_visualization_to_library(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('works/main/existing.jpg', $this->imageBytes(1400, 1000));

        $generated = 'generated-image-bytes';
        Http::fake([
            'https://api.openai.test/v1/images/edits' => Http::response([
                'data' => [[
                    'b64_json' => base64_encode($generated),
                ]],
            ]),
        ]);

        $work = Work::create([
            'technique_id' => $this->technique->id,
            'main_image_path' => 'works/main/existing.jpg',
            'size_w_mm' => 1000,
            'size_h_mm' => 700,
            'is_published' => true,
            'sort_order' => 0,
        ]);

        Livewire::test(Edit::class, ['work' => $work])
            ->set('visualization_preset', 'bedroom')
            ->call('generateVisualization');

        $image = WorkImage::query()->sole();

        $this->assertSame('bedroom', $image->preset);
        $this->assertStringStartsWith('works/visualizations/', $image->image_path);
        Storage::disk('public')->assertExists($image->image_path);
        $this->assertSame($generated, Storage::disk('public')->get($image->image_path));

        Http::assertSent(function (Request $request) {
            return $request->url() === 'https://api.openai.test/v1/images/edits';
        });
    }

    public function test_edit_does_not_generate_when_library_limit_is_reached(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('works/main/existing.jpg', $this->imageBytes(1400, 1000));
        Http::fake();

        $work = Work::create([
            'technique_id' => $this->technique->id,
            'main_image_path' => 'works/main/existing.jpg',
            'size_w_mm' => 1000,
            'size_h_mm' => 700,
            'is_published' => true,
            'sort_order' => 0,
        ]);

        foreach (['living-room', 'bedroom', 'office'] as $index => $preset) {
            WorkImage::create([
                'work_id' => $work->id,
                'image_path' => "works/visualizations/existing-{$index}.png",
                'preset' => $preset,
                'sort_order' => $index,
            ]);
        }

        Livewire::test(Edit::class, ['work' => $work])
            ->set('visualization_preset', 'office')
            ->call('generateVisualization');

        $this->assertSame(3, WorkImage::query()->count());
        Http::assertNothingSent();
    }

    public function test_edit_requires_physical_size_before_visualization_generation(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('works/main/existing.jpg', $this->imageBytes(1400, 1000));
        Http::fake();

        $work = Work::create([
            'technique_id' => $this->technique->id,
            'main_image_path' => 'works/main/existing.jpg',
            'is_published' => true,
            'sort_order' => 0,
        ]);

        Livewire::test(Edit::class, ['work' => $work])
            ->set('visualization_preset', 'living-room')
            ->call('generateVisualization');

        $this->assertSame(0, WorkImage::query()->count());
        Http::assertNothingSent();
    }

    public function test_create_compresses_and_stores_main_image_on_public_disk(): void
    {
        Storage::fake('public');
        $source = $this->makeLargeImage('large-main.jpg', 4200, 3200);

        Livewire::test(Create::class)
            ->set('technique_id', $this->technique->id)
            ->set('is_published', true)
            ->set('sort_order', 0)
            ->set('main_image', $source)
            ->call('save')
            ->assertHasNoErrors();

        $work = Work::query()->latest('id')->firstOrFail();

        $this->assertStringStartsWith('works/main/', $work->main_image_path);
        Storage::disk('public')->assertExists($work->main_image_path);
        $this->assertLessThan(2_000_000, Storage::disk('public')->size($work->main_image_path));
    }

    public function test_edit_replaces_main_image_and_deletes_old_file(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('works/main/existing.jpg', str_repeat('a', 100));

        $work = Work::create([
            'technique_id' => $this->technique->id,
            'main_image_path' => 'works/main/existing.jpg',
            'is_published' => true,
            'sort_order' => 0,
        ]);

        Livewire::test(Edit::class, ['work' => $work])
            ->set('technique_id', $this->technique->id)
            ->set('is_published', true)
            ->set('sort_order', 0)
            ->set('main_image', $this->makeLargeImage('replacement.jpg', 4000, 2800))
            ->call('save')
            ->assertHasNoErrors();

        $work->refresh();

        Storage::disk('public')->assertMissing('works/main/existing.jpg');
        Storage::disk('public')->assertExists($work->main_image_path);
        $this->assertNotSame('works/main/existing.jpg', $work->main_image_path);
        $this->assertLessThan(2_000_000, Storage::disk('public')->size($work->main_image_path));
    }

    private function makeLargeImage(string $name, int $width, int $height): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'work-image-');
        $image = imagecreatetruecolor($width, $height);

        for ($x = 0; $x < $width; $x += 80) {
            for ($y = 0; $y < $height; $y += 80) {
                $color = imagecolorallocate($image, ($x * 13) % 255, ($y * 17) % 255, (($x + $y) * 19) % 255);
                imagefilledrectangle($image, $x, $y, min($x + 79, $width - 1), min($y + 79, $height - 1), $color);
            }
        }

        imagejpeg($image, $path, 100);
        imagedestroy($image);

        $contents = file_get_contents($path);
        unlink($path);

        return UploadedFile::fake()->createWithContent($name, $contents ?: '');
    }

    private function imageBytes(int $width, int $height): string
    {
        $path = tempnam(sys_get_temp_dir(), 'work-image-');
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
