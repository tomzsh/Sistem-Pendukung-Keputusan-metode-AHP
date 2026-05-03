<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Alternatif;
use App\Models\Kriteria;

new class extends Component {
    use WithPagination;

    public string $search        = '';
    public string $sortField     = 'nomor';
    public string $sortDirection = 'asc';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField     = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function deletePenilaian(int $alternatifId): void
    {
        \App\Models\Penilaian::where('id_alternatif', $alternatifId)->delete();
        session()->flash('success', 'Penilaian alternatif berhasil dihapus.');
    }

    public function with(): array
    {
        return [
            'alternatifs'   => Alternatif::query()
                ->withCount('penilaian')
                ->where('nama', 'like', '%' . $this->search . '%')
                ->orderBy($this->sortField, $this->sortDirection)
                ->paginate(10),
            'totalKriteria' => Kriteria::count(),
        ];
    }
};
?>

<div>
    <header class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <flux:heading size="xl" level="1" class="flex items-center gap-2">
                <flux:icon.clipboard-document-check class="size-6 text-zinc-500" />
                {{ __('Penilaian Alternatif') }}
            </flux:heading>
            <flux:subheading size="lg" class="mt-1">
                {{ __('Tetapkan sub kriteria untuk setiap alternatif.') }}
            </flux:subheading>
        </div>
    </header>

    {{-- Flash --}}
    @if (session('success'))
        <div class="mb-4 px-4 py-3 text-sm text-green-700 bg-green-100 border border-green-300 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    {{-- Peringatan jika belum ada kriteria / sub kriteria --}}
    @if ($totalKriteria === 0)
        <div class="mb-6 px-4 py-4 text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-lg flex items-start gap-3">
            <flux:icon.exclamation-triangle class="size-5 mt-0.5 shrink-0" />
            <div>
                <p class="font-semibold">Data kriteria belum tersedia.</p>
                <p class="mt-1 text-amber-600">Tambahkan kriteria dan sub kriteria terlebih dahulu sebelum melakukan penilaian.</p>
            </div>
        </div>
    @endif

    <flux:card class="p-0 overflow-hidden">
        <div class="p-4 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-white/5">
            <flux:input
                wire:model.live.debounce.300ms="search"
                icon="magnifying-glass"
                placeholder="Cari nama alternatif..."
                class="max-w-md"
                clearable />
        </div>

        <flux:table :paginate="$alternatifs">
            <flux:table.columns>
                <flux:table.column
                    sortable
                    :sorted="$sortField === 'nomor'"
                    :direction="$sortDirection"
                    wire:click="sortBy('nomor')"
                    class="w-24 !pl-8 font-semibold">
                    No
                </flux:table.column>

                <flux:table.column
                    sortable
                    :sorted="$sortField === 'nama'"
                    :direction="$sortDirection"
                    wire:click="sortBy('nama')"
                    class="font-semibold">
                    Nama Alternatif
                </flux:table.column>

                <flux:table.column class="font-semibold text-center">
                    Status Penilaian
                </flux:table.column>

                <flux:table.column class="text-right font-semibold">
                    Aksi
                </flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($alternatifs as $alternatif)
                    <flux:table.row :key="$alternatif->id">

                        {{-- Nomor --}}
                        <flux:table.cell>
                            <div class="ml-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium
                                             bg-zinc-100 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200
                                             border border-zinc-200 dark:border-zinc-700">
                                    A{{ $alternatif->nomor }}
                                </span>
                            </div>
                        </flux:table.cell>

                        {{-- Nama --}}
                        <flux:table.cell class="font-medium text-zinc-900 dark:text-white">
                            {{ $alternatif->nama }}
                        </flux:table.cell>

                        {{-- Status --}}
                        <flux:table.cell class="text-center">
                            @if ($totalKriteria === 0)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium
                                             bg-zinc-100 text-zinc-500">
                                    —
                                </span>
                            @elseif ($alternatif->penilaian_count === 0)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium
                                             bg-red-100 text-red-600 border border-red-200">
                                    <span class="size-1.5 rounded-full bg-red-500"></span>
                                    Belum Dinilai
                                </span>
                            @elseif ($alternatif->penilaian_count < $totalKriteria)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium
                                             bg-amber-100 text-amber-600 border border-amber-200">
                                    <span class="size-1.5 rounded-full bg-amber-500"></span>
                                    {{ $alternatif->penilaian_count }}/{{ $totalKriteria }} Kriteria
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium
                                             bg-green-100 text-green-700 border border-green-200">
                                    <span class="size-1.5 rounded-full bg-green-500"></span>
                                    Lengkap
                                </span>
                            @endif
                        </flux:table.cell>

                        {{-- Aksi --}}
                        <flux:table.cell class="text-right">
                            <div class="flex justify-start items-center gap-2 pr-6">

                                {{-- Tombol Nilai (jika belum ada penilaian) --}}
                                @if ($alternatif->penilaian_count === 0)
                                    <flux:modal.trigger name="nilai-penilaian-modal">
                                        <flux:button
                                            variant="ghost"
                                            size="sm"
                                            icon="clipboard-document-check"
                                            class="text-indigo-500 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-950/30"
                                            wire:click="$dispatch('nilai-alternatif', { id: {{ $alternatif->id }} })">
                                            Nilai
                                        </flux:button>
                                    </flux:modal.trigger>

                                {{-- Tombol Edit (jika sudah ada penilaian) --}}
                                @else
                                    <flux:modal.trigger name="edit-penilaian-modal">
                                        <flux:button
                                            variant="ghost"
                                            size="sm"
                                            icon="pencil-square"
                                            class="text-amber-500 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-950/30"
                                            wire:click="$dispatch('edit-penilaian', { id: {{ $alternatif->id }} })">
                                            Edit
                                        </flux:button>
                                    </flux:modal.trigger>

                                    {{-- Hapus Penilaian --}}
                                    <flux:button
                                        variant="ghost"
                                        size="sm"
                                        icon="trash"
                                        class="text-red-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/30"
                                        wire:click="deletePenilaian({{ $alternatif->id }})"
                                        wire:confirm="Hapus semua penilaian untuk [A{{ $alternatif->nomor }}] {{ $alternatif->nama }}?" />
                                @endif

                            </div>
                        </flux:table.cell>

                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="4" class="text-center py-12">
                            <div class="flex flex-col items-center justify-center text-zinc-400">
                                <flux:icon.magnifying-glass class="size-8 mb-2 opacity-20" />
                                <p>Tidak ada data alternatif ditemukan.</p>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>

    <livewire:pages::penilaian.nilai />
    <livewire:pages::penilaian.edit />

    <x-flash-message />
</div>
