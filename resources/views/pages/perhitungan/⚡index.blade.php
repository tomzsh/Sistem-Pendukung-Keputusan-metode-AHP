<?php

use Livewire\Component;
use App\Models\Kriteria;
use App\Models\Alternatif;
use App\Models\KriteriaHasil;
use App\Models\SubKriteriaHasil;
use App\Models\Penilaian;
use App\Models\Hasil;

new class extends Component {

    public array $bobotKriteria    = [];
    public array $bobotSubKriteria = [];
    public array $penilaianMatrix  = [];
    public array $nilaiMatrix      = [];
    public array $skorMatrix       = [];
    public array $totalSkor        = [];

    public bool  $sudahDihitung    = false;
    public bool  $dataLengkap      = true;
    public array $peringatan       = [];

    public string $exportFormat    = '';
    public bool   $showExportModal = false;

    public function mount(): void
    {
        $this->loadData();
    }

    protected function getKriterias()
    {
        return Kriteria::with(['subKriteria', 'hasil'])->orderBy('kode')->get();
    }

    protected function getAlternatifs()
    {
        return Alternatif::orderBy('nomor')->get();
    }

    public function loadData(): void
    {
        $this->peringatan  = [];
        $this->dataLengkap = true;

        $kriterias   = $this->getKriterias();
        $alternatifs = $this->getAlternatifs();

        $this->bobotKriteria = [];
        foreach ($kriterias as $k) {
            $hasil = KriteriaHasil::where('kriteria_id', $k->id)->first();
            if (!$hasil) {
                $this->peringatan[] = "Bobot kriteria \"{$k->nama}\" belum tersimpan.";
                $this->dataLengkap  = false;
            }
            $this->bobotKriteria[$k->id] = $hasil ? (float) $hasil->nilai : 0;
        }

        $this->bobotSubKriteria = [];
        foreach ($kriterias as $k) {
            $this->bobotSubKriteria[$k->id] = [];
            $subHasils = SubKriteriaHasil::where('id_kriteria', $k->id)->get();
            foreach ($subHasils as $sh) {
                $this->bobotSubKriteria[$k->id][$sh->id_sub_kriteria] = (float) $sh->nilai;
            }
        }

        $this->penilaianMatrix = [];
        $this->nilaiMatrix     = [];

        foreach ($alternatifs as $alt) {
            $penilaians = Penilaian::with('subKriteria')
                ->where('id_alternatif', $alt->id)
                ->get()
                ->keyBy('id_kriteria');

            foreach ($kriterias as $k) {
                $penilaian = $penilaians->get($k->id);

                if (!$penilaian) {
                    $this->penilaianMatrix[$alt->id][$k->id] = '—';
                    $this->nilaiMatrix[$alt->id][$k->id]     = 0;
                    $this->peringatan[] = "Alternatif \"{$alt->nama}\" belum dinilai untuk kriteria \"{$k->nama}\".";
                    $this->dataLengkap  = false;
                    continue;
                }

                $this->penilaianMatrix[$alt->id][$k->id] =
                    $penilaian->subKriteria?->nama ?? '—';

                $this->nilaiMatrix[$alt->id][$k->id] =
                    $this->bobotSubKriteria[$k->id][$penilaian->id_sub_kriteria] ?? 0;
            }
        }

        $this->skorMatrix = [];
        $this->totalSkor  = [];

        foreach ($alternatifs as $alt) {
            $total = 0;
            foreach ($kriterias as $k) {
                $skor = ($this->nilaiMatrix[$alt->id][$k->id] ?? 0)
                      * ($this->bobotKriteria[$k->id] ?? 0);
                $this->skorMatrix[$alt->id][$k->id] = $skor;
                $total += $skor;
            }
            $this->totalSkor[$alt->id] = $total;
        }

        arsort($this->totalSkor);
        $this->sudahDihitung = true;
    }

    public function simpanHasil(): void
    {
        if (!$this->dataLengkap) {
            session()->flash('error', 'Data belum lengkap. Periksa peringatan di bawah.');
            return;
        }

        foreach ($this->getAlternatifs() as $alt) {
            Hasil::updateOrCreate(
                ['id_alternatif' => $alt->id],
                ['nilai'         => $this->totalSkor[$alt->id] ?? 0]
            );
        }

        session()->flash('success', 'Hasil perhitungan berhasil disimpan.');
        $this->loadData();
    }

    public function openExportModal(): void
    {
        $this->showExportModal = true;
        $this->exportFormat    = '';
    }

    public function export()
    {
        if (!$this->dataLengkap) {
            session()->flash('error', 'Data belum lengkap, tidak dapat dieksport.');
            $this->showExportModal = false;
            return null;
        }

        if (empty($this->exportFormat)) {
            return null;
        }

        $this->showExportModal = false;

        return match ($this->exportFormat) {
            'excel' => $this->exportExcel(),
            'pdf'   => $this->exportPdf(),
            'docx'  => $this->exportDocx(),
            default => null,
        };
    }

    public function exportExcel()
    {
        $kriterias   = $this->getKriterias();
        $alternatifs = $this->getAlternatifs();

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\PerhitunganExport(
                $this->bobotKriteria, $this->bobotSubKriteria,
                $this->penilaianMatrix, $this->nilaiMatrix,
                $this->skorMatrix, $this->totalSkor,
                $kriterias, $alternatifs,
            ),
            'perhitungan-ahp-' . now()->format('Ymd-His') . '.xlsx'
        );
    }

    public function exportPdf()
    {
        $kriterias   = $this->getKriterias();
        $alternatifs = $this->getAlternatifs();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.perhitungan-pdf', [
            'kriterias'        => $kriterias,
            'alternatifs'      => $alternatifs,
            'bobotKriteria'    => $this->bobotKriteria,
            'bobotSubKriteria' => $this->bobotSubKriteria,
            'penilaianMatrix'  => $this->penilaianMatrix,
            'nilaiMatrix'      => $this->nilaiMatrix,
            'skorMatrix'       => $this->skorMatrix,
            'totalSkor'        => $this->totalSkor,
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn() => print($pdf->output()),
            'perhitungan-ahp-' . now()->format('Ymd-His') . '.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }

    public function exportDocx()
    {
        $kriterias   = $this->getKriterias();
        $alternatifs = $this->getAlternatifs();

        return (new \App\Exports\PerhitunganWordExport(
            $this->bobotKriteria, $this->bobotSubKriteria,
            $this->penilaianMatrix, $this->nilaiMatrix,
            $this->skorMatrix, $this->totalSkor,
            $kriterias, $alternatifs,
        ))->download();
    }

    public function with(): array
    {
        return [
            'kriterias'   => $this->getKriterias(),
            'alternatifs' => $this->getAlternatifs(),
        ];
    }
};
?>

{{-- ═══════════════════════════════════════════════════════════════════
     STYLES — inline supaya tidak perlu file CSS terpisah
     ═══════════════════════════════════════════════════════════════════ --}}
<style>
    /* Scrollbar tipis yang menyesuaikan tema */
    .overflow-x-auto {
        scrollbar-width: thin;
        scrollbar-color: rgb(203 213 225 / .6) transparent;
    }
    .dark .overflow-x-auto {
        scrollbar-color: rgb(71 85 105 / .6) transparent;
    }
    .overflow-x-auto::-webkit-scrollbar       { height: 4px; }
    .overflow-x-auto::-webkit-scrollbar-track { background: transparent; }
    .overflow-x-auto::-webkit-scrollbar-thumb { background: rgb(203 213 225 / .6); border-radius: 99px; }
    .dark .overflow-x-auto::-webkit-scrollbar-thumb { background: rgb(71 85 105 / .6); }

    /* Highlight baris terbaik */
    @keyframes shimmer-gold {
        0%   { background-position: -200% center; }
        100% { background-position:  200% center; }
    }
    .row-best {
        background: linear-gradient(
            90deg,
            rgb(254 252 232) 0%,
            rgb(255 249 196) 40%,
            rgb(254 252 232) 100%
        ) !important;
        background-size: 200% auto !important;
    }
    .dark .row-best {
        background: linear-gradient(
            90deg,
            rgb(41 35 10 / .5) 0%,
            rgb(58 49 11 / .6) 40%,
            rgb(41 35 10 / .5) 100%
        ) !important;
        background-size: 200% auto !important;
    }
</style>

<div class="space-y-8">

    {{-- ── HEADER ─────────────────────────────────────────────────────── --}}
    <header class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1" class="flex items-center gap-2">
                <flux:icon.calculator class="size-6 text-zinc-400 dark:text-zinc-500" />
                {{ __('Perhitungan AHP') }}
            </flux:heading>
            <flux:subheading size="lg" class="mt-1">
                {{ __('Hasil perhitungan bobot dan perankingan alternatif.') }}
            </flux:subheading>
        </div>

        <div class="flex items-center gap-2">
            {{-- untuk mengaktifkan fitur export, install phpoffice/phpspreadsheet dan maatwebsite/excel --}}
            {{-- <flux:button type="button" wire:click="openExportModal" variant="outline" icon="arrow-up-tray">Export</flux:button> --}}
            <flux:button wire:click="simpanHasil" variant="primary" icon="arrow-down-tray"
                :disabled="!$dataLengkap">
                Simpan Hasil
            </flux:button>
        </div>
    </header>

    {{-- ── MODAL EXPORT ────────────────────────────────────────────────── --}}
    @teleport('body')
        <flux:modal wire:model="showExportModal" name="export-modal" class="md:w-80">
            <div class="space-y-5">
                <div>
                    <flux:heading size="lg">Export Laporan</flux:heading>
                    <flux:text class="mt-1">Pilih format file yang diinginkan.</flux:text>
                </div>

                <div class="space-y-3">
                    {{-- Excel --}}
                    <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer transition
                                  hover:border-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/30 dark:hover:border-emerald-700
                                  {{ $exportFormat === 'excel'
                                        ? 'border-emerald-500 bg-emerald-50 ring-1 ring-emerald-400 dark:bg-emerald-950/40 dark:border-emerald-600'
                                        : 'border-zinc-200 dark:border-zinc-700' }}">
                        <input type="radio" wire:model.live="exportFormat" value="excel" class="sr-only">
                        <div class="flex items-center justify-center size-9 rounded-lg bg-emerald-100 dark:bg-emerald-900/50">
                            <flux:icon.table-cells class="size-5 text-emerald-700 dark:text-emerald-400" />
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">Excel (.xlsx)</p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">6 sheet terpisah per tabel</p>
                        </div>
                        @if ($exportFormat === 'excel')
                            <flux:icon.check-circle class="size-5 text-emerald-600 dark:text-emerald-400 shrink-0" />
                        @endif
                    </label>

                    {{-- PDF --}}
                    <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer transition
                                  hover:border-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 dark:hover:border-red-700
                                  {{ $exportFormat === 'pdf'
                                        ? 'border-red-500 bg-red-50 ring-1 ring-red-400 dark:bg-red-950/40 dark:border-red-600'
                                        : 'border-zinc-200 dark:border-zinc-700' }}">
                        <input type="radio" wire:model.live="exportFormat" value="pdf" class="sr-only">
                        <div class="flex items-center justify-center size-9 rounded-lg bg-red-100 dark:bg-red-900/50">
                            <flux:icon.document-text class="size-5 text-red-700 dark:text-red-400" />
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">PDF (.pdf)</p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Landscape, siap cetak</p>
                        </div>
                        @if ($exportFormat === 'pdf')
                            <flux:icon.check-circle class="size-5 text-red-500 dark:text-red-400 shrink-0" />
                        @endif
                    </label>

                    {{-- Word --}}
                    <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer transition
                                  hover:border-blue-400 hover:bg-blue-50 dark:hover:bg-blue-950/30 dark:hover:border-blue-700
                                  {{ $exportFormat === 'docx'
                                        ? 'border-blue-500 bg-blue-50 ring-1 ring-blue-400 dark:bg-blue-950/40 dark:border-blue-600'
                                        : 'border-zinc-200 dark:border-zinc-700' }}">
                        <input type="radio" wire:model.live="exportFormat" value="docx" class="sr-only">
                        <div class="flex items-center justify-center size-9 rounded-lg bg-blue-100 dark:bg-blue-900/50">
                            <flux:icon.document class="size-5 text-blue-700 dark:text-blue-400" />
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">Word (.docx)</p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Dokumen siap edit</p>
                        </div>
                        @if ($exportFormat === 'docx')
                            <flux:icon.check-circle class="size-5 text-blue-500 dark:text-blue-400 shrink-0" />
                        @endif
                    </label>
                </div>

                <div class="flex justify-end gap-2 pt-1">
                    <flux:modal.close>
                        <flux:button type="button" variant="ghost">Batal</flux:button>
                    </flux:modal.close>
                    <flux:button type="button" wire:click="export" variant="primary"
                        icon="arrow-down-tray" :disabled="$exportFormat === ''">
                        Download
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    @endteleport

    {{-- ── FLASH MESSAGES ──────────────────────────────────────────────── --}}
    @if (session('success'))
        <div class="px-4 py-3 text-sm font-medium
                    text-emerald-700 bg-emerald-50 border border-emerald-200
                    dark:text-emerald-300 dark:bg-emerald-950/40 dark:border-emerald-800
                    rounded-lg flex items-center gap-2">
            <flux:icon.check-circle class="size-4 shrink-0" />
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="px-4 py-3 text-sm font-medium
                    text-red-700 bg-red-50 border border-red-200
                    dark:text-red-300 dark:bg-red-950/40 dark:border-red-800
                    rounded-lg flex items-center gap-2">
            <flux:icon.x-circle class="size-4 shrink-0" />
            {{ session('error') }}
        </div>
    @endif

    {{-- ── PERINGATAN ───────────────────────────────────────────────────── --}}
    @if (!$dataLengkap && count($peringatan))
        <div class="px-4 py-4
                    bg-amber-50 border border-amber-200
                    dark:bg-amber-950/30 dark:border-amber-800
                    rounded-lg">
            <p class="text-sm font-semibold
                       text-amber-700 dark:text-amber-400
                       flex items-center gap-2">
                <flux:icon.exclamation-triangle class="size-4 shrink-0" />
                Data belum lengkap — perhitungan tidak dapat disimpan:
            </p>
            <ul class="mt-2 space-y-0.5 list-disc list-inside">
                @foreach (array_unique($peringatan) as $warn)
                    <li class="text-xs text-amber-600 dark:text-amber-400/80">{{ $warn }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════════════
         SECTION 1 — BOBOT KRITERIA
         ══════════════════════════════════════════════════════════════════ --}}
    <x-section-divider label="1. Bobot Prioritas Kriteria" />

    <flux:card class="p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-200 dark:border-zinc-700
                                bg-zinc-50 dark:bg-zinc-800/60">
                        <th class="px-4 py-3 text-center w-12
                                   text-xs font-semibold uppercase tracking-wider
                                   text-zinc-500 dark:text-zinc-400">#</th>
                        <th class="px-4 py-3 text-left
                                   text-xs font-semibold uppercase tracking-wider
                                   text-zinc-500 dark:text-zinc-400">Kode</th>
                        <th class="px-4 py-3 text-left
                                   text-xs font-semibold uppercase tracking-wider
                                   text-zinc-500 dark:text-zinc-400">Nama Kriteria</th>
                        <th class="px-4 py-3 text-center
                                   text-xs font-semibold uppercase tracking-wider
                                   text-zinc-500 dark:text-zinc-400">Bobot Prioritas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($kriterias as $i => $k)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors">
                            <td class="px-4 py-3 text-center text-sm text-zinc-400 dark:text-zinc-500">
                                {{ $i + 1 }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold
                                             bg-zinc-100 text-zinc-600 border border-zinc-200
                                             dark:bg-zinc-700 dark:text-zinc-300 dark:border-zinc-600">
                                    {{ $k->kode }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-medium text-zinc-800 dark:text-zinc-100">
                                {{ $k->nama }}
                            </td>
                            <td class="px-4 py-3 text-center font-mono font-semibold
                                       {{ ($bobotKriteria[$k->id] ?? 0) > 0
                                            ? 'text-zinc-700 dark:text-zinc-200'
                                            : 'text-red-500 dark:text-red-400' }}">
                                @if (($bobotKriteria[$k->id] ?? 0) > 0)
                                    {{ number_format($bobotKriteria[$k->id], 6) }}
                                @else
                                    <span class="italic text-xs">Belum ada</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-10 text-center text-sm italic text-zinc-400 dark:text-zinc-500">
                                Belum ada data kriteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if ($kriterias->isNotEmpty())
                    <tfoot>
                        <tr class="border-t border-zinc-200 dark:border-zinc-700
                                    bg-zinc-50 dark:bg-zinc-800/60">
                            <td colspan="3" class="px-4 py-3 text-right text-sm font-semibold
                                                    text-zinc-500 dark:text-zinc-400">
                                Total Bobot
                            </td>
                            <td class="px-4 py-3 text-center font-mono font-bold
                                        text-zinc-700 dark:text-zinc-200">
                                {{ number_format(array_sum($bobotKriteria), 6) }}
                            </td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </flux:card>

    {{-- ══════════════════════════════════════════════════════════════════
         SECTION 2 — BOBOT SUB KRITERIA
         ══════════════════════════════════════════════════════════════════ --}}
    <x-section-divider label="2. Bobot Prioritas Sub Kriteria" />

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @forelse ($kriterias as $k)
            <flux:card class="p-0 overflow-hidden">
                {{-- Card header --}}
                <div class="px-4 py-3 flex items-center gap-2
                             border-b border-zinc-100 dark:border-zinc-700
                             bg-zinc-50/60 dark:bg-zinc-800/50">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold
                                 bg-cyan-50 text-cyan-700 border border-cyan-200
                                 dark:bg-cyan-950/50 dark:text-cyan-300 dark:border-cyan-800">
                        {{ $k->kode }}
                    </span>
                    <span class="font-semibold text-sm text-zinc-800 dark:text-zinc-100">
                        {{ $k->nama }}
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-zinc-100 dark:border-zinc-700
                                        bg-zinc-50 dark:bg-zinc-800/40">
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider
                                            text-zinc-500 dark:text-zinc-400">Sub Kriteria</th>
                                <th class="px-4 py-2 text-center text-xs font-semibold uppercase tracking-wider
                                            text-zinc-500 dark:text-zinc-400">Bobot</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @forelse ($k->subKriteria as $sub)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors">
                                    <td class="px-4 py-2.5 font-medium text-zinc-700 dark:text-zinc-200">
                                        {{ $sub->nama }}
                                    </td>
                                    <td class="px-4 py-2.5 text-center font-mono
                                               {{ isset($bobotSubKriteria[$k->id][$sub->id])
                                                    ? 'font-semibold text-cyan-600 dark:text-cyan-400'
                                                    : 'italic text-xs text-red-500 dark:text-red-400' }}">
                                        {{ isset($bobotSubKriteria[$k->id][$sub->id])
                                            ? number_format($bobotSubKriteria[$k->id][$sub->id], 6)
                                            : 'Belum ada' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="px-4 py-5 text-center text-xs italic text-zinc-400 dark:text-zinc-500">
                                        Belum ada sub kriteria.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if ($k->subKriteria->isNotEmpty())
                            <tfoot>
                                <tr class="border-t border-zinc-100 dark:border-zinc-700
                                            bg-zinc-50 dark:bg-zinc-800/40">
                                    <td class="px-4 py-2 text-right text-xs font-semibold text-zinc-500 dark:text-zinc-400">
                                        Total
                                    </td>
                                    <td class="px-4 py-2 text-center font-mono font-bold text-zinc-700 dark:text-zinc-200">
                                        {{ number_format(array_sum($bobotSubKriteria[$k->id] ?? []), 6) }}
                                    </td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </flux:card>
        @empty
            <div class="col-span-2 px-4 py-10 text-center text-sm italic text-zinc-400 dark:text-zinc-500">
                Belum ada data kriteria.
            </div>
        @endforelse
    </div>

    {{-- ══════════════════════════════════════════════════════════════════
         SECTION 3 — DATA PENILAIAN ALTERNATIF
         ══════════════════════════════════════════════════════════════════ --}}
    <x-section-divider label="3. Data Penilaian Alternatif" />

    <flux:card class="p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-200 dark:border-zinc-700
                                bg-zinc-50 dark:bg-zinc-800/60">
                        <th class="px-4 py-3 text-center w-12
                                   text-xs font-semibold uppercase tracking-wider
                                   text-zinc-500 dark:text-zinc-400">#</th>
                        <th class="px-4 py-3 text-left whitespace-nowrap
                                   text-xs font-semibold uppercase tracking-wider
                                   text-zinc-500 dark:text-zinc-400">Nama Alternatif</th>
                        @foreach ($kriterias as $k)
                            <th class="px-3 py-3 text-center whitespace-nowrap
                                        text-xs font-semibold uppercase tracking-wider
                                        text-zinc-500 dark:text-zinc-400">
                                {{ $k->nama }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($alternatifs as $i => $alt)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors">
                            <td class="px-4 py-3 text-center text-sm text-zinc-400 dark:text-zinc-500">
                                {{ $i + 1 }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5">
                                    <span class="text-xs font-mono text-zinc-400 dark:text-zinc-500">
                                        A{{ $alt->nomor }}
                                    </span>
                                    <span class="font-medium text-zinc-800 dark:text-zinc-100">
                                        {{ $alt->nama }}
                                    </span>
                                </span>
                            </td>
                            @foreach ($kriterias as $k)
                                <td class="px-3 py-3 text-center whitespace-nowrap">
                                    @php $nama = $penilaianMatrix[$alt->id][$k->id] ?? '—'; @endphp
                                    @if ($nama === '—')
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs italic
                                                     text-red-500 bg-red-50 dark:text-red-400 dark:bg-red-950/30">
                                            Belum dinilai
                                        </span>
                                    @else
                                        <span class="text-zinc-700 dark:text-zinc-200">{{ $nama }}</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 2 + $kriterias->count() }}"
                                class="px-4 py-10 text-center text-sm italic text-zinc-400 dark:text-zinc-500">
                                Belum ada data alternatif.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </flux:card>

    {{-- ══════════════════════════════════════════════════════════════════
         SECTION 4 — MATRIKS KEPUTUSAN (X)
         ══════════════════════════════════════════════════════════════════ --}}
    <x-section-divider label="4. Matriks Keputusan (X)" />

    <flux:card class="p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-200 dark:border-zinc-700
                                bg-zinc-50 dark:bg-zinc-800/60">
                        <th class="px-4 py-3 text-center w-12
                                   text-xs font-semibold uppercase tracking-wider
                                   text-zinc-500 dark:text-zinc-400">#</th>
                        <th class="px-4 py-3 text-left whitespace-nowrap
                                   text-xs font-semibold uppercase tracking-wider
                                   text-zinc-500 dark:text-zinc-400">Nama Alternatif</th>
                        @foreach ($kriterias as $k)
                            <th class="px-3 py-3 text-center whitespace-nowrap
                                        text-xs font-semibold uppercase tracking-wider
                                        text-zinc-500 dark:text-zinc-400">
                                {{ $k->nama }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($alternatifs as $i => $alt)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors">
                            <td class="px-4 py-3 text-center text-sm text-zinc-400 dark:text-zinc-500">
                                {{ $i + 1 }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5">
                                    <span class="text-xs font-mono text-zinc-400 dark:text-zinc-500">
                                        A{{ $alt->nomor }}
                                    </span>
                                    <span class="font-medium text-zinc-800 dark:text-zinc-100">
                                        {{ $alt->nama }}
                                    </span>
                                </span>
                            </td>
                            @foreach ($kriterias as $k)
                                @php $v = $nilaiMatrix[$alt->id][$k->id] ?? 0; @endphp
                                <td class="px-3 py-3 text-center font-mono
                                           {{ $v > 0
                                                ? 'text-zinc-700 dark:text-zinc-200'
                                                : 'text-red-500 dark:text-red-400' }}">
                                    {{ $v > 0 ? number_format($v, 6) : '0' }}
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 2 + $kriterias->count() }}"
                                class="px-4 py-10 text-center text-sm italic text-zinc-400 dark:text-zinc-500">
                                Belum ada data alternatif.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </flux:card>

    {{-- ══════════════════════════════════════════════════════════════════
         SECTION 5 — PERHITUNGAN NILAI ATRIBUT
         ══════════════════════════════════════════════════════════════════ --}}
    <x-section-divider label="5. Perhitungan Nilai Atribut (X × Bobot Kriteria)" />

    <flux:card class="p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-200 dark:border-zinc-700
                                bg-zinc-50 dark:bg-zinc-800/60">
                        <th class="px-4 py-3 text-center w-12
                                   text-xs font-semibold uppercase tracking-wider
                                   text-zinc-500 dark:text-zinc-400">#</th>
                        <th class="px-4 py-3 text-left whitespace-nowrap
                                   text-xs font-semibold uppercase tracking-wider
                                   text-zinc-500 dark:text-zinc-400">Nama Alternatif</th>
                        @foreach ($kriterias as $k)
                            <th class="px-3 py-3 text-center whitespace-nowrap
                                        text-xs font-semibold uppercase tracking-wider
                                        text-zinc-500 dark:text-zinc-400">
                                <div>{{ $k->nama }}</div>
                                <div class="mt-0.5 text-[10px] font-normal normal-case
                                             text-zinc-400 dark:text-zinc-500">
                                    ×{{ number_format($bobotKriteria[$k->id] ?? 0, 4) }}
                                </div>
                            </th>
                        @endforeach
                        <th class="px-4 py-3 text-center whitespace-nowrap
                                    text-xs font-semibold uppercase tracking-wider
                                    text-zinc-500 dark:text-zinc-400">Total Skor</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($alternatifs as $i => $alt)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors">
                            <td class="px-4 py-3 text-center text-sm text-zinc-400 dark:text-zinc-500">
                                {{ $i + 1 }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5">
                                    <span class="text-xs font-mono text-zinc-400 dark:text-zinc-500">
                                        A{{ $alt->nomor }}
                                    </span>
                                    <span class="font-medium text-zinc-800 dark:text-zinc-100">
                                        {{ $alt->nama }}
                                    </span>
                                </span>
                            </td>
                            @foreach ($kriterias as $k)
                                <td class="px-3 py-3 text-center font-mono text-xs
                                           text-zinc-600 dark:text-zinc-300">
                                    {{ number_format($skorMatrix[$alt->id][$k->id] ?? 0, 6) }}
                                </td>
                            @endforeach
                            <td class="px-4 py-3 text-center font-mono font-bold
                                        text-zinc-800 dark:text-zinc-100
                                        bg-zinc-50 dark:bg-zinc-800/50
                                        border-l border-zinc-100 dark:border-zinc-700">
                                {{ number_format($totalSkor[$alt->id] ?? 0, 6) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 3 + $kriterias->count() }}"
                                class="px-4 py-10 text-center text-sm italic text-zinc-400 dark:text-zinc-500">
                                Belum ada data alternatif.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </flux:card>

    {{-- ══════════════════════════════════════════════════════════════════
         SECTION 6 — RANKING AKHIR
         ══════════════════════════════════════════════════════════════════ --}}
    <x-section-divider label="6. Ranking Akhir Alternatif" />

    <flux:card class="p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-200 dark:border-zinc-700
                                bg-zinc-50 dark:bg-zinc-800/60">
                        <th class="px-4 py-3 text-center w-16
                                   text-xs font-semibold uppercase tracking-wider
                                   text-zinc-500 dark:text-zinc-400">Rank</th>
                        <th class="px-4 py-3 text-left
                                   text-xs font-semibold uppercase tracking-wider
                                   text-zinc-500 dark:text-zinc-400">Nama Alternatif</th>
                        <th class="px-4 py-3 text-center
                                   text-xs font-semibold uppercase tracking-wider
                                   text-zinc-500 dark:text-zinc-400">Total Skor</th>
                        <th class="px-4 py-3 text-center
                                   text-xs font-semibold uppercase tracking-wider
                                   text-zinc-500 dark:text-zinc-400">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @php $rank = 1; @endphp
                    @forelse ($totalSkor as $altId => $skor)
                        @php $alt = $alternatifs->firstWhere('id', $altId); @endphp
                        @if ($alt)
                            <tr class="{{ $rank === 1 ? 'row-best' : 'hover:bg-zinc-50 dark:hover:bg-zinc-800/40' }} transition-colors">

                                {{-- Rank Badge --}}
                                <td class="px-4 py-3.5 text-center">
                                    @if ($rank === 1)
                                        <span class="inline-flex items-center justify-center size-8 rounded-full
                                                     bg-amber-400 dark:bg-amber-500 text-white font-black text-sm shadow-md">
                                            1
                                        </span>
                                    @elseif ($rank === 2)
                                        <span class="inline-flex items-center justify-center size-8 rounded-full
                                                     bg-zinc-300 dark:bg-zinc-600 text-zinc-700 dark:text-zinc-200 font-bold text-sm">
                                            2
                                        </span>
                                    @elseif ($rank === 3)
                                        <span class="inline-flex items-center justify-center size-8 rounded-full
                                                     bg-amber-600 dark:bg-amber-700 text-white font-bold text-sm">
                                            3
                                        </span>
                                    @else
                                        <span class="text-zinc-500 dark:text-zinc-400 font-semibold">{{ $rank }}</span>
                                    @endif
                                </td>

                                {{-- Nama --}}
                                <td class="px-4 py-3.5">
                                    <span class="inline-flex items-center gap-1.5">
                                        <span class="text-xs font-mono text-zinc-400 dark:text-zinc-500">
                                            A{{ $alt->nomor }}
                                        </span>
                                        <span class="font-semibold text-zinc-800 dark:text-zinc-100
                                                     {{ $rank === 1 ? 'text-amber-800 dark:text-amber-300' : '' }}">
                                            {{ $alt->nama }}
                                        </span>
                                    </span>
                                </td>

                                {{-- Skor --}}
                                <td class="px-4 py-3.5 text-center font-mono font-bold
                                           {{ $rank === 1
                                                ? 'text-amber-700 dark:text-amber-400'
                                                : 'text-zinc-700 dark:text-zinc-200' }}">
                                    {{ number_format($skor, 6) }}
                                </td>

                                {{-- Label --}}
                                <td class="px-4 py-3.5 text-center">
                                    @if ($rank === 1)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold
                                                     bg-amber-100 text-amber-700 border border-amber-200
                                                     dark:bg-amber-950/50 dark:text-amber-300 dark:border-amber-800">
                                            <flux:icon.trophy class="size-3.5" />
                                            Terbaik
                                        </span>
                                    @elseif ($rank <= 3)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold
                                                     bg-zinc-100 text-zinc-600 border border-zinc-200
                                                     dark:bg-zinc-800 dark:text-zinc-300 dark:border-zinc-700">
                                            <flux:icon.check-circle class="size-3.5" />
                                            Top {{ $rank }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                                                     bg-zinc-50 text-zinc-500 border border-zinc-200
                                                     dark:bg-zinc-800/50 dark:text-zinc-400 dark:border-zinc-700">
                                            Rank {{ $rank }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @php $rank++; @endphp
                        @endif
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-10 text-center text-sm italic text-zinc-400 dark:text-zinc-500">
                                Belum ada data untuk diranking.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </flux:card>

    {{-- ── TOMBOL SIMPAN BAWAH ─────────────────────────────────────────── --}}
    @if ($alternatifs->isNotEmpty() && $dataLengkap)
        <div class="flex justify-end pt-2 pb-4">
            <flux:button wire:click="simpanHasil" variant="primary" icon="arrow-down-tray" size="sm">
                {{ __('Simpan Hasil ke Database') }}
            </flux:button>
        </div>
    @endif

</div>
