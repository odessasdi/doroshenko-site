<?php

namespace App\Livewire\Admin\Works;

use App\Livewire\Admin\Works\Concerns\UsesPaperSizePresets;
use App\Exceptions\WorkDescriptionGenerationException;
use App\Models\Technique;
use App\Models\Work;
use App\Models\WorkImage;
use App\Services\WorkDescriptionGenerationService;
use App\Services\WorkImageStorageService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;

class Create extends Component
{
    use WithFileUploads;
    use UsesPaperSizePresets;

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
    public array $additional_images = [];

    public function generateDescriptions(): void
    {
        if (! $this->main_image) {
            session()->flash('error', 'Спочатку завантажте головне зображення.');

            return;
        }

        $this->resetErrorBag(['main_image', 'description_ua', 'description_en', 'description_de']);

        $this->validate([
            'main_image' => WorkImageStorageService::requiredRules(),
        ]);

        try {
            $descriptions = app(WorkDescriptionGenerationService::class)->generateFromUpload($this->main_image);
        } catch (WorkDescriptionGenerationException $exception) {
            session()->flash('error', $exception->getMessage());

            return;
        }

        $this->description_ua = $descriptions['ua'];
        $this->description_en = $descriptions['en'];
        $this->description_de = $descriptions['de'];

        session()->flash('success', 'Описи згенеровано.');
    }

    public function save()
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
            'main_image' => WorkImageStorageService::requiredRules(),
            'additional_images' => ['nullable', 'array', 'max:3'],
            'additional_images.*' => WorkImageStorageService::itemRules(),
        ]);

        $priceCents = $this->price !== null && $this->price !== ''
            ? $this->toCents($this->price)
            : null;

        $imageStorage = app(WorkImageStorageService::class);

        DB::transaction(function () use ($data, $priceCents, $imageStorage) {
            $mainPath = $imageStorage->store($this->main_image, 'works/main', 'main_image');

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

            foreach ($this->additional_images as $index => $image) {
                $path = $imageStorage->store($image, 'works/additional', "additional_images.$index");

                WorkImage::create([
                    'work_id' => $work->id,
                    'image_path' => $path,
                    'sort_order' => $index,
                ]);
            }
        });

        session()->flash('success', 'Роботу створено.');
        return $this->redirectRoute('admin.works.index');
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
            'remainingAdditional' => max(0, 3 - count($this->additional_images)),
            'paperPresets' => $this->paperPresets(),
        ]);
    }
}
