<?php

use Livewire\Attributes\On;
use Livewire\Component;
use App\Livewire\Forms\AlternatifForm;
use App\Models\Alternatif;

new class extends Component {

    public AlternatifForm $form;

    // Gunakan nama event yang konsisten dengan dispatch di index
    #[On('edit-alternatif')]
    public function edit(int $id): void
    {
        $alternatif = Alternatif::findOrFail($id);
        $this->form->setAlternatif($alternatif);
        Flux::modal('edit-alternatif-modal')->show();
    }

    public function save(): void
    {
        $this->form->update();
        Flux::modal('edit-alternatif-modal')->close();
        session()->flash('success', 'Alternatif berhasil diperbarui!');
        $this->redirectRoute('alternatif', navigate: true);
    }
};
?>

<div>
    <flux:modal name="edit-alternatif-modal" class="md:w-96">
        <form wire:submit.prevent="save" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Edit Alternatif') }}</flux:heading>
                <flux:text class="mt-2">
                    {{ __('Perbarui nomor urut dan nama alternatif.') }}
                </flux:text>
            </div>

            <flux:input
                type="number"
                min="1"
                label="{{ __('Nomor') }}"
                wire:model.blur="form.nomor"
                :error="$errors->first('form.nomor')" />

            <flux:input
                label="{{ __('Nama Alternatif') }}"
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
                    {{ __('Simpan Perubahan') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
