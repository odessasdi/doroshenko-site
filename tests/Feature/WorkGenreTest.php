<?php

namespace Tests\Feature;

use App\Livewire\Admin\Works\Edit;
use App\Models\Genre;
use App\Models\Technique;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WorkGenreTest extends TestCase
{
    use RefreshDatabase;

    public function test_edit_can_clear_work_genre(): void
    {
        $technique = $this->technique();
        $genre = Genre::create([
            'name_en' => 'Landscape',
            'name_de' => 'Landschaft',
            'name_ua' => 'Пейзаж',
        ]);
        $work = Work::create([
            'technique_id' => $technique->id,
            'genre_id' => $genre->id,
            'main_image_path' => 'works/main/existing.jpg',
            'is_published' => true,
            'sort_order' => 0,
        ]);

        Livewire::test(Edit::class, ['work' => $work])
            ->set('genre_id', null)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertNull($work->refresh()->genre_id);
    }

    public function test_public_gallery_filters_by_genre(): void
    {
        $technique = $this->technique();
        $landscape = Genre::create([
            'name_en' => 'Landscape',
            'name_de' => 'Landschaft',
            'name_ua' => 'Пейзаж',
        ]);
        $portrait = Genre::create([
            'name_en' => 'Portrait',
            'name_de' => 'Portrat',
            'name_ua' => 'Портрет',
        ]);

        Work::create([
            'technique_id' => $technique->id,
            'genre_id' => $landscape->id,
            'main_image_path' => 'works/main/landscape.jpg',
            'price_cents' => 111100,
            'currency' => 'USD',
            'is_published' => true,
            'sort_order' => 1,
        ]);
        Work::create([
            'technique_id' => $technique->id,
            'genre_id' => $portrait->id,
            'main_image_path' => 'works/main/portrait.jpg',
            'price_cents' => 222200,
            'currency' => 'EUR',
            'is_published' => true,
            'sort_order' => 2,
        ]);

        $this->get('/en/gallery?collection='.$landscape->id)
            ->assertOk()
            ->assertSee('Oil · Landscape')
            ->assertSee('USD 1,111')
            ->assertDontSee('EUR 2,222');
    }

    public function test_public_gallery_uses_first_description_line_as_card_title(): void
    {
        $technique = $this->technique();
        $genre = Genre::create([
            'name_en' => 'Landscape',
            'name_de' => 'Landschaft',
            'name_ua' => 'Пейзаж',
        ]);

        Work::create([
            'technique_id' => $technique->id,
            'genre_id' => $genre->id,
            'main_image_path' => 'works/main/gallery-title.jpg',
            'description_en' => "Midnight Garden\nA quiet path under deep green branches.",
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $this->get('/en/gallery')
            ->assertOk()
            ->assertSee(
                '<div class="mt-3 text-lg font-semibold text-zinc-900">Midnight Garden</div>',
                false,
            )
            ->assertSeeInOrder(['Midnight Garden', 'Oil · Landscape']);
    }

    public function test_public_gallery_keeps_legacy_genre_filter_url_working(): void
    {
        $technique = $this->technique();
        $landscape = Genre::create([
            'name_en' => 'Landscape',
            'name_de' => 'Landschaft',
            'name_ua' => 'Пейзаж',
        ]);

        Work::create([
            'technique_id' => $technique->id,
            'genre_id' => $landscape->id,
            'main_image_path' => 'works/main/legacy-genre.jpg',
            'price_cents' => 111100,
            'currency' => 'USD',
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $this->get('/en/gallery?genre='.$landscape->id)
            ->assertOk()
            ->assertSee('Oil · Landscape')
            ->assertSee('USD 1,111');
    }

    public function test_work_page_more_works_uses_same_genre(): void
    {
        $technique = $this->technique();
        $landscape = Genre::create([
            'name_en' => 'Landscape',
            'name_de' => 'Landschaft',
            'name_ua' => 'Пейзаж',
        ]);
        $portrait = Genre::create([
            'name_en' => 'Portrait',
            'name_de' => 'Portrat',
            'name_ua' => 'Портрет',
        ]);

        $current = Work::create([
            'technique_id' => $technique->id,
            'genre_id' => $landscape->id,
            'main_image_path' => 'works/main/current.jpg',
            'price_cents' => 100000,
            'currency' => 'USD',
            'is_published' => true,
            'sort_order' => 1,
        ]);
        Work::create([
            'technique_id' => $technique->id,
            'genre_id' => $landscape->id,
            'main_image_path' => 'works/main/same-genre.jpg',
            'price_cents' => 200000,
            'currency' => 'USD',
            'is_published' => true,
            'sort_order' => 2,
        ]);
        Work::create([
            'technique_id' => $technique->id,
            'genre_id' => $portrait->id,
            'main_image_path' => 'works/main/other-genre.jpg',
            'price_cents' => 300000,
            'currency' => 'USD',
            'is_published' => true,
            'sort_order' => 3,
        ]);

        $this->get('/en/gallery/'.$current->id)
            ->assertOk()
            ->assertSee('USD 2,000')
            ->assertDontSee('USD 3,000');
    }

    public function test_work_page_uses_first_description_line_as_title(): void
    {
        $technique = $this->technique();
        $genre = Genre::create([
            'name_en' => 'Landscape',
            'name_de' => 'Landschaft',
            'name_ua' => 'Пейзаж',
        ]);
        $work = Work::create([
            'technique_id' => $technique->id,
            'genre_id' => $genre->id,
            'main_image_path' => 'works/main/titled.jpg',
            'description_en' => "Midnight Garden\nA quiet path under deep green branches.",
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $this->get('/en/gallery/'.$work->id)
            ->assertOk()
            ->assertSee('Midnight Garden')
            ->assertSee('A quiet path under deep green branches.')
            ->assertDontSee('>Landscape</h1>', false)
            ->assertDontSee('>Oil</h1>', false)
            ->assertSee(
                '<p class="mt-2 whitespace-pre-line text-base leading-relaxed text-zinc-700">Midnight Garden',
                false,
            );
    }

    public function test_work_page_handles_malformed_utf8_in_description_title(): void
    {
        $technique = $this->technique();
        $work = Work::create([
            'technique_id' => $technique->id,
            'main_image_path' => 'works/main/malformed.jpg',
            'description_en' => 'Broken title '.chr(0xD1)."\nBody text",
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $this->get('/en/gallery/'.$work->id)
            ->assertOk()
            ->assertSee('Broken title')
            ->assertSee('Body text');
    }

    public function test_work_page_keeps_multibyte_description_title_intact(): void
    {
        $technique = $this->technique();
        $work = Work::create([
            'technique_id' => $technique->id,
            'main_image_path' => 'works/main/ukrainian-title.jpg',
            'description_ua' => "Дует птахів\nЦя робота показує двох курей.",
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $this->get('/ua/gallery/'.$work->id)
            ->assertOk()
            ->assertSee('Дует птахів')
            ->assertDontSee('Дует пта?');
    }

    private function technique(): Technique
    {
        return Technique::create([
            'name_en' => 'Oil',
            'name_de' => 'Ol',
            'name_ua' => 'Олія',
        ]);
    }
}
