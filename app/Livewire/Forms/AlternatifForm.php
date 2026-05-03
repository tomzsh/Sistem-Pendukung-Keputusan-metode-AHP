<?php

namespace App\Livewire\Forms;

use App\Models\Alternatif;
use Livewire\Form;
use Illuminate\Validation\Rule;

class AlternatifForm extends Form
{
    public ?Alternatif $alternatif = null;

    public string $nama   = '';
    public string $nomor  = '';

    protected function rules(): array
    {
        return [
            'nomor' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('alternatif', 'nomor')
                    ->ignore($this->alternatif?->id),
            ],
            'nama' => [
                'required',
                'string',
                'min:2',
                'max:100',
                Rule::unique('alternatif', 'nama')
                    ->ignore($this->alternatif?->id),
            ],
        ];
    }

    protected function messages(): array
    {
        return [
            'nomor.required' => 'Nomor alternatif wajib diisi.',
            'nomor.integer'  => 'Nomor harus berupa angka.',
            'nomor.min'      => 'Nomor minimal 1.',
            'nomor.unique'   => 'Nomor alternatif sudah digunakan.',
            'nama.required'  => 'Nama alternatif wajib diisi.',
            'nama.min'       => 'Nama minimal 2 karakter.',
            'nama.max'       => 'Nama maksimal 100 karakter.',
            'nama.unique'    => 'Nama alternatif sudah terdaftar.',
        ];
    }

    public function setAlternatif(Alternatif $alternatif): void
    {
        $this->alternatif = $alternatif;
        $this->nomor      = (string) $alternatif->nomor;
        $this->nama       = $alternatif->nama;
    }

    public function store(): void
    {
        $this->validate();

        Alternatif::create([
            'nomor' => (int) $this->nomor,
            'nama'  => $this->nama,
        ]);

        $this->reset();
    }

    public function update(): void
    {
        $this->validate();

        $this->alternatif->update([
            'nomor' => (int) $this->nomor,
            'nama'  => $this->nama,
        ]);

        $this->reset();
    }
}
