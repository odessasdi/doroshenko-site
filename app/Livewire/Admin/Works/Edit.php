<?php

namespace App\Livewire\Admin\Works;

use App\Exceptions\WorkDescriptionGenerationException;
use App\Models\Technique;
use App\Models\Work;
use App\Models\WorkImage;
use App\Services\WorkDescriptionGenerationService;
use App\Services\WorkImageStorageService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class Edit extends Component
{
    use WithFileUploads;

    public Work $work;

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
        $this->resetErrorBag(['main_image', 'description_ua', 'description_en', 'description_de']);

        try {
            if ($this->main_image) {
                $this->validate([
                    'main_image' => WorkImageStorageService::optionalRules(),
                ]);

                $descriptions = app(WorkDescriptionGenerationService::class)->generateFromUpload($this->main_image);
            } elseif ($this->work->main_image_path) {
                $descriptions = app(WorkDescriptionGenerationService::class)
                    ->generateFromStoredImage($this->work->main_image_path);
            } else {
                session()->flash('error', 'Спочатку завантажте головне зображення.');

                return;
            }
        } catch (WorkDescriptionGenerationException $exception) {
            session()->flash('error', $exception->getMessage());

            return;
        }

        $this->description_ua = $descriptions['ua'];
        $this->description_en = $descriptions['en'];
        $this->description_de = $descriptions['de'];

        session()->flash('success', 'Описи згенеровано.');
    }

    public function mount(Work $work): void
    {
        $this->work = $work;
        $this->technique_id = $work->technique_id;
        $this->year = $work->year;
        $this->size_w_mm = $work->size_w_mm;
        $this->size_h_mm = $work->size_h_mm;
        $this->price = $work->price_cents !== null
            ? number_format($work->price_cents / 100, 2, '.', '')
            : null;
        $this->currency = $work->currency;
        $this->description_en = $work->description_en;
        $this->description_de = $work->description_de;
        $this->description_ua = $work->description_ua;
        $this->is_published = (bool) $work->is_published;
        $this->sort_order = $work->sort_order;
    }

    public function deleteExtraImage(int $id): void
    {
        $image = WorkImage::where('work_id', $this->work->id)->whereKey($id)->firstOrFail();

        if ($image->image_path) {
            Storage::disk('public')->delete($image->image_path);
        }

        $image->delete();

        session()->flash('success', 'Додаткове зображення видалено.');
    }

    public function save()
    {
        $remaining = $this->remainingExtraSlots();

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
            'main_image' => WorkImageStorageService::optionalRules(),
            'additional_images' => ['nullable', 'array', 'max:'.$remaining],
            'additional_images.*' => WorkImageStorageService::itemRules(),
        ]);

        $priceCents = $this->price !== null && $this->price !== ''
            ? $this->toCents($this->price)
            : null;

        $imageStorage = app(WorkImageStorageService::class);

        DB::transaction(function () use ($data, $priceCents, $imageStorage) {
            if ($this->main_image) {
                if ($this->work->main_image_path) {
                    Storage::disk('public')->delete($this->work->main_image_path);
                }

                $this->work->main_image_path = $imageStorage->store($this->main_image, 'works/main', 'main_image');
            }

            $this->work->technique_id = $data['technique_id'];
            $this->work->year = $data['year'];
            $this->work->size_w_mm = $data['size_w_mm'];
            $this->work->size_h_mm = $data['size_h_mm'];
            $this->work->price_cents = $priceCents;
            $this->work->currency = $data['currency'];
            $this->work->description_en = $data['description_en'];
            $this->work->description_de = $data['description_de'];
            $this->work->description_ua = $data['description_ua'];
            $this->work->is_published = $data['is_published'];
            $this->work->sort_order = $data['sort_order'];
            $this->work->save();

            $nextSort = (int) ($this->work->images()->max('sort_order') ?? -1) + 1;

            foreach ($this->additional_images as $image) {
                $path = $imageStorage->store($image, 'works/additional', 'additional_image');

                WorkImage::create([
                    'work_id' => $this->work->id,
                    'image_path' => $path,
                    'sort_order' => $nextSort,
                ]);

                $nextSort++;
            }
        });

        $this->additional_images = [];
        $this->main_image = null;

        session()->flash('success', 'Роботу оновлено.');
        return $this->redirectRoute('admin.works.index');
    }

    private function remainingExtraSlots(): int
    {
        $current = $this->work->images()->count();

        return max(0, 3 - $current);
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
        $workImages = $this->work->images()->orderBy('sort_order')->get();

        return view('livewire.admin.works.edit', [
            'techniques' => $techniques,
            'workImages' => $workImages,
            'remainingExtra' => $this->remainingExtraSlots(),
        ]);
    }
}
