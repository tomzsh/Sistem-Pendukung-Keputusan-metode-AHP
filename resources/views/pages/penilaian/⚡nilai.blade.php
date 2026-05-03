<?php

use Livewire\Attributes\On;
use Livewire\Component;
use App\Livewire\Forms\PenilaianForm;
use App\Models\Alternatif;
use App\Models\Kriteria;

new class extends Component {

    public PenilaianForm $form;
    public ?Alternatif $alternatif = null;
    public $kriterias = [];

    public function mount(): void
    {
        // Load semua kriteria beserta sub_kriteria-nya
        $this->kriterias = Kriteria::with('subKriteria')->get();
    }

    #[On('nilai-alternatif')]
    public function loadAlternatif(int $id): void
    {
        $alternatif = Alternatif::findOrFail($id);
        $this->alternatif = $alternatif;

        // Set form tanpa pre-load data (penilaian baru)
        $this->form->setAlternatifOnly($alternatif);

        Flux::modal('nilai-penilaian-modal')->show();
    }

    public function save(): void
    {
        $this->form->store();
        Flux::modal('nilai-penilaian-modal')->close();
        session()->flash('success', "Penilaian untuk \"{$this->alternatif->nama}\" berhasil disimpan.");
        $this->redirectRoute('penilaian', navigate: true);
    }
};
?>

<div>
    <flux:modal name="nilai-penilaian-modal" class="md:w-[560px]">
        <form wire:submit.prevent="save" class="space-y-5">

            {{-- Header --}}
            <div class="pb-2 border-b border-zinc-100 dark:border-zinc-800">
                <flux:heading size="lg">{{ __('Penilaian Alternatif') }}</flux:heading>
                @if ($alternatif)
                    <flux:text class="mt-1">
                        Pilih sub kriteria yang sesuai untuk
                        <span class="font-semibold text-zinc-800 dark:text-white">
                            A{{ $alternatif->nomor }} — {{ $alternatif->nama }}
                        </span>
                    </flux:text>
                @endif
            </div>

            {{-- Selects per kriteria --}}
            <div class="space-y-4 max-h-[60vh] overflow-y-auto pr-1">

                @if ($kriterias->isEmpty())
                    <div class="py-6 text-center text-sm text-zinc-400 italic">
                        Belum ada data kriteria. Tambahkan kriteria terlebih dahulu.
                    </div>
                @else
                    @foreach ($kriterias as $kriteria)
                        <div class="space-y-1.5">
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-bold
                                             bg-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-300 mr-1">
                                    {{ $kriteria->kode }}
                                </span>
                                {{ $kriteria->nama }}
                                <span class="text-red-500 ml-0.5">*</span>
                            </label>

                            @if ($kriteria->subKriteria->isEmpty())
                                <p class="text-xs text-amber-500 italic flex items-center gap-2">
                                    <flux:icon.exclamation-triangle class="size-4" />
                                    Belum ada sub kriteria untuk kriteria ini.
                                </p>
                            @else
                                <select
                                    wire:model="form.selections.{{ $kriteria->id }}"
                                    class="w-full px-3 py-2 text-sm text-gray-700 bg-white border rounded-lg
                                           focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400
                                           hover:border-gray-400 transition
                                           @error("form.selections.{$kriteria->id}") border-red-400 bg-red-50 @enderror">

                                    <option value="">— Pilih Sub Kriteria —</option>
                                    @foreach ($kriteria->subKriteria as $sub)
                                        <option value="{{ $sub->id }}">{{ $sub->nama }}</option>
                                    @endforeach
                                </select>

                                @error("form.selections.{$kriteria->id}")
                                    <p class="text-xs text-red-500 mt-0.5">{{ $message }}</p>
                                @enderror
                            @endif
                        </div>
                    @endforeach
                @endif

            </div>

            {{-- Footer --}}
            <div class="flex pt-2 border-t border-zinc-100 dark:border-zinc-800">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button type="button" variant="ghost" class="mr-2">
                        {{ __('Batal') }}
                    </flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary" icon="check">
                    {{ __('Simpan Penilaian') }}
                </flux:button>
            </div>

        </form>
    </flux:modal>
</div>
