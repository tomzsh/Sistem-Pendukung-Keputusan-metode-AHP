<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Alternatif;

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

    public function delete(Alternatif $alternatif): void
    {
        $alternatif->delete();
        session()->flash('success', 'Alternatif berhasil dihapus.');
    }

    public function with(): array
    {
        return [
            'alternatifs' => Alternatif::query()
                ->where('nama', 'like', '%' . $this->search . '%')
                ->orWhere('nomor', 'like', '%' . $this->search . '%')
                ->orderBy($this->sortField, $this->sortDirection)
                ->paginate(10),
        ];
    }
};
?>

<div>
    <header class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <flux:heading size="xl" level="1" class="flex items-center gap-2">
                <flux:icon.user-group class="size-6 text-zinc-500" />
                {{ __('Alternatif') }}
            </flux:heading>
            <flux:subheading size="lg" class="mt-1">
                {{ __('Kelola data alternatif pilihan.') }}
            </flux:subheading>
        </div>

        <div class="flex flex-col sm:flex-row gap-2">
            <flux:modal.trigger name="tambah-alternatif">
                <flux:button variant="primary" icon="plus" class="w-full sm:w-auto">
                    {{ __('Tambah Alternatif') }}
                </flux:button>
            </flux:modal.trigger>
        </div>
    </header>

    <flux:card class="p-0 overflow-hidden">
        <div class="p-4 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-white/5">
            <flux:input
                wire:model.live.debounce.300ms="search"
                icon="magnifying-glass"
                placeholder="Cari berdasarkan nomor atau nama..."
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

                <flux:table.column class="text-right font-semibold">
                    Aksi
                </flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($alternatifs as $alternatif)
                    <flux:table.row :key="$alternatif->id" class="group/row">

                        <flux:table.cell>
                            <div class="ml-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium
                                             bg-zinc-100 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200
                                             border border-zinc-200 dark:border-zinc-700">
                                    A{{ $alternatif->nomor }}
                                </span>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell class="font-medium text-zinc-900 dark:text-white">
                            {{ $alternatif->nama }}
                        </flux:table.cell>

                        <flux:table.cell class="text-right">
                            <div class="flex justify-start gap-2 pr-6">

                                {{-- Edit --}}
                                <flux:modal.trigger name="edit-alternatif-modal">
                                    <flux:button
                                        variant="ghost"
                                        size="sm"
                                        icon="pencil-square"
                                        class="text-amber-500 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-950/30"
                                        wire:click="$dispatch('edit-alternatif', { id: {{ $alternatif->id }} })" />
                                </flux:modal.trigger>

                                {{-- Hapus --}}
                                <flux:button
                                    variant="ghost"
                                    size="sm"
                                    icon="trash"
                                    class="text-red-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/30"
                                    wire:click="delete({{ $alternatif->id }})"
                                    wire:confirm="Apakah Anda yakin ingin menghapus alternatif [A{{ $alternatif->nomor }}] {{ $alternatif->nama }}?" />

                            </div>
                        </flux:table.cell>

                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="3" class="text-center py-12">
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

    <livewire:pages::alternatif.create />
    <livewire:pages::alternatif.edit />

    <x-flash-message />
</div>
