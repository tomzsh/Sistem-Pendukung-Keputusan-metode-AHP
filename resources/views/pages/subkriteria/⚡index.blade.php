<?php

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use App\Models\SubKriteria;
use App\Models\Kriteria;

new class extends Component {
    use WithPagination;

    public string $search = '';
    public bool $showMatriksutama = false;

    public $activeKriteria = null;

    public function openModal($id)
    {
        $this->activeKriteria = $id;
    }

    public function closeModal()
    {
        $this->activeKriteria = null;
    }

    public function toggleKriteria($id)
    {
        $this->activeKriteria = $this->activeKriteria === $id ? null : $id;
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    #[On('sub-kriteria-created')]
    public function scrollToKriteria($kriteriaId)
    {
        $this->dispatch('scroll-to-kriteria', kriteriaId: (int) $kriteriaId);
    }

    public function delete(SubKriteria $subKriteria)
    {
        $subKriteria->delete();
        session()->flash('success', 'Sub Kriteria berhasil dihapus.');
    }

    public function with(): array
    {
        return [
            'kriterias' => Kriteria::with([
                'subKriteria' => function ($query) {
                    $query->where('nama', 'like', '%' . $this->search . '%');
                }
            ])->get(),
        ];
    }
    public function toggleMatriksutama()
    {
        $this->showMatriksutama = !$this->showMatriksutama;
    }
};
?>

<div
    x-data
    x-on:scroll-to-kriteria.window="
        setTimeout(() => {
            document.getElementById('kriteria-' + $event.detail.kriteriaId)?.scrollIntoView({
                behavior: 'smooth',
                block: 'start',
            });
        }, 50);
    "
>
    <header class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <flux:heading size="xl" level="1" class="flex items-center gap-2">
                <flux:icon.list-bullet class="size-6 text-zinc-500" />
                {{ __('Sub Kriteria') }}
            </flux:heading>
            <flux:subheading size="lg" class="mt-1">{{ __('Kelola data sub kriteria.') }}
            </flux:subheading>
        </div>
        <div class="flex flex-col sm:flex-row gap-2">
            <flux:button wire:click="toggleMatriksutama" variant="outline"
                :icon="$showMatriksutama ? 'x-mark' : 'table-cells'">
                {{ $showMatriksutama ? __('Tutup Perbandingan') : __('Perbandingan Kriteria') }}
            </flux:button>
        </div>
    </header>

    <div class="space-y-8">
        <flux:card class="p-4 bg-zinc-50/50 dark:bg-white/5">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass"
                placeholder="Cari nama sub kriteria..." class="max-w-md" clearable />
        </flux:card>

        <div class="space-y-6">
            @foreach ($kriterias as $kriteria)
                <flux:card id="kriteria-{{ $kriteria->id }}" class="p-0 overflow-hidden scroll-mt-24">
                    <div
                        class="flex items-center justify-between p-4 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-white/5">
                        <div class="flex items-center gap-3">
                            <span
                                class="flex items-center justify-center px-2 py-1 rounded bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 font-bold text-xs border border-indigo-200 dark:border-indigo-800">
                                {{ $kriteria->kode }}
                            </span>
                            <flux:heading size="md" class="!mb-0">{{ $kriteria->nama }}</flux:heading>
                        </div>

                        <flux:modal.trigger name="tambah-sub-kriteria">
                            <flux:button variant="ghost" size="sm" icon="plus"
                                class="text-indigo-600 hover:text-indigo-700 hover:bg-indigo-50 dark:hover:bg-indigo-900/30"
                                wire:click="$dispatch('set-kriteria-id', { id: {{ $kriteria->id }} })">
                                {{ __('Tambah Sub') }}
                            </flux:button>
                        </flux:modal.trigger>
                    </div>

                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column class="w-10 !pl-6">No</flux:table.column>
                            <flux:table.column>Nama Sub Kriteria</flux:table.column>
                            <flux:table.column class="text-right">Aksi</flux:table.column>
                        </flux:table.columns>

                        <flux:table.rows>
                            @forelse ($kriteria->subKriteria as $sub)
                                <flux:table.row :key="$sub->id">
                                    <flux:table.cell class="!pl-6 text-zinc-500">
                                        {{ $loop->iteration }}
                                    </flux:table.cell>

                                    <flux:table.cell class="font-medium text-zinc-900 dark:text-white">
                                        {{ $sub->nama }}
                                    </flux:table.cell>

                                    <flux:table.cell class="text-right">
                                        <div class="flex justify-start gap-2 pr-6">
                                            <flux:modal.trigger name="edit-sub-kriteria">
                                                <flux:button variant="ghost" size="sm" icon="pencil-square"
                                                    class="text-amber-500 hover:text-amber-600"
                                                    wire:click="$dispatch('edit-sub-kriteria', { subKriteria: {{ $sub->id }} })" />
                                            </flux:modal.trigger>

                                            <flux:button variant="ghost" size="sm" icon="trash"
                                                class="text-red-500 hover:text-red-600" wire:click="delete({{ $sub->id }})"
                                                wire:confirm="Hapus sub kriteria [{{ $sub->nama }}]?" />
                                        </div>
                                    </flux:table.cell>
                                </flux:table.row>
                            @empty
                                <flux:table.row>
                                    <flux:table.cell colspan="3" class="text-center py-6 text-zinc-400 italic text-sm">
                                        Belum ada data sub kriteria.
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforelse
                        </flux:table.rows>
                    </flux:table>
                </flux:card>
            @endforeach
        </div>
        @if ($showMatriksutama)
            <flux:heading size="sm" level="1" class="flex items-center gap-2 mb-2">
                {{ __('Data Perbandingan Sub Kriteria') }}
            </flux:heading>
            <div class="flex w-full gap-2">
                @foreach ($kriterias as $kriteria)
                    <flux:button wire:click="toggleKriteria({{ $kriteria->id }})"
                        variant="{{ $activeKriteria === $kriteria->id ? 'primary' : 'outline' }}"
                        class="flex-1 text-center py-3">
                        {{ $activeKriteria === $kriteria->id
                ? 'Tutup Kriteria': $kriteria->nama }}
                    </flux:button>
                @endforeach
            </div>

            {{-- CONTENT --}}
            <div class="mt-6">
                @if ($activeKriteria)
                    @php
                        $selected = $kriterias->firstWhere('id', $activeKriteria);
                    @endphp
                    <div class="p-5 border rounded-2xl shadow bg-white animate-fade-in">
                        <h3 class="text-lg font-bold mb-4">
                            {{ $selected->nama }}
                        </h3>
                        {{-- 🔥 MASUKKAN MATRKS DI SINI --}}
                        <livewire:pages::subkriteria.matriksutama :kriteriaId="$activeKriteria" :key="$activeKriteria" />
                    </div>
                @endif
            </div>
        @endif
    </div>
    <livewire:pages::subkriteria.create />
    <livewire:pages::subkriteria.edit />

    <x-flash-message />
</div>
