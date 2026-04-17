<?php

namespace App\Livewire\Admin\Genres;

use App\Models\Genre;
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
            Genre::whereKey($this->editingId)->update($data);
            session()->flash('success', 'Колекцію оновлено.');
            $this->resetForm();
        } else {
            Genre::create($data);
            session()->flash('success', 'Колекцію створено.');

            return $this->redirectRoute('admin.collections.index');
        }
    }

    public function edit(int $id): void
    {
        $genre = Genre::findOrFail($id);

        $this->editingId = $genre->id;
        $this->name_en = $genre->name_en;
        $this->name_de = $genre->name_de;
        $this->name_ua = $genre->name_ua;
    }

    public function delete(int $id): void
    {
        Genre::whereKey($id)->delete();
        session()->flash('success', 'Колекцію видалено.');
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
        $genres = Genre::orderByDesc('id')->get();

        return view('livewire.admin.genres.index', [
            'genres' => $genres,
        ]);
    }
}
