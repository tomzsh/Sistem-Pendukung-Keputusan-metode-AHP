<?php

use Livewire\Component;
use App\Livewire\Forms\SubKriteriaForm;
use App\Models\Kriteria;
use Livewire\Attributes\On;

new class extends Component {

    public SubKriteriaForm $form;

    #[On('set-kriteria-id')]
    public function setKriteriaId($id)
    {
        $this->form->kriteria_id = $id;
    }


    public function save()
    {
        $kriteriaId = (int) $this->form->kriteria_id;

        $this->form->store();

        Flux::modal('tambah-sub-kriteria')->close();
        session()->flash('success', 'Sub Kriteria berhasil ditambahkan!');
        $this->dispatch('sub-kriteria-created', kriteriaId: $kriteriaId);
    }

    public function with(): array
    {
        return [
            'kriterias' => Kriteria::all(),
        ];
    }
};
?>

<div>
    <flux:modal name="tambah-sub-kriteria" class="md:w-96">
        <form wire:submit.prevent="save" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Tambah Sub Kriteria') }}</flux:heading>
                <flux:text class="mt-2">{{ __('Pilih kriteria induk dan masukkan nama sub kriteria.') }}</flux:text>
            </div>
            <flux:select label="{{ __('Kriteria') }}" wire:model="form.kriteria_id" disabled>
                @foreach ($kriterias as $kriteria)
                    <flux:select.option value="{{ $kriteria->id }}">
                        {{ $kriteria->kode }} - {{ $kriteria->nama }}
                    </flux:select.option>
                @endforeach
            </flux:select>

            <flux:input label="{{ __('Nama Sub Kriteria') }}" placeholder="Contoh: Sangat Baik, Cukup, dll"
                wire:model.blur="form.nama" />

            <div class="flex">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost" class="mr-2">{{ __('Batal') }}</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">{{ __('Simpan') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
