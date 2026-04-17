<?php

namespace Tests\Feature;

use App\Livewire\Admin\Works\Edit;
use App\Models\Genre;
use App\Models\Surface;
use App\Models\Technique;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WorkSurfaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_edit_can_clear_work_surface(): void
    {
        $technique = $this->technique();
        $surface = $this->surface('Canvas', 'Leinwand', 'Полотно');
        $work = Work::create([
            'technique_id' => $technique->id,
            'surface_id' => $surface->id,
            'main_image_path' => 'works/main/existing.jpg',
            'is_published' => true,
            'sort_order' => 0,
        ]);

        Livewire::test(Edit::class, ['work' => $work])
            ->set('surface_id', null)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertNull($work->refresh()->surface_id);
    }

    public function test_public_gallery_filters_by_surface_and_shows_surface_on_card(): void
    {
        $technique = $this->technique();
        $genre = Genre::create([
            'name_en' => 'Landscape',
            'name_de' => 'Landschaft',
            'name_ua' => 'Пейзаж',
        ]);
        $paper = $this->surface('Paper', 'Papier', 'Папір');
        $canvas = $this->surface('Canvas', 'Leinwand', 'Полотно');

        Work::create([
            'technique_id' => $technique->id,
            'genre_id' => $genre->id,
            'surface_id' => $paper->id,
            'main_image_path' => 'works/main/paper.jpg',
            'price_cents' => 111100,
            'currency' => 'USD',
            'is_published' => true,
            'sort_order' => 1,
        ]);
        Work::create([
            'technique_id' => $technique->id,
            'genre_id' => $genre->id,
            'surface_id' => $canvas->id,
            'main_image_path' => 'works/main/canvas.jpg',
            'price_cents' => 222200,
            'currency' => 'EUR',
            'is_published' => true,
            'sort_order' => 2,
        ]);

        $this->get('/en/gallery?surface='.$paper->id)
            ->assertOk()
            ->assertSee('Oil · Landscape · Paper')
            ->assertSee('USD 1,111')
            ->assertDontSee('EUR 2,222');
    }

    public function test_work_page_shows_surface_in_details(): void
    {
        $technique = $this->technique();
        $surface = $this->surface('Photo paper', 'Fotopapier', 'Фотопапір');
        $work = Work::create([
            'technique_id' => $technique->id,
            'surface_id' => $surface->id,
            'main_image_path' => 'works/main/photo-paper.jpg',
            'description_en' => "Quiet Light\nSoft details on photo paper.",
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $this->get('/en/gallery/'.$work->id)
            ->assertOk()
            ->assertSee('Surface')
            ->assertSee('Photo paper');
    }

    private function technique(): Technique
    {
        return Technique::create([
            'name_en' => 'Oil',
            'name_de' => 'Ol',
            'name_ua' => 'Олія',
        ]);
    }

    private function surface(string $en, string $de, string $ua): Surface
    {
        return Surface::create([
            'name_en' => $en,
            'name_de' => $de,
            'name_ua' => $ua,
        ]);
    }
}
