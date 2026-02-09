<?php

namespace App\Livewire\Admin\Techniques;

use App\Models\Technique;
use Livewire\Component;

class Index extends Component
{
    public ?int $editingId = null;
    public string $name_en = '';
    public string $name_de = '';
    public string $name_ua = '';

    public function save(): void
    {
        $data = $this->validate([
            'name_en' => ['required', 'string', 'max:255'],
            'name_de' => ['required', 'string', 'max:255'],
            'name_ua' => ['required', 'string', 'max:255'],
        ]);

        if ($this->editingId) {
            Technique::whereKey($this->editingId)->update($data);
            session()->flash('success', 'Technique updated.');
        } else {
            Technique::create($data);
            session()->flash('success', 'Technique created.');
        }

        $this->resetForm();
    }

    public function edit(int $id): void
    {
        $technique = Technique::findOrFail($id);

        $this->editingId = $technique->id;
        $this->name_en = $technique->name_en;
        $this->name_de = $technique->name_de;
        $this->name_ua = $technique->name_ua;
    }

    public function delete(int $id): void
    {
        Technique::whereKey($id)->delete();
        session()->flash('success', 'Technique deleted.');
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
        $techniques = Technique::orderByDesc('id')->get();

        return view('livewire.admin.techniques.index', [
            'techniques' => $techniques,
        ]);
    }
}
