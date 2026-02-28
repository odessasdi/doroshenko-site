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

    public function test_create_rejects_more_than_three_additional_images(): void
    {
        Storage::fake('public');
        $technique = Technique::create([
            'name_en' => 'Oil',
            'name_de' => 'Ol',
            'name_ua' => 'Олія',
        ]);

        Livewire::test(Create::class)
            ->set('technique_id', $technique->id)
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
        $technique = Technique::create([
            'name_en' => 'Oil',
            'name_de' => 'Ol',
            'name_ua' => 'Олія',
        ]);

        $work = Work::create([
            'technique_id' => $technique->id,
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
            ->set('technique_id', $technique->id)
            ->set('is_published', true)
            ->set('sort_order', 0)
            ->set('additional_images', [
                UploadedFile::fake()->image('new-1.jpg'),
                UploadedFile::fake()->image('new-2.jpg'),
            ])
            ->call('save')
            ->assertHasErrors(['additional_images' => 'max']);
    }
}
