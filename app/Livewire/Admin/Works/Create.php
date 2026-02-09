<?php

namespace App\Livewire\Admin\Works;

use App\Models\Technique;
use App\Models\Work;
use App\Models\WorkImage;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;

class Create extends Component
{
    use WithFileUploads;

    public ?int $technique_id = null;
    public ?int $year = null;
    public ?int $size_w_mm = null;
    public ?int $size_h_mm = null;
    public ?string $price = null;
    public ?string $currency = null;
    public ?string $description_en = null;
    public ?string $description_de = null;
    public ?string $description_ua = null;
    public bool $is_published = true;
    public int $sort_order = 0;

    public $main_image;
    public array $extra_images = [];

    public function save(): void
    {
        $data = $this->validate([
            'technique_id' => ['required', 'exists:techniques,id'],
            'year' => ['nullable', 'integer', 'between:1800,2100'],
            'size_w_mm' => ['nullable', 'integer', 'min:1'],
            'size_h_mm' => ['nullable', 'integer', 'min:1'],
            'price' => ['nullable', 'regex:/^\d+(\.\d{1,2})?$/'],
            'currency' => ['nullable', 'in:USD,EUR,UAH', 'required_with:price'],
            'description_en' => ['nullable', 'string'],
            'description_de' => ['nullable', 'string'],
            'description_ua' => ['nullable', 'string'],
            'is_published' => ['boolean'],
            'sort_order' => ['integer'],
            'main_image' => ['required', 'image', 'max:8192'],
            'extra_images' => ['nullable', 'array', 'max:3'],
            'extra_images.*' => ['image', 'max:8192'],
        ]);

        $priceCents = $this->price !== null && $this->price !== ''
            ? $this->toCents($this->price)
            : null;

        DB::transaction(function () use ($data, $priceCents) {
            $mainPath = $this->main_image->store('works/main', 'public');

            $work = Work::create([
                'technique_id' => $data['technique_id'],
                'year' => $data['year'],
                'size_w_mm' => $data['size_w_mm'],
                'size_h_mm' => $data['size_h_mm'],
                'main_image_path' => $mainPath,
                'price_cents' => $priceCents,
                'currency' => $data['currency'],
                'description_en' => $data['description_en'],
                'description_de' => $data['description_de'],
                'description_ua' => $data['description_ua'],
                'is_published' => $data['is_published'],
                'sort_order' => $data['sort_order'],
            ]);

            foreach ($this->extra_images as $index => $image) {
                $path = $image->store('works/extra', 'public');

                WorkImage::create([
                    'work_id' => $work->id,
                    'image_path' => $path,
                    'sort_order' => $index,
                ]);
            }
        });

        session()->flash('success', 'Work created.');

        $this->redirect('/admin/works', navigate: true);
    }

    private function toCents(string $value): int
    {
        $normalized = str_replace(',', '.', trim($value));

        if (str_contains($normalized, '.')) {
            [$whole, $fraction] = explode('.', $normalized, 2);
            $fraction = str_pad(substr($fraction, 0, 2), 2, '0');
        } else {
            $whole = $normalized;
            $fraction = '00';
        }

        return ((int) $whole) * 100 + (int) $fraction;
    }

    public function render()
    {
        $techniques = Technique::orderBy('name_en')->get();

        return view('livewire.admin.works.create', [
            'techniques' => $techniques,
        ]);
    }
}
