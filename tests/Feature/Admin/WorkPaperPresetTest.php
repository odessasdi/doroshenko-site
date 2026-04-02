<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Works\Create;
use App\Livewire\Admin\Works\Edit;
use App\Models\Technique;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WorkPaperPresetTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_applies_a3_landscape_preset(): void
    {
        Livewire::test(Create::class)
            ->call('applyPaperPreset', 'a3_landscape')
            ->assertSet('size_w_mm', 420)
            ->assertSet('size_h_mm', 297);
    }

    public function test_edit_applies_a4_portrait_preset(): void
    {
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

        Livewire::test(Edit::class, ['work' => $work])
            ->call('applyPaperPreset', 'a4_portrait')
            ->assertSet('size_w_mm', 210)
            ->assertSet('size_h_mm', 297);
    }
}
