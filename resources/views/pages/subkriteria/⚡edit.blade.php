<?php

use Livewire\Attributes\On;
use Livewire\Component;
use App\Livewire\Forms\SubKriteriaForm;
use App\Models\SubKriteria;
use App\Models\Kriteria;

new class extends Component {
    public SubKriteriaForm $form;
    public $currentKriteria = null;

    #[On('edit-sub-kriteria')]
    public function edit(SubKriteria $subKriteria)
    {
        $this->form->setSubKriteria($subKriteria);
        // Ambil data kriteria induknya
        $this->currentKriteria = $subKriteria->kriteria;

        Flux::modal('edit-sub-kriteria-modal')->show();
    }

    public function save()
    {
        $this->form->update();
        Flux::modal('edit-sub-kriteria-modal')->close();
        session()->flash('success', 'Sub Kriteria berhasil diperbarui!');
        $this->redirectRoute('sub-kriteria', navigate: true);
    }
};
?>

<div>
    <flux:modal name="edit-sub-kriteria-modal" class="md:w-96">
        <form wire:submit.prevent="save" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Edit Sub Kriteria') }}</flux:heading>
                <flux:text class="mt-2">{{ __('Mengedit detail untuk kriteria:') }} <span class="font-bold text-zinc-900 dark:text-white">{{ $currentKriteria?->nama }}</span></flux:text>
            </div>

            <flux:select label="{{ __('Kriteria') }}" wire:model="form.kriteria_id">
                @if($currentKriteria)
                    <flux:select.option value="{{ $currentKriteria->id }}">
                        {{ $currentKriteria->kode }} - {{ $currentKriteria->nama }}
                    </flux:select.option>
                @endif
            </flux:select>

            <flux:input
                label="{{ __('Nama Sub Kriteria') }}"
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
