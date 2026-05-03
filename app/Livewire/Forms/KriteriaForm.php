<?php

namespace App\Livewire\Forms;

use App\Models\Kriteria;
use Livewire\Attributes\Validate;
use Livewire\Form;
use Illuminate\Validation\Rule;

class KriteriaForm extends Form
{
    public ?Kriteria $kriteria = null;

    public string $kode = '';
    public string $nama = '';

    // Gunakan fungsi rules() agar validasi 'unique' bisa dinamis (mengabaikan ID sendiri saat update)
    protected function rules()
    {
        return [
            'kode' => [
                'required',
                'min:2',
                Rule::unique('kriteria', 'kode')->ignore($this->kriteria?->id)
            ],
            'nama' => [
                'required',
                'min:3',
                Rule::unique('kriteria', 'nama')->ignore($this->kriteria?->id)
            ],
        ];
    }

    public function setKriteria(Kriteria $kriteria)
    {
        $this->kriteria = $kriteria;
        $this->kode = $kriteria->kode;
        $this->nama = $kriteria->nama;
    }

    public function store()
    {
        $this->validate();
        Kriteria::create($this->only(['kode', 'nama']));
        $this->reset();
    }

    public function update()
    {
        $this->validate();
        $this->kriteria->update($this->only(['kode', 'nama']));
        $this->reset();
    }
}
