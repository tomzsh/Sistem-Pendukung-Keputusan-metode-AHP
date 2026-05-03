<?php

use Livewire\Component;
use App\Models\Kriteria;
use App\Models\Alternatif;
use App\Models\Penilaian;
use App\Models\Hasil;
use App\Models\SubKriteria;
use App\Models\KriteriaNilai;
use App\Models\KriteriaHasil;

new class extends Component {

    public array $stats        = [];
    public array $rankingData  = [];
    public array $bobotData    = [];
    public bool  $hasHasil     = false;
    public bool  $hasBobotKrit = false;
    public float $cr           = 0;
    public float $ci           = 0;
    public bool  $hasCr        = false;

    public function mount(): void
    {
        $this->loadStats();
        $this->loadRankingChart();
        $this->loadBobotChart();
        $this->loadConsistencyRatio();
    }

    protected function loadStats(): void
    {
        $totalAlternatif = Alternatif::count();
        $totalPenilaian  = Penilaian::distinct('id_alternatif')->count('id_alternatif');
        $kelengkapan     = $totalAlternatif > 0
            ? round(($totalPenilaian / $totalAlternatif) * 100)
            : 0;

        $this->stats = [
            'alternatif'   => $totalAlternatif,
            'kriteria'     => Kriteria::count(),
            'sub_kriteria' => SubKriteria::count(),
            'penilaian'    => $totalPenilaian,
            'kelengkapan'  => $kelengkapan,
        ];
    }

    protected function loadRankingChart(): void
    {
        $hasil = Hasil::with('alternatif')
            ->orderByDesc('nilai')
            ->limit(10)
            ->get();

        $this->hasHasil = $hasil->isNotEmpty();

        $this->rankingData = [
            'labels' => $hasil->map(fn($h) => "A{$h->alternatif->nomor} - {$h->alternatif->nama}")->toArray(),
            'values' => $hasil->map(fn($h) => round((float) $h->nilai, 6))->toArray(),
        ];
    }

    protected function loadBobotChart(): void
    {
        $kriterias = Kriteria::with('hasil')->orderBy('kode')->get();
        $labels = [];
        $values = [];

        foreach ($kriterias as $k) {
            if ($k->hasil && (float) $k->hasil->nilai > 0) {
                $labels[] = $k->nama;
                $values[] = round((float) $k->hasil->nilai, 6);
            }
        }

        $this->hasBobotKrit = count($values) > 0;
        $this->bobotData    = ['labels' => $labels, 'values' => $values];
    }

    protected function loadConsistencyRatio(): void
    {
        $kriterias = Kriteria::orderBy('kode')->get();
        $n = $kriterias->count();

        if ($n < 2 || KriteriaNilai::count() === 0) {
            return;
        }

        // Build matrix from saved values
        $matrix = [];
        foreach ($kriterias as $row) {
            foreach ($kriterias as $col) {
                $matrix[$row->id][$col->id] = 1;
            }
        }

        $savedValues = KriteriaNilai::all();
        foreach ($savedValues as $nilai) {
            $dari = $nilai->kriteria_id_dari;
            $tujuan = $nilai->kriteria_id_tujuan;
            $val = (float) $nilai->nilai;

            if ($dari == $tujuan) {
                $matrix[$dari][$tujuan] = 1;
            } elseif ($dari < $tujuan) {
                $matrix[$dari][$tujuan] = $val;
                $matrix[$tujuan][$dari] = $val != 0 ? 1 / $val : 1;
            }
        }

        // Column totals
        $totals = [];
        foreach ($kriterias as $col) {
            $sum = 0;
            foreach ($kriterias as $row) {
                $sum += $matrix[$row->id][$col->id] ?? 1;
            }
            $totals[$col->id] = $sum ?: 1;
        }

        // Normalized + priorities
        $priorities = [];
        foreach ($kriterias as $row) {
            $sumRow = 0;
            foreach ($kriterias as $col) {
                $val = $matrix[$row->id][$col->id] ?? 1;
                $tot = $totals[$col->id] ?? 1;
                $sumRow += $tot != 0 ? $val / $tot : 0;
            }
            $priorities[$row->id] = $sumRow / $n;
        }

        // Weighted sum vector
        $mptbTotals = [];
        foreach ($kriterias as $row) {
            $sum = 0;
            foreach ($kriterias as $col) {
                $sum += ($matrix[$row->id][$col->id] ?? 1) * ($priorities[$col->id] ?? 0);
            }
            $mptbTotals[$row->id] = $sum;
        }

        // Lambda max
        $rk = [];
        foreach ($kriterias as $row) {
            $p = $priorities[$row->id] ?? 0;
            $rk[$row->id] = $p != 0 ? ($mptbTotals[$row->id] ?? 0) / $p : 0;
        }
        $lambdaMax = array_sum($rk) / $n;

        // CI & CR
        $this->ci = $n > 1 ? ($lambdaMax - $n) / ($n - 1) : 0;
        $riTable = [1 => 0, 2 => 0, 3 => 0.58, 4 => 0.90, 5 => 1.12, 6 => 1.24, 7 => 1.32, 8 => 1.41, 9 => 1.45, 10 => 1.49];
        $ri = $riTable[$n] ?? 1.49;
        $this->cr = $ri != 0 ? $this->ci / $ri : 0;
        $this->hasCr = true;
    }
};
?>


{{-- Root element --}}
<div class="space-y-6 p-1">

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3">
        <div>
            <p class="text-xs font-medium uppercase tracking-widest text-cyan-600 dark:text-cyan-400 mb-1">
                Sistem Pendukung Keputusan
            </p>
            <h1 class="text-2xl sm:text-3xl font-bold font-serif text-zinc-900 dark:text-white tracking-normal">
                Dashboard SPK AHP
            </h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                {{ now()->isoFormat('dddd, D MMMM Y') }}
            </p>
        </div>
        <a href="{{ route('perhitungan') }}" wire:navigate
           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-cyan-600 hover:bg-cyan-700
                  text-white text-sm font-semibold transition shadow-sm w-fit">
            <flux:icon.calculator class="size-4" />
            Lihat Perhitungan
        </a>
    </div>

    {{-- STAT CARDS --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">

        {{-- Kriteria --}}
        <div class="rounded-xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wide">Kriteria</p>
                    <p class="text-3xl font-bold text-zinc-900 dark:text-white mt-1 tabular-nums">{{ $stats['kriteria'] }}</p>
                </div>
                <div class="flex items-center justify-center size-9 rounded-lg bg-zinc-100 dark:bg-zinc-700">
                    <flux:icon.clipboard-document-list class="size-5 text-zinc-500 dark:text-zinc-400" />
                </div>
            </div>
            <p class="text-xs text-zinc-400 mt-2">{{ $stats['sub_kriteria'] }} sub kriteria</p>
        </div>

        {{-- Alternatif --}}
        <div class="rounded-xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wide">Alternatif</p>
                    <p class="text-3xl font-bold text-zinc-900 dark:text-white mt-1 tabular-nums">{{ $stats['alternatif'] }}</p>
                </div>
                <div class="flex items-center justify-center size-9 rounded-lg bg-zinc-100 dark:bg-zinc-700">
                    <flux:icon.users class="size-5 text-zinc-500 dark:text-zinc-400" />
                </div>
            </div>
            <p class="text-xs text-zinc-400 mt-2">Data kandidat terdaftar</p>
        </div>

        {{-- Penilaian --}}
        <div class="rounded-xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wide">Dinilai</p>
                    <p class="text-3xl font-bold text-zinc-900 dark:text-white mt-1 tabular-nums">
                        {{ $stats['penilaian'] }}<span class="text-base font-normal text-zinc-400">/ {{ $stats['alternatif'] }}</span>
                    </p>
                </div>
                <div class="flex items-center justify-center size-9 rounded-lg bg-cyan-50 dark:bg-cyan-900/30">
                    <flux:icon.clipboard-document-check class="size-5 text-cyan-600 dark:text-cyan-400" />
                </div>
            </div>
            <div class="mt-2">
                <div class="h-1.5 bg-zinc-100 dark:bg-zinc-700 rounded-full overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-700 {{ $stats['kelengkapan'] === 100 ? 'bg-cyan-600' : 'bg-cyan-400' }}"
                         style="width: {{ $stats['kelengkapan'] }}%"></div>
                </div>
                <p class="text-xs text-zinc-400 mt-1">{{ $stats['kelengkapan'] }}% lengkap</p>
            </div>
        </div>

        {{-- CR --}}
        <div class="rounded-xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wide">CR</p>
                    @if ($hasCr)
                        <p class="text-3xl font-bold mt-1 tabular-nums font-mono {{ $cr < 0.1 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                            {{ number_format($cr, 4) }}
                        </p>
                    @else
                        <p class="text-3xl font-bold text-zinc-300 dark:text-zinc-600 mt-1">—</p>
                    @endif
                </div>
                <div class="flex items-center justify-center size-9 rounded-lg {{ $hasCr && $cr < 0.1 ? 'bg-emerald-50 dark:bg-emerald-900/30' : ($hasCr ? 'bg-red-50 dark:bg-red-900/30' : 'bg-zinc-100 dark:bg-zinc-700') }}">
                    @if ($hasCr && $cr < 0.1)
                        <flux:icon.check-circle class="size-5 text-emerald-600 dark:text-emerald-400" />
                    @elseif ($hasCr)
                        <flux:icon.exclamation-triangle class="size-5 text-red-600 dark:text-red-400" />
                    @else
                        <flux:icon.question-mark-circle class="size-5 text-zinc-400" />
                    @endif
                </div>
            </div>
            <p class="text-xs mt-2 {{ $hasCr && $cr < 0.1 ? 'text-emerald-600 dark:text-emerald-400' : ($hasCr ? 'text-red-500' : 'text-zinc-400') }}">
                {{ $hasCr ? ($cr < 0.1 ? 'Konsisten (< 0.1)' : 'Tidak konsisten (≥ 0.1)') : 'Belum dihitung' }}
            </p>
        </div>

        {{-- Hasil Ranking --}}
        <div class="rounded-xl p-5 shadow-sm border
                    {{ $hasHasil
                        ? 'bg-cyan-600 dark:bg-cyan-700 border-cyan-500'
                        : 'bg-white dark:bg-zinc-800 border-zinc-200 dark:border-zinc-700' }}">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide {{ $hasHasil ? 'text-cyan-100' : 'text-zinc-500 dark:text-zinc-400' }}">Ranking</p>
                    <p class="text-3xl font-bold mt-1 {{ $hasHasil ? 'text-white' : 'text-zinc-300 dark:text-zinc-600' }}">
                        {{ $hasHasil ? '✓' : '—' }}
                    </p>
                </div>
                <div class="flex items-center justify-center size-9 rounded-lg {{ $hasHasil ? 'bg-white/20' : 'bg-zinc-100 dark:bg-zinc-700' }}">
                    <flux:icon.trophy class="size-5 {{ $hasHasil ? 'text-white' : 'text-zinc-400' }}" />
                </div>
            </div>
            <p class="text-xs mt-2 {{ $hasHasil ? 'text-cyan-100' : 'text-zinc-400' }}">
                {{ $hasHasil ? 'Tersimpan' : 'Belum ada hasil' }}
            </p>
        </div>

    </div>

    {{-- CHARTS --}}
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">

        {{-- Ranking Chart --}}
        <div class="lg:col-span-3 rounded-xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-sm font-semibold text-zinc-800 dark:text-white">Ranking Alternatif</h2>
                    <p class="text-xs text-zinc-400 mt-0.5">Total skor akhir AHP</p>
                </div>
                @if ($hasHasil)
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                        <span class="size-1.5 rounded-full bg-emerald-500"></span>
                        Data tersedia
                    </span>
                @endif
            </div>
            @if ($hasHasil)
                <div class="relative h-64">
                    <canvas id="rankingChart"></canvas>
                </div>
            @else
                <div class="flex flex-col items-center justify-center h-64 text-center">
                    <div class="size-14 rounded-xl bg-zinc-50 dark:bg-zinc-700 flex items-center justify-center mb-3">
                        <flux:icon.chart-bar class="size-7 text-zinc-300 dark:text-zinc-500" />
                    </div>
                    <p class="text-sm font-medium text-zinc-400">Belum ada hasil perhitungan</p>
                    <p class="text-xs text-zinc-300 dark:text-zinc-600 mt-1">Simpan hasil di halaman Perhitungan</p>
                    <a href="{{ route('perhitungan') }}" wire:navigate
                       class="mt-3 text-xs font-semibold text-cyan-600 hover:text-cyan-700 underline underline-offset-2">
                        Ke Perhitungan →
                    </a>
                </div>
            @endif
        </div>

        {{-- Bobot Chart --}}
        <div class="lg:col-span-2 rounded-xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 p-5 shadow-sm">
            <div class="mb-4">
                <h2 class="text-sm font-semibold text-zinc-800 dark:text-white">Bobot Kriteria</h2>
                <p class="text-xs text-zinc-400 mt-0.5">Distribusi prioritas</p>
            </div>
            @if ($hasBobotKrit)
                <div class="relative h-44">
                    <canvas id="bobotChart"></canvas>
                </div>
                <div class="mt-3 space-y-1.5" id="bobotLegend"></div>
            @else
                <div class="flex flex-col items-center justify-center h-48 text-center">
                    <div class="size-12 rounded-xl bg-zinc-50 dark:bg-zinc-700 flex items-center justify-center mb-3">
                        <flux:icon.chart-pie class="size-6 text-zinc-300 dark:text-zinc-500" />
                    </div>
                    <p class="text-sm font-medium text-zinc-400">Bobot belum tersimpan</p>
                    <a href="{{ route('kriteria') }}" wire:navigate
                       class="mt-2 text-xs font-semibold text-cyan-600 hover:text-cyan-700 underline underline-offset-2">
                        Atur Kriteria →
                    </a>
                </div>
            @endif
        </div>
    </div>

    {{-- TOP 3 RANKING --}}
    @if ($hasHasil && count($rankingData['labels']) > 0)
        <div class="rounded-xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 p-5 shadow-sm">
            <h2 class="text-sm font-semibold text-zinc-800 dark:text-white mb-4 flex items-center gap-2">
                <flux:icon.trophy class="size-4 text-cyan-500" />
                Top Alternatif
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                @foreach (array_slice($rankingData['labels'], 0, 3) as $i => $label)
                    <div class="rounded-lg p-4 border
                                {{ $i === 0
                                    ? 'bg-cyan-50 border-cyan-200 dark:bg-cyan-900/20 dark:border-cyan-800/50'
                                    : 'bg-zinc-50 border-zinc-200 dark:bg-zinc-700/50 dark:border-zinc-600' }}">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl font-black {{ $i === 0 ? 'text-cyan-500' : 'text-zinc-400' }}">
                                #{{ $i + 1 }}
                            </span>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-zinc-800 dark:text-white truncate">{{ $label }}</p>
                                <p class="text-xs font-mono mt-0.5 {{ $i === 0 ? 'text-cyan-600 dark:text-cyan-400' : 'text-zinc-500' }}">
                                    {{ number_format($rankingData['values'][$i] ?? 0, 6) }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- QUICK LINKS --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        @foreach ([
            ['label' => 'Kriteria',     'route' => 'kriteria',     'icon' => 'clipboard-document-list'],
            ['label' => 'Sub Kriteria', 'route' => 'sub-kriteria', 'icon' => 'list-bullet'],
            ['label' => 'Alternatif',   'route' => 'alternatif',   'icon' => 'users'],
            ['label' => 'Penilaian',    'route' => 'penilaian',    'icon' => 'clipboard-document-check'],
        ] as $link)
            <a href="{{ route($link['route']) }}" wire:navigate
               class="flex items-center gap-3 p-4 rounded-lg border border-zinc-200 dark:border-zinc-700
                      bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300
                      hover:border-cyan-300 hover:bg-cyan-50 dark:hover:border-cyan-700 dark:hover:bg-cyan-900/20
                      transition-all duration-150 group">
                <flux:icon :name="$link['icon']" class="size-5 shrink-0 text-zinc-400 group-hover:text-cyan-500 transition-colors" />
                <span class="text-sm font-medium">{{ $link['label'] }}</span>
                <flux:icon.chevron-right class="size-3.5 ml-auto opacity-0 group-hover:opacity-100 transition-opacity shrink-0 text-cyan-500" />
            </a>
        @endforeach
    </div>

    {{-- CHART.JS SCRIPTS --}}
    @script
    <script>
        const loadChartJs = () => new Promise(resolve => {
            if (window.Chart) return resolve();
            const s = document.createElement('script');
            s.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js';
            s.onload = resolve;
            document.head.appendChild(s);
        });

        loadChartJs().then(() => {
            const isDark = () => document.documentElement.classList.contains('dark');
            const grid   = () => isDark() ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.05)';
            const text   = () => isDark() ? '#a1a1aa' : '#71717a';
            const cyan   = '#0ea5e9';

            @if ($hasHasil)
            const rankingCtx = document.getElementById('rankingChart');
            if (rankingCtx) {
                const labels = @json($rankingData['labels']);
                const values = @json($rankingData['values']);
                const colors = values.map((_, i) => i === 0 ? cyan : (isDark() ? '#38bdf8' : '#7dd3fc'));
                new Chart(rankingCtx, {
                    type: 'bar',
                    data: {
                        labels,
                        datasets: [{
                            data: values,
                            backgroundColor: colors,
                            borderRadius: 6,
                            borderSkipped: false,
                            maxBarThickness: 40,
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: { label: ctx => ' Skor: ' + ctx.raw.toFixed(6) },
                                backgroundColor: isDark() ? '#1e293b' : '#fff',
                                titleColor: isDark() ? '#f1f5f9' : '#0f172a',
                                bodyColor: isDark() ? '#94a3b8' : '#475569',
                                borderColor: isDark() ? '#334155' : '#e2e8f0',
                                borderWidth: 1, padding: 10, cornerRadius: 6,
                            }
                        },
                        scales: {
                            x: { grid: { display: false }, ticks: { color: text(), font: { size: 10 }, maxRotation: 30 } },
                            y: { grid: { color: grid() }, border: { display: false }, ticks: { color: text(), font: { size: 10 }, callback: v => v.toFixed(4) } }
                        }
                    }
                });
            }
            @endif

            @if ($hasBobotKrit)
            const bobotCtx = document.getElementById('bobotChart');
            if (bobotCtx) {
                const bobotLabels = @json($bobotData['labels']);
                const bobotValues = @json($bobotData['values']);
                const palette = ['#0ea5e9','#06b6d4','#14b8a6','#10b981','#22c55e','#84cc16','#eab308','#f97316'];

                new Chart(bobotCtx, {
                    type: 'doughnut',
                    data: {
                        labels: bobotLabels,
                        datasets: [{
                            data: bobotValues,
                            backgroundColor: palette.slice(0, bobotLabels.length),
                            borderWidth: 2,
                            borderColor: isDark() ? '#1e293b' : '#ffffff',
                            hoverOffset: 6,
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false, cutout: '68%',
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: { label: ctx => ` ${(ctx.raw * 100).toFixed(2)}%` },
                                backgroundColor: isDark() ? '#1e293b' : '#fff',
                                titleColor: isDark() ? '#f1f5f9' : '#0f172a',
                                bodyColor: isDark() ? '#94a3b8' : '#475569',
                                borderColor: isDark() ? '#334155' : '#e2e8f0',
                                borderWidth: 1, padding: 8, cornerRadius: 6,
                            }
                        }
                    }
                });

                const legendEl = document.getElementById('bobotLegend');
                if (legendEl) {
                    bobotLabels.forEach((label, i) => {
                        legendEl.innerHTML += `
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-1.5 min-w-0">
                                    <span class="size-2 rounded-full shrink-0" style="background:${palette[i]}"></span>
                                    <span class="text-xs text-zinc-600 dark:text-zinc-400 truncate">${label}</span>
                                </div>
                                <span class="text-xs font-mono font-semibold text-zinc-700 dark:text-zinc-300 shrink-0">
                                    ${(bobotValues[i] * 100).toFixed(1)}%
                                </span>
                            </div>`;
                    });
                }
            }
            @endif
        });
    </script>
    @endscript

</div>{{-- end root div --}}
