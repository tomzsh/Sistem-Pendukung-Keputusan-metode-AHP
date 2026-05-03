<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Kriteria;

new class extends Component {
    use WithPagination;

    public string $search = '';
    public string $sortField = 'kode';
    public string $sortDirection = 'asc';

    public bool $showMatriksutama = false;

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function toggleMatriksutama()
    {
        $this->showMatriksutama = !$this->showMatriksutama;
    }

    public function toggleMatriks()
    {
        $this->showMatriks = !$this->showMatriks;
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function delete(Kriteria $kriteria)
    {
        $kriteria->delete();
        session()->flash('success', 'Kriteria berhasil dihapus.');
    }

    public function with(): array
    {
        return [
            'kriterias' => Kriteria::query()
                ->where('nama', 'like', '%' . $this->search . '%')
                ->orWhere('kode', 'like', '%' . $this->search . '%')
                ->orderBy($this->sortField, $this->sortDirection)
                ->paginate(10),
        ];
    }
};
?>

<div>
    <header class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <flux:heading size="xl" level="1" class="flex items-center gap-2 !font-serif tracking-normal">
                <flux:icon.clipboard-document-list class="size-6 text-zinc-500" />
                {{ __('Kriteria') }}
            </flux:heading>
            <flux:subheading size="lg" class="mt-1">{{ __('Kelola data kriteria.') }}</flux:subheading>
        </div>

        <div class="flex flex-col sm:flex-row gap-2">
            <flux:button wire:click="toggleMatriksutama" variant="outline"
                :icon="$showMatriksutama ? 'x-mark' : 'table-cells'">
                {{ $showMatriksutama ? __('Tutup Perbandingan') : __('Perbandingan Kriteria') }}
            </flux:button>

            <flux:modal.trigger name="tambah-kriteria">
                <flux:button variant="primary" icon="plus" class="w-full sm:w-auto">
                    {{ __('Tambah Kriteria') }}
                </flux:button>
            </flux:modal.trigger>
        </div>
    </header>

    <flux:card class="p-0 overflow-hidden">
        <div class="p-4 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-white/5">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass"
                placeholder="Cari berdasarkan kode atau nama..." class="max-w-md" clearable />
        </div>

        <flux:table :paginate="$kriterias">
            <flux:table.columns>
                <flux:table.column sortable :sorted="$sortField === 'kode'" :direction="$sortDirection"
                    wire:click="sortBy('kode')" class="w-32 !pl-8 font-semibold">
                    Kode
                </flux:table.column>

                <flux:table.column sortable :sorted="$sortField === 'nama'" :direction="$sortDirection"
                    wire:click="sortBy('nama')" class="font-semibold">
                    Nama Kriteria
                </flux:table.column>

                <flux:table.column class="text-right font-semibold">Aksi</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($kriterias as $kriteria)
                    <flux:table.row :key="$kriteria->id" class="group/row">
                        <flux:table.cell>
                            <div class="ml-4">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-zinc-100 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 border border-zinc-200 dark:border-zinc-700">
                                    {{ $kriteria->kode }}
                                </span>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell class="font-medium text-zinc-900 dark:text-white">
                            {{ $kriteria->nama }}
                        </flux:table.cell>

                        <flux:table.cell class="text-right">
                            <div class="flex justify-start gap-2 pr-6">
                                <flux:modal.trigger name="edit-kriteria">
                                    <flux:button variant="ghost" size="sm" icon="pencil-square"
                                        class="text-zinc-500 hover:text-zinc-900 hover:bg-zinc-100 dark:hover:text-zinc-100 dark:hover:bg-zinc-800"
                                        wire:click="$dispatch('edit-kriteria', { kriteria: {{ $kriteria->id }} })" />
                                </flux:modal.trigger>

                                <flux:button variant="ghost" size="sm" icon="trash"
                                    class="text-zinc-500 hover:text-red-600 hover:bg-red-50 dark:hover:text-red-400 dark:hover:bg-red-500/10"
                                    wire:click="delete({{ $kriteria->id }})"
                                    wire:confirm="Apakah Anda yakin ingin menghapus kriteria [{{ $kriteria->kode }}] {{ $kriteria->nama }}?" />
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="3" class="text-center py-12">
                            <div class="flex flex-col items-center justify-center text-zinc-400">
                                <flux:icon.magnifying-glass class="size-8 mb-2 opacity-20" />
                                <p>Tidak ada data kriteria ditemukan.</p>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>
    @if ($showMatriksutama)
        <div class="mt-8 pt-8 border-t border-zinc-200 dark:border-zinc-800">
            <flux:card size="sm"
                class="bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 p-4 shadow-sm mb-6">
                <flux:heading class="text-sm font-bold mb-2">Informasi Penting</flux:heading>
                <flux:text class="text-zinc-700 dark:text-zinc-300 text-sm leading-relaxed">
                    Setelah mengisi nilai matrik perbandingan, silahkan klik tombol
                    <b>Simpan</b> untuk menyimpan nilai matrik.
                    Selanjutnya, tekan tombol <b>Lihat Hasil</b> untuk melihat hasil apakah nilai CR konsisten atau tidak.
                </flux:text>
            </flux:card>
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-4">
                <div>
                    <flux:heading size="xl" level="1" class="flex items-center gap-2 !font-serif tracking-normal">
                        <flux:icon.clipboard-document-list class="size-6 text-zinc-500" />
                        {{ __('Matriks Perbandingan Berpasangan') }}
                    </flux:heading>
                    <flux:subheading size="lg" class="max-w-2xl">
                        {{ __('Gunakan skala (1-9) untuk membandingkan tingkat kepentingan antar kriteria secara berpasangan.') }}
                    </flux:subheading>
                </div>
                <div
                    class="flex items-center gap-2 text-xs font-medium text-zinc-500 bg-zinc-100 dark:bg-zinc-800 px-3 py-1.5 rounded-full w-fit">
                    <span class="relative flex h-2 w-2">
                        <span
                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                    </span>
                    Mode Input Aktif
                </div>
            </div>

            <livewire:pages::kriteria.matriksutama />
        </div>
    @endif

    <livewire:pages::kriteria.create />
    <livewire:pages::kriteria.edit />

    <x-flash-message />
</div>
