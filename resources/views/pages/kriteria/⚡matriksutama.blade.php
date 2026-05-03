<?php

use Livewire\Component;
use App\Models\Kriteria;
use App\Models\KriteriaNilai;
use App\Models\KriteriaHasil;

new class extends Component {

    public bool $showMatriks = false;
    public $kriterias = [];
    public $matrix = [];
    public $priorities = [];
    public $totals = [];
    public $cr = 0;
    public $ci = 0;
    public $normalized = [];
    public $rowTotals = [];
    public $mptb = [];
    public $mptbTotals = [];
    public $rk = [];
    public $lambdaMax = 0;

    public function mount()
    {
        $this->kriterias = Kriteria::all();

        // 1. Init semua ke 1 dulu (default)
        foreach ($this->kriterias as $row) {
            foreach ($this->kriterias as $col) {
                $this->matrix[$row->id][$col->id] = 1;
            }
        }

        // 2. Load dari database — hanya segitiga atas (dari < tujuan)
        //    lalu recompute kebalikannya agar simetri terjaga
        $savedValues = KriteriaNilai::all();

        foreach ($savedValues as $nilai) {
            $dari = $nilai->kriteria_id_dari;
            $tujuan = $nilai->kriteria_id_tujuan;
            $val = (float) $nilai->nilai;

            if ($dari == $tujuan) {
                // Diagonal selalu 1
                $this->matrix[$dari][$tujuan] = 1;
            } elseif ($dari < $tujuan) {
                // Segitiga atas: simpan nilai asli
                $this->matrix[$dari][$tujuan] = $val;
                // Segitiga bawah: kebalikannya
                $this->matrix[$tujuan][$dari] = $val != 0 ? 1 / $val : 1;
            }
        }

        $this->calculate();
    }

    public function resetMatrix()
    {
        foreach ($this->kriterias as $row) {
            foreach ($this->kriterias as $col) {
                $this->matrix[$row->id][$col->id] = 1;
            }
        }

        $this->calculate();
    }

    public function updatedMatrix($value, $key)
    {
        [$rowId, $colId] = explode('.', $key);

        if ($rowId == $colId) {
            $this->matrix[$rowId][$colId] = 1;
            return;
        }

        $floatValue = (float) $value;

        if ($floatValue <= 0) {
            $this->matrix[$rowId][$colId] = 1;
            $this->matrix[$colId][$rowId] = 1;
        } else {
            $this->matrix[$rowId][$colId] = $floatValue;
            // Kebalikan: pakai 1 / nilai asli (bukan 1 / float hasil konversi)
            // agar presisi lebih terjaga
            $this->matrix[$colId][$rowId] = 1 / $floatValue;
        }

        $this->calculate();
    }

    public function calculate()
    {
        $n = count($this->kriterias);

        if ($n === 0)
            return;

        // ── TOTAL KOLOM ──────────────────────────────────────────────
        foreach ($this->kriterias as $col) {
            $sum = 0;
            foreach ($this->kriterias as $row) {
                $sum += $this->matrix[$row->id][$col->id] ?? 1;
            }
            $this->totals[$col->id] = $sum ?: 1;
        }

        // ── NORMALISASI + PRIORITAS ───────────────────────────────────
        foreach ($this->kriterias as $row) {
            $sumRow = 0;
            foreach ($this->kriterias as $col) {
                $val = $this->matrix[$row->id][$col->id] ?? 1;
                $tot = $this->totals[$col->id] ?? 1;
                $norm = $tot != 0 ? $val / $tot : 0;

                $this->normalized[$row->id][$col->id] = $norm;
                $sumRow += $norm;
            }

            $this->rowTotals[$row->id] = $sumRow;
            // Prioritas = rata-rata baris normalisasi
            $this->priorities[$row->id] = $sumRow / $n;
        }

        // ── WEIGHTED SUM VECTOR (MPTB) ────────────────────────────────
        foreach ($this->kriterias as $row) {
            $sum = 0;
            foreach ($this->kriterias as $col) {
                $nilai = $this->matrix[$row->id][$col->id] ?? 1;
                $prio = $this->priorities[$col->id] ?? 0;
                $hasil = $nilai * $prio;

                $this->mptb[$row->id][$col->id] = $hasil;
                $sum += $hasil;
            }
            $this->mptbTotals[$row->id] = $sum;
        }

        // ── CONSISTENCY VECTOR (λ_i) ──────────────────────────────────
        foreach ($this->kriterias as $row) {
            $jumlahBaris = $this->mptbTotals[$row->id] ?? 0;
            $prioritas = $this->priorities[$row->id] ?? 0;

            $this->rk[$row->id] = $prioritas != 0
                ? $jumlahBaris / $prioritas
                : 0;
        }

        // ── LAMBDA MAX = rata-rata vektor konsistensi ─────────────────
        $this->lambdaMax = array_sum($this->rk) / $n;

        // ── CI ────────────────────────────────────────────────────────
        $this->ci = $n > 1
            ? ($this->lambdaMax - $n) / ($n - 1)
            : 0;

        // ── RI ────────────────────────────────────────────────────────
        $riTable = [
            1 => 0.00,
            2 => 0.00,
            3 => 0.58,
            4 => 0.90,
            5 => 1.12,
            6 => 1.24,
            7 => 1.32,
            8 => 1.41,
            9 => 1.45,
            10 => 1.49,
        ];
        $ri = $riTable[$n] ?? 1.49;

        // ── CR ────────────────────────────────────────────────────────
        $this->cr = $ri != 0 ? $this->ci / $ri : 0;
    }

    public function save()
    {
        if ($this->cr >= 0.1) {
            session()->flash('error', 'Matriks tidak konsisten (CR ≥ 0.1). Sesuaikan perbandingan terlebih dahulu.');
            return;
        }

        // Simpan bobot/prioritas tiap kriteria
        foreach ($this->kriterias as $kriteria) {
            KriteriaHasil::updateOrCreate(
                ['kriteria_id' => $kriteria->id],
                ['nilai' => $this->priorities[$kriteria->id] ?? 0]
            );
        }

        // Simpan nilai matriks (cukup simpan semua sel, termasuk kebalikan)
        foreach ($this->kriterias as $row) {
            foreach ($this->kriterias as $col) {
                KriteriaNilai::updateOrCreate(
                    [
                        'kriteria_id_dari' => $row->id,
                        'kriteria_id_tujuan' => $col->id,
                    ],
                    ['nilai' => $this->matrix[$row->id][$col->id] ?? 1]
                );
            }
        }

        session()->flash('success', 'Data berhasil disimpan.');
    }

    public function toggleMatriks()
    {
        $this->showMatriks = !$this->showMatriks;
    }
};
?>

<div class="space-y-6">

    {{-- Flash message --}}
    @if (session('success'))
        <div class="px-4 py-3 text-sm text-green-700 bg-green-100 border border-green-300 rounded-lg flex items-center gap-2">
            <flux:icon.check-circle class="size-5 text-green-500" />
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="px-4 py-3 text-sm text-red-700 bg-red-100 border border-red-300 rounded-lg flex items-center gap-2">
            <flux:icon.x-circle class="size-5 text-red-600" />
            {{ session('error') }}
        </div>
    @endif

    <form wire:submit.prevent="save" class="space-y-6">
        <flux:card class="p-0 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr>
                            <th class="px-3 py-2 text-left">Kriteria (Y \ X)</th>
                            @foreach ($kriterias as $kriteriaKolom)
                                <th class="px-3 py-2 text-center">{{ $kriteriaKolom->nama }}</th>
                            @endforeach
                        </tr>
                    </thead>

                    <tbody class="text-sm text-gray-700">
                        @foreach ($kriterias as $kriteriaBaris)
                            <tr class="border-b hover:bg-gray-50 transition">

                                <td class="px-4 py-3 font-medium text-gray-800 whitespace-nowrap">
                                    {{ $kriteriaBaris->nama }}
                                </td>

                                @foreach ($kriterias as $kriteriaKolom)
                                    <td class="px-2 py-2 text-center">

                                        {{-- DIAGONAL --}}
                                        @if ($kriteriaBaris->id === $kriteriaKolom->id)
                                            <input value="1" readonly
                                                class="w-16 py-1 text-center text-gray-500 bg-gray-100 border border-gray-200 rounded-md">

                                            {{-- SEGITIGA BAWAH (auto dari kebalikan) --}}
                                        @elseif ($kriteriaBaris->id > $kriteriaKolom->id)
                                            <input type="text"
                                                value="{{ rtrim(rtrim(number_format($matrix[$kriteriaBaris->id][$kriteriaKolom->id] ?? 1, 4), '0'), '.') }}"
                                                readonly
                                                class="w-16 py-1 text-center text-gray-400 bg-gray-50 border border-gray-200 rounded-md">

                                            {{-- SEGITIGA ATAS (input user) --}}
                                        @else
                                            <select wire:model.lazy="matrix.{{ $kriteriaBaris->id }}.{{ $kriteriaKolom->id }}"
                                                class="w-20 py-1 text-center text-gray-700 bg-white border border-gray-200 rounded-md
                                                                   focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400
                                                                   hover:border-gray-400 transition">

                                                {{-- Pecahan (kurang penting) --}}
                                                <option value="0.111111111111111">1/9</option>
                                                <option value="0.125">1/8</option>
                                                <option value="0.142857142857143">1/7</option>
                                                <option value="0.166666666666667">1/6</option>
                                                <option value="0.2">1/5</option>
                                                <option value="0.25">1/4</option>
                                                <option value="0.333333333333333">1/3</option>
                                                <option value="0.5">1/2</option>

                                                {{-- Sama penting --}}
                                                <option value="1">1</option>

                                                {{-- Bulat (lebih penting) --}}
                                                <option value="2">2</option>
                                                <option value="3">3</option>
                                                <option value="4">4</option>
                                                <option value="5">5</option>
                                                <option value="6">6</option>
                                                <option value="7">7</option>
                                                <option value="8">8</option>
                                                <option value="9">9</option>

                                            </select>
                                        @endif

                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>

                    <tfoot>
                        <tr class="font-semibold bg-gray-50">
                            <td class="px-3 py-2">Jumlah</td>
                            @foreach ($kriterias as $kriteriaKolom)
                                <td class="text-center px-2 py-1">
                                    {{ number_format($totals[$kriteriaKolom->id] ?? 0, 3) }}
                                </td>
                            @endforeach
                        </tr>
                    </tfoot>
                </table>
            </div>
        </flux:card>

        <div class="mt-3 text-xs text-gray-600">
            <b>Petunjuk:</b><br><br>

            <b>1</b> Dua elemen memiliki pengaruh yang sama penting.<br>
            <b>3</b> Elemen yang satu sedikit lebih penting dibanding elemen yang lainnya.<br>
            <b>5</b> Elemen yang satu lebih penting daripada elemen yang lainnya.<br>
            <b>7</b> Satu elemen sangat penting daripada elemen yang lainnya.<br>
            <b>9</b> Satu elemen mutlak lebih penting daripada elemen yang lainnya.<br><br>

            <b>2, 4, 6, 8</b> Nilai tengah di antara dua nilai pertimbangan yang saling berdekatan.<br><br>

            <b>Kebalikan</b> Jika elemen yang satu (x) lebih tinggi dibanding elemen lainnya (y),
            maka nilai (y) merupakan kebalikan dari (x), yaitu <i>1/x</i>.
        </div>

        {{-- TOMBOL — tambahkan type="button" agar tidak trigger submit form --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-end gap-3 mb-8">
            <flux:button type="button" wire:click="toggleMatriks" variant="outline">
                {{ $showMatriks ? 'Sembunyikan' : 'Lihat Hasil' }}
            </flux:button>
            <flux:button type="button" wire:click="resetMatrix" variant="outline" icon="arrow-path">
                Reset
            </flux:button>
            <flux:button type="submit" variant="primary" icon="check">
                Simpan
            </flux:button>
        </div>
    </form>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- HASIL --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    @if ($showMatriks)

        {{-- 1. MATRIKS NORMALISASI --}}
        <div class="mb-4 flex items-center gap-2">
            <div class="h-px flex-1 bg-zinc-200 dark:bg-zinc-800"></div>
            <span class="text-[10px] font-bold uppercase tracking-widest text-zinc-400">
                Matriks Nilai Kriteria (Normalisasi)
            </span>
            <div class="h-px flex-1 bg-zinc-200 dark:bg-zinc-800"></div>
        </div>

        <flux:card class="p-0 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-gray-700">
                    <thead class="bg-gray-50 text-gray-600 uppercase text-xs tracking-wide">
                        <tr>
                            <th class="w-48 px-6 py-3 text-left font-semibold">Kriteria</th>
                            @foreach ($kriterias as $k)
                                <th class="px-4 py-3 text-center font-semibold">{{ $k->nama }}</th>
                            @endforeach
                            <th class="px-4 py-3 text-center font-semibold">Jumlah</th>
                            <th class="px-4 py-3 text-center font-semibold">Prioritas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($kriterias as $row)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-3 font-medium text-gray-800 whitespace-nowrap">{{ $row->nama }}</td>
                                @foreach ($kriterias as $col)
                                    <td class="px-3 py-2 text-center font-mono text-gray-600">
                                        {{ number_format($normalized[$row->id][$col->id] ?? 0, 6) }}
                                    </td>
                                @endforeach
                                <td class="px-3 py-2 text-center font-semibold text-gray-800 bg-gray-50">
                                    {{ number_format($rowTotals[$row->id] ?? 0, 6) }}
                                </td>
                                <td class="px-3 py-2 text-center font-semibold text-gray-600 bg-gray-50">
                                    {{ number_format($priorities[$row->id] ?? 0, 6) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 border-t">
                        <tr>
                            <td class="px-6 py-3 font-semibold">Total</td>
                            @foreach ($kriterias as $col)
                                <td class="px-3 py-2 text-center font-mono text-gray-500">—</td>
                            @endforeach
                            <td class="px-3 py-2 text-center font-semibold">
                                {{ number_format(array_sum($rowTotals), 6) }}
                            </td>
                            <td class="px-3 py-2 text-center font-semibold text-gray-700">
                                {{ number_format(array_sum($priorities), 6) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </flux:card>

        {{-- 2. WEIGHTED SUM VECTOR --}}
        <div class="mb-4 flex items-center gap-2">
            <div class="h-px flex-1 bg-zinc-200 dark:bg-zinc-800"></div>
            <span class="text-[10px] font-bold uppercase tracking-widest text-zinc-400">
                Matriks Penjumlahan Tiap Baris (Weighted Sum Vector)
            </span>
            <div class="h-px flex-1 bg-zinc-200 dark:bg-zinc-800"></div>
        </div>

        <flux:card class="p-0 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-gray-700">
                    <thead class="bg-gray-50 text-gray-600 uppercase text-xs tracking-wide">
                        <tr>
                            <th class="w-48 px-6 py-3 text-left font-semibold">Kriteria</th>
                            @foreach ($kriterias as $k)
                                <th class="px-4 py-3 text-center font-semibold">{{ $k->nama }}</th>
                            @endforeach
                            <th class="px-4 py-3 text-center font-semibold">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($kriterias as $row)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-3 font-medium text-gray-800 whitespace-nowrap">{{ $row->nama }}</td>
                                @foreach ($kriterias as $col)
                                    <td class="px-3 py-2 text-center font-mono text-gray-600">
                                        {{ number_format($mptb[$row->id][$col->id] ?? 0, 6) }}
                                    </td>
                                @endforeach
                                <td class="px-3 py-2 text-center font-semibold text-gray-600 bg-gray-50">
                                    {{ number_format($mptbTotals[$row->id] ?? 0, 6) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </flux:card>

        {{-- 3. RASIO KONSISTENSI --}}
        <div class="mb-4 flex items-center gap-2">
            <div class="h-px flex-1 bg-zinc-200 dark:bg-zinc-800"></div>
            <span class="text-[10px] font-bold uppercase tracking-widest text-zinc-400">
                Rasio Konsistensi
            </span>
            <div class="h-px flex-1 bg-zinc-200 dark:bg-zinc-800"></div>
        </div>

        <flux:card class="p-0 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-gray-700">
                    <thead class="bg-gray-50 text-gray-600 uppercase text-xs tracking-wide">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">Kriteria</th>
                            <th class="px-4 py-3 text-center font-semibold">Jumlah Baris</th>
                            <th class="px-4 py-3 text-center font-semibold">Prioritas (w)</th>
                            <th class="px-4 py-3 text-center font-semibold">λ = Jumlah / w</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($kriterias as $row)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3 font-medium text-gray-800 whitespace-nowrap">{{ $row->nama }}</td>
                                <td class="px-4 py-2 text-center font-mono text-gray-600">
                                    {{ number_format($mptbTotals[$row->id] ?? 0, 6) }}
                                </td>
                                <td class="px-4 py-2 text-center font-semibold text-gray-600">
                                    {{ number_format($priorities[$row->id] ?? 0, 6) }}
                                </td>
                                <td class="px-4 py-2 text-center font-bold text-gray-600">
                                    {{ number_format($rk[$row->id] ?? 0, 6) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 border-t">
                        <tr>
                            <td class="px-4 py-3 font-semibold text-gray-800">Total / λ max</td>
                            <td class="px-4 py-2 text-center font-semibold text-gray-700">
                                {{ number_format(array_sum($mptbTotals), 6) }}
                            </td>
                            <td class="px-4 py-2 text-center font-semibold text-gray-700">
                                {{ number_format(array_sum($priorities), 6) }}
                            </td>
                            <td class="px-4 py-2 text-center font-bold text-gray-700 bg-gray-50">
                                {{ number_format($lambdaMax, 6) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </flux:card>

        {{-- 4. HASIL AKHIR --}}
        <div class="mb-4 flex items-center gap-2">
            <div class="h-px flex-1 bg-zinc-200 dark:bg-zinc-800"></div>
            <span class="text-[10px] font-bold uppercase tracking-widest text-zinc-400">
                Hasil Perhitungan
            </span>
            <div class="h-px flex-1 bg-zinc-200 dark:bg-zinc-800"></div>
        </div>

        <flux:card class="p-0 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-gray-700">
                    <thead class="bg-gray-50 text-gray-600 uppercase text-xs tracking-wide">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">Keterangan</th>
                            <th class="px-4 py-3 text-center font-semibold">Nilai</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-700">n (Jumlah Kriteria)</td>
                            <td class="px-4 py-3 text-center font-semibold">{{ count($kriterias) }}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-700">λ max</td>
                            <td class="px-4 py-3 text-center font-mono text-gray-600 font-bold">
                                {{ number_format($lambdaMax, 6) }}
                            </td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-700">CI = (λ max − n) / (n − 1)</td>
                            <td class="px-4 py-3 text-center font-mono">
                                {{ number_format($ci, 6) }}
                            </td>
                        </tr>
                        <tr class="{{ $cr < 0.1 ? 'bg-gray-50' : 'bg-red-50' }}">
                            <td class="px-4 py-3 font-semibold">CR = CI / RI</td>
                            <td class="px-4 py-3 text-center font-bold
                                    {{ $cr < 0.1 ? 'text-gray-600' : 'text-red-600' }}">
                                {{ number_format($cr, 6) }}
                                <span class="text-xs font-normal ml-1 flex items-center justify-center gap-1">
                                    @if ($cr < 0.1)
                                        (< 0.1 <flux:icon.check-circle class="size-3.5" />)
                                    @else
                                        (≥ 0.1 <flux:icon.x-circle class="size-3.5" />)
                                    @endif
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </flux:card>

        {{-- STATUS --}}
        <div class="mt-4 text-center font-semibold">
            @if ($cr < 0.1)
                <span class="text-gray-700 flex items-center justify-center gap-2 text-green-500">
                    <flux:icon.check-circle class="size-5 text-green-500" />
                    Konsisten — Matriks dapat digunakan
                </span>
            @else
                <span class="text-red-600 flex items-center justify-center gap-2">
                    <flux:icon.x-circle class="size-5 text-red-600" />
                    Tidak Konsisten
                </span>
                <div class="text-sm text-red-400 mt-1 flex items-center justify-center gap-2">
                    <flux:icon.exclamation-triangle class="size-4 " />
                    CR ≥ 0.1 — Perbaiki perbandingan antar kriteria sebelum menyimpan
                </div>
            @endif
        </div>

    @endif
</div>
