<?php

use Livewire\Component;
use App\Livewire\Forms\KriteriaForm;

new class extends Component {

    public KriteriaForm $form;

    public function save()
    {
        $this->form->store();
        Flux::modal('tambah-kriteria')->close();
        session()->flash('success', 'Kriteria berhasil ditambahkan!');
        $this->redirectRoute('kriteria', navigate: true);
    }
};
?>

<div>
    <flux:modal name="tambah-kriteria" class="md:w-96">
        <form wire:submit.prevent="save" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Tambah Kriteria') }}</flux:heading>
                <flux:text class="mt-2">{{ __('Masukkan detail kode dan nama kriteria baru.') }}</flux:text>
            </div>

            <flux:input
                label="{{ __('Kode') }}"
                placeholder="Contoh: C1, K1, atau KRT01..."
                wire:model.blur="form.kode"
            />

            <flux:input
                label="{{ __('Nama Kriteria') }}"
                placeholder="Contoh: Pengalaman Kerja, Usia..."
                wire:model.blur="form.nama"
            />

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
