<?php

use Livewire\Component;
use App\Livewire\Forms\AlternatifForm;

new class extends Component {

    public AlternatifForm $form;

    public function save(): void
    {
        $this->form->store();
        Flux::modal('tambah-alternatif')->close();
        session()->flash('success', 'Alternatif berhasil ditambahkan!');
        $this->redirectRoute('alternatif', navigate: true);
    }
};
?>

<div>
    <flux:modal name="tambah-alternatif" class="md:w-96">
        <form wire:submit.prevent="save" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Tambah Alternatif') }}</flux:heading>
                <flux:text class="mt-2">
                    {{ __('Masukkan nomor urut dan nama alternatif baru.') }}
                </flux:text>
            </div>

            <flux:input
                type="number"
                min="1"
                label="{{ __('Nomor') }}"
                placeholder="Contoh: 1, 2, 3..."
                wire:model.blur="form.nomor"
                :error="$errors->first('form.nomor')" />

            <flux:input
                label="{{ __('Nama Alternatif') }}"
                placeholder="Contoh: Budi Santoso, PT Maju Jaya..."
                wire:model.blur="form.nama"
                :error="$errors->first('form.nama')" />

            <div class="flex">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button type="button" variant="ghost" class="mr-2">
                        {{ __('Batal') }}
                    </flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">
                    {{ __('Simpan') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
