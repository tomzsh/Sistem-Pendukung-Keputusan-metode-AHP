<?php

namespace App\Livewire\Forms;

use App\Models\SubKriteria;
use Livewire\Attributes\Validate;
use Livewire\Form;
use Illuminate\Validation\Rule;

class SubKriteriaForm extends Form
{
    public ?SubKriteria $subKriteria = null;

    public $kriteria_id = '';
    public string $nama = '';

    protected function rules()
    {
        return [
            'kriteria_id' => [
                'required',
                'exists:kriteria,id'
            ],
            'nama' => [
                'required',
                'min:1',
                // Validasi agar nama sub-kriteria unik di dalam kriteria_id yang sama
                Rule::unique('sub_kriteria', 'nama')
                    ->where('kriteria_id', $this->kriteria_id)
                    ->ignore($this->subKriteria?->id)
            ],
        ];
    }

    public function setSubKriteria(SubKriteria $subKriteria)
    {
        $this->subKriteria = $subKriteria;
        $this->kriteria_id = $subKriteria->kriteria_id;
        $this->nama = $subKriteria->nama;
    }

    public function store()
    {
        $this->validate();
        SubKriteria::create($this->only(['kriteria_id', 'nama']));
        $this->reset();
    }

    public function update()
    {
        $this->validate();
        $this->subKriteria->update($this->only(['kriteria_id', 'nama']));
        $this->reset();
    }
}
