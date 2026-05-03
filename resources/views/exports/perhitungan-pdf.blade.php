<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Perhitungan AHP</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #333;
            line-height: 1.4;
        }
        .container {
            margin: 20px;
        }
        h1 {
            text-align: center;
            font-size: 16px;
            margin-bottom: 10px;
            color: #0891b2;
        }
        h2 {
            font-size: 12px;
            margin-top: 20px;
            margin-bottom: 10px;
            padding: 8px;
            background-color: #cffafe;
            border-left: 4px solid #06b6d4;
            color: #0891b2;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        thead {
            background-color: #06b6d4;
            color: white;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border: 1px solid #d1d5db;
        }
        th {
            text-align: center;
            font-weight: bold;
        }
        td {
            text-align: center;
        }
        td:first-child {
            text-align: left;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .font-bold {
            font-weight: bold;
        }
        .bg-light {
            background-color: #f3f4f6;
        }
        .page-break {
            page-break-after: always;
        }
        .total-row {
            background-color: #cffafe;
            font-weight: bold;
        }
        .ranking {
            margin-top: 20px;
        }
        .ranking-item {
            display: flex;
            justify-content: space-between;
            padding: 10px;
            border-bottom: 1px solid #e5e7eb;
            background-color: #f9fafb;
        }
        .ranking-num {
            min-width: 30px;
            font-weight: bold;
            color: #06b6d4;
        }
        .ranking-name {
            flex: 1;
            padding-left: 15px;
        }
        .ranking-score {
            min-width: 100px;
            text-align: right;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>LAPORAN PERHITUNGAN AHP</h1>
        <p style="text-align: center; margin-bottom: 20px; color: #6b7280;">
            Tanggal: {{ now()->format('d F Y') }}
        </p>

        <!-- 1. BOBOT KRITERIA -->
        <h2>1. Bobot Prioritas Kriteria</h2>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kode</th>
                    <th>Nama Kriteria</th>
                    <th style="text-align: center;">Bobot Prioritas</th>
                </tr>
            </thead>
            <tbody>
                @php $no = 1; @endphp
                @foreach ($kriterias as $kriteria)
                    <tr>
                        <td>{{ $no++ }}</td>
                        <td>{{ $kriteria->kode }}</td>
                        <td>{{ $kriteria->nama }}</td>
                        <td class="text-right">{{ number_format($bobotKriteria[$kriteria->id] ?? 0, 6) }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="3" class="text-right">TOTAL</td>
                    <td class="text-right">{{ number_format(array_sum($bobotKriteria), 6) }}</td>
                </tr>
            </tbody>
        </table>

        <!-- 2. BOBOT SUB KRITERIA -->
        <h2>2. Bobot Prioritas Sub-Kriteria</h2>
        @foreach ($kriterias as $kriteria)
            <h3 style="font-size: 11px; margin: 15px 0 8px 0; color: #374151;">
                {{ $kriteria->kode }} - {{ $kriteria->nama }}
            </h3>
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Sub-Kriteria</th>
                        <th style="text-align: center;">Bobot</th>
                    </tr>
                </thead>
                <tbody>
                    @php $subNo = 1; @endphp
                    @if ($kriteria->subKriteria && count($kriteria->subKriteria) > 0)
                        @foreach ($kriteria->subKriteria as $subKriteria)
                            <tr>
                                <td>{{ $subNo++ }}</td>
                                <td>{{ $subKriteria->nama }}</td>
                                <td class="text-right">
                                    {{ number_format($bobotSubKriteria[$kriteria->id][$subKriteria->id] ?? 0, 6) }}
                                </td>
                            </tr>
                        @endforeach
                        <tr class="total-row">
                            <td colspan="2" class="text-right">TOTAL</td>
                            <td class="text-right">
                                {{ number_format(array_sum($bobotSubKriteria[$kriteria->id] ?? []), 6) }}
                            </td>
                        </tr>
                    @else
                        <tr>
                            <td colspan="3" class="text-center" style="color: #9ca3af;">
                                Tidak ada sub-kriteria
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        @endforeach

        <!-- PAGE BREAK -->
        <div class="page-break"></div>

        <!-- 3. PENILAIAN ALTERNATIF -->
        <h2>3. Penilaian Alternatif</h2>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Alternatif</th>
                    @foreach ($kriterias as $kriteria)
                        <th style="text-align: center;">{{ $kriteria->kode }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @php $altNo = 1; @endphp
                @foreach ($alternatifs as $alternatif)
                    <tr>
                        <td>{{ $altNo++ }}</td>
                        <td>{{ $alternatif->nama }}</td>
                        @foreach ($kriterias as $kriteria)
                            <td class="text-center">
                                {{ $penilaianMatrix[$alternatif->id][$kriteria->id] ?? '—' }}
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- 4. MATRIKS KEPUTUSAN -->
        <h2>4. Matriks Keputusan (Nilai Atribut)</h2>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Alternatif</th>
                    @foreach ($kriterias as $kriteria)
                        <th style="text-align: center;">{{ $kriteria->kode }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @php $altNo = 1; @endphp
                @foreach ($alternatifs as $alternatif)
                    <tr>
                        <td>{{ $altNo++ }}</td>
                        <td>{{ $alternatif->nama }}</td>
                        @foreach ($kriterias as $kriteria)
                            <td class="text-right">
                                {{ number_format($nilaiMatrix[$alternatif->id][$kriteria->id] ?? 0, 4) }}
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- PAGE BREAK -->
        <div class="page-break"></div>

        <!-- 5. NILAI ATRIBUT TERBOBOT -->
        <h2>5. Nilai Atribut Terbobot (Skor)</h2>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Alternatif</th>
                    @foreach ($kriterias as $kriteria)
                        <th style="text-align: center;">{{ $kriteria->kode }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @php $altNo = 1; @endphp
                @foreach ($alternatifs as $alternatif)
                    <tr>
                        <td>{{ $altNo++ }}</td>
                        <td>{{ $alternatif->nama }}</td>
                        @foreach ($kriterias as $kriteria)
                            <td class="text-right">
                                {{ number_format($skorMatrix[$alternatif->id][$kriteria->id] ?? 0, 6) }}
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- 6. RANKING HASIL AKHIR -->
        <h2>6. Ranking Hasil Akhir</h2>
        <div class="ranking">
            @php
                $ranking = [];
                foreach ($alternatifs as $alt) {
                    $ranking[] = [
                        'nama' => $alt->nama,
                        'skor' => $totalSkor[$alt->id] ?? 0
                    ];
                }
                usort($ranking, function($a, $b) { return $b['skor'] <=> $a['skor']; });
                $rank = 1;
            @endphp
            @foreach ($ranking as $item)
                <div class="ranking-item">
                    <div class="ranking-num">{{ $rank++ }}.</div>
                    <div class="ranking-name">{{ $item['nama'] }}</div>
                    <div class="ranking-score">{{ number_format($item['skor'], 6) }}</div>
                </div>
            @endforeach
        </div>

        <p style="text-align: center; margin-top: 30px; color: #9ca3af; font-size: 9px;">
            Generated on {{ now()->format('d M Y H:i:s') }}
        </p>
    </div>
</body>
</html>
