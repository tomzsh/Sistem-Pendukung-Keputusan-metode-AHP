<?php

use Livewire\Attributes\On;
use Livewire\Component;
use App\Livewire\Forms\KriteriaForm;
use App\Models\Kriteria;

new class extends Component {
    public KriteriaForm $form;

    #[On('edit-kriteria')]
    public function edit(Kriteria $kriteria)
    {
        $this->form->setKriteria($kriteria);
        $this->dispatch('modal-show', name: 'edit-kriteria-modal');
    }

    public function save()
    {
        $this->form->update();

        Flux::modal('edit-kriteria-modal')->close();

        session()->flash('success', 'Kriteria berhasil diperbarui!');
        $this->redirectRoute('kriteria', navigate: true);
    }
};
?>

<div>
    <flux:modal name="edit-kriteria-modal" class="md:w-96">
        <form wire:submit.prevent="save" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Edit Kriteria') }}</flux:heading>
                <flux:text class="mt-2">{{ __('Perbarui informasi kode dan nama kriteria.') }}</flux:text>
            </div>

            <flux:input
                label="{{ __('Kode') }}"
                wire:model.blur="form.kode"
            />

            <flux:input
                label="{{ __('Nama Kriteria') }}"
                wire:model.blur="form.nama"
            />

            <div class="flex">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost" class="mr-2">{{ __('Batal') }}</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">{{ __('Simpan Perubahan') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
