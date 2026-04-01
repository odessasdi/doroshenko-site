<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Works\Create;
use App\Livewire\Admin\Works\Edit;
use App\Models\Technique;
use App\Models\Work;
use App\Models\WorkImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
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
    }

    public function test_create_rejects_more_than_three_additional_images(): void
    {
        Storage::fake('public');

        Livewire::test(Create::class)
            ->set('technique_id', $this->technique->id)
            ->set('is_published', true)
            ->set('sort_order', 0)
            ->set('main_image', UploadedFile::fake()->image('main.jpg'))
            ->set('additional_images', [
                UploadedFile::fake()->image('a1.jpg'),
                UploadedFile::fake()->image('a2.jpg'),
                UploadedFile::fake()->image('a3.jpg'),
                UploadedFile::fake()->image('a4.jpg'),
            ])
            ->call('save')
            ->assertHasErrors(['additional_images' => 'max']);
    }

    public function test_edit_rejects_when_total_additional_images_exceeds_three(): void
    {
        Storage::fake('public');

        $work = Work::create([
            'technique_id' => $this->technique->id,
            'main_image_path' => 'works/main/existing.jpg',
            'is_published' => true,
            'sort_order' => 0,
        ]);

        WorkImage::create([
            'work_id' => $work->id,
            'image_path' => 'works/additional/existing-1.jpg',
            'sort_order' => 0,
        ]);
        WorkImage::create([
            'work_id' => $work->id,
            'image_path' => 'works/additional/existing-2.jpg',
            'sort_order' => 1,
        ]);

        Livewire::test(Edit::class, ['work' => $work])
            ->set('technique_id', $this->technique->id)
            ->set('is_published', true)
            ->set('sort_order', 0)
            ->set('additional_images', [
                UploadedFile::fake()->image('new-1.jpg'),
                UploadedFile::fake()->image('new-2.jpg'),
            ])
            ->call('save')
            ->assertHasErrors(['additional_images' => 'max']);
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

    public function test_create_compresses_additional_images_with_current_paths(): void
    {
        Storage::fake('public');

        Livewire::test(Create::class)
            ->set('technique_id', $this->technique->id)
            ->set('is_published', true)
            ->set('sort_order', 0)
            ->set('main_image', $this->makeLargeImage('main.jpg', 3600, 2600))
            ->set('additional_images', [
                $this->makeLargeImage('detail.png', 3800, 2800),
            ])
            ->call('save')
            ->assertHasNoErrors();

        $image = WorkImage::query()->sole();

        $this->assertStringStartsWith('works/additional/', $image->image_path);
        Storage::disk('public')->assertExists($image->image_path);
        $this->assertLessThan(2_000_000, Storage::disk('public')->size($image->image_path));
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
}
