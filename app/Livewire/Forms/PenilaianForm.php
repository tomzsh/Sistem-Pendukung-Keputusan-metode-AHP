<?php

namespace App\Livewire\Forms;

use App\Models\Alternatif;
use App\Models\Kriteria;
use App\Models\Penilaian;
use Livewire\Form;
use Illuminate\Validation\Rule;

class PenilaianForm extends Form
{
    public ?Alternatif $alternatif = null;
    public ?int $id_alternatif = null;

    // [kriteria_id => sub_kriteria_id]
    public array $selections = [];

    protected function rules(): array
    {
        $rules = [
            'id_alternatif' => ['required', 'exists:alternatif,id'],
        ];

        // Validasi dinamis — setiap kriteria wajib dipilih sub_kriteria-nya
        $kriterias = Kriteria::with('subKriteria')->get();

        foreach ($kriterias as $kriteria) {
            // Hanya validasi jika kriteria punya sub_kriteria
            if ($kriteria->subKriteria->isNotEmpty()) {
                $rules["selections.{$kriteria->id}"] = [
                    'required',
                    Rule::exists('sub_kriteria', 'id')
                        ->where('kriteria_id', $kriteria->id),
                ];
            }
        }

        return $rules;
    }

    protected function messages(): array
    {
        $messages = [
            'id_alternatif.required' => 'Alternatif wajib dipilih.',
        ];

        $kriterias = Kriteria::with('subKriteria')->get();

        foreach ($kriterias as $kriteria) {
            $messages["selections.{$kriteria->id}.required"] =
                "Sub kriteria untuk \"{$kriteria->nama}\" wajib dipilih.";
            $messages["selections.{$kriteria->id}.exists"] =
                "Sub kriteria untuk \"{$kriteria->nama}\" tidak valid.";
        }

        return $messages;
    }

    /**
     * Set alternatif dan muat penilaian yang sudah ada (untuk edit)
     */
    public function setAlternatif(Alternatif $alternatif): void
    {
        $this->alternatif    = $alternatif;
        $this->id_alternatif = $alternatif->id;

        // Pre-load selections dari database
        $existing = Penilaian::where('id_alternatif', $alternatif->id)->get();

        foreach ($existing as $penilaian) {
            $this->selections[$penilaian->id_kriteria] = $penilaian->id_sub_kriteria;
        }
    }

    /**
     * Set alternatif saja tanpa load data (untuk nilai baru)
     */
    public function setAlternatifOnly(Alternatif $alternatif): void
    {
        $this->alternatif    = $alternatif;
        $this->id_alternatif = $alternatif->id;
        $this->selections    = [];
    }

    /**
     * Simpan penilaian baru (upsert agar aman dari duplikat)
     */
    public function store(): void
    {
        $this->validate();

        foreach ($this->selections as $kriteriaId => $subKriteriaId) {
            Penilaian::updateOrCreate(
                [
                    'id_alternatif' => $this->id_alternatif,
                    'id_kriteria'   => (int) $kriteriaId,
                ],
                [
                    'id_sub_kriteria' => (int) $subKriteriaId,
                ]
            );
        }

        $this->reset();
    }

    /**
     * Update penilaian yang sudah ada
     */
    public function update(): void
    {
        $this->validate();

        foreach ($this->selections as $kriteriaId => $subKriteriaId) {
            Penilaian::updateOrCreate(
                [
                    'id_alternatif' => $this->id_alternatif,
                    'id_kriteria'   => (int) $kriteriaId,
                ],
                [
                    'id_sub_kriteria' => (int) $subKriteriaId,
                ]
            );
        }

        $this->reset();
    }
}
