<?php

namespace App\Livewire\Admin\Works;

use App\Models\Technique;
use App\Models\Work;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $q = '';
    public ?int $techniqueId = null;
    public ?int $year = null;
    public string $published = 'all';
    public string $sortBy = 'id';
    public string $sortDir = 'desc';
    public int $perPage = 20;

    public function updated($property): void
    {
        if ($property === 'techniqueId') {
            $this->techniqueId = $this->normalizeInt($this->techniqueId);
        }

        if ($property === 'year') {
            $this->year = $this->normalizeInt($this->year);
        }

        if ($property === 'perPage') {
            $this->perPage = $this->normalizeInt($this->perPage) ?? 20;
        }

        if (in_array($property, ['q', 'techniqueId', 'year', 'published', 'sortBy', 'sortDir', 'perPage'], true)) {
            $this->resetPage();
        }
    }

    public function sort(string $field): void
    {
        $allowed = ['id', 'year', 'price', 'sort_order', 'created_at'];

        if (!in_array($field, $allowed, true)) {
            return;
        }

        if ($this->sortBy === $field) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
            return;
        }

        $this->sortBy = $field;
        $this->sortDir = 'asc';
    }

    public function resetFilters(): void
    {
        $this->q = '';
        $this->techniqueId = null;
        $this->year = null;
        $this->published = 'all';
        $this->sortBy = 'id';
        $this->sortDir = 'desc';
        $this->perPage = 20;
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        $work = Work::with('images')->findOrFail($id);

        if ($work->main_image_path) {
            Storage::delete($work->main_image_path);
        }

        foreach ($work->images as $image) {
            if ($image->image_path) {
                Storage::delete($image->image_path);
            }
        }

        $work->delete();

        session()->flash('success', 'Work deleted.');

        $this->resetPage();
    }

    public function render()
    {
        $query = Work::with('technique');

        if ($this->q !== '') {
            $q = trim($this->q);
            $query->where(function ($inner) use ($q) {
                if (ctype_digit($q)) {
                    $inner->orWhere('id', (int) $q);
                }

                $like = '%' . $q . '%';
                $inner->orWhere('description_en', 'like', $like)
                    ->orWhere('description_de', 'like', $like)
                    ->orWhere('description_ua', 'like', $like);
            });
        }

        if ($this->techniqueId) {
            $query->where('technique_id', $this->techniqueId);
        }

        if ($this->year) {
            $query->where('year', $this->year);
        }

        if ($this->published !== 'all') {
            $query->where('is_published', $this->published === 'published');
        }

        if ($this->sortBy === 'price') {
            $query->orderByRaw('price_cents is null, price_cents ' . $this->sortDir);
        } else {
            $allowed = ['id', 'year', 'sort_order', 'created_at'];
            $sortBy = in_array($this->sortBy, $allowed, true) ? $this->sortBy : 'id';
            $query->orderBy($sortBy, $this->sortDir);
        }

        $works = $query
            ->paginate($this->perPage)
            ->withPath('/admin/works');
        $techniques = Technique::orderBy('name_en')->get();

        return view('livewire.admin.works.index', [
            'works' => $works,
            'techniques' => $techniques,
        ]);
    }

    private function normalizeInt($value): ?int
    {
        if ($value === '' || $value === null) {
            return null;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        return null;
    }
}
