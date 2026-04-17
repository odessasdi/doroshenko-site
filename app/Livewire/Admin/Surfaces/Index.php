<?php

namespace App\Livewire\Admin\Surfaces;

use App\Models\Surface;
use Livewire\Component;

class Index extends Component
{
    public ?int $editingId = null;

    public string $name_en = '';

    public string $name_de = '';

    public string $name_ua = '';

    public function save()
    {
        $data = $this->validate([
            'name_en' => ['required', 'string', 'max:255'],
            'name_de' => ['required', 'string', 'max:255'],
            'name_ua' => ['required', 'string', 'max:255'],
        ]);

        if ($this->editingId) {
            Surface::whereKey($this->editingId)->update($data);
            session()->flash('success', 'Основу оновлено.');
            $this->resetForm();
        } else {
            Surface::create($data);
            session()->flash('success', 'Основу створено.');

            return $this->redirectRoute('admin.surfaces.index');
        }
    }

    public function edit(int $id): void
    {
        $surface = Surface::findOrFail($id);

        $this->editingId = $surface->id;
        $this->name_en = $surface->name_en;
        $this->name_de = $surface->name_de;
        $this->name_ua = $surface->name_ua;
    }

    public function delete(int $id): void
    {
        Surface::whereKey($id)->delete();
        session()->flash('success', 'Основу видалено.');
    }

    public function resetForm(): void
    {
        $this->editingId = null;
        $this->name_en = '';
        $this->name_de = '';
        $this->name_ua = '';
    }

    public function render()
    {
        $surfaces = Surface::orderByDesc('id')->get();

        return view('livewire.admin.surfaces.index', [
            'surfaces' => $surfaces,
        ]);
    }
}
