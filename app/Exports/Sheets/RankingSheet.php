<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class RankingSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths
{
    protected array $rankRows = [];

    public function __construct(
        protected $alternatifs,
        protected array $totalSkor,
    ) {}

    public function title(): string
    {
        return '6. Ranking';
    }

    public function array(): array
    {
        $rows = [['Rank', 'Nama Alternatif', 'Total Skor', 'Keterangan']];

        $rank = 1;
        foreach ($this->totalSkor as $altId => $skor) {
            $alt = $this->alternatifs->firstWhere('id', $altId);
            if (!$alt) continue;

            if ($rank === 1) {
                $ket = 'TERBAIK';
                $this->rankRows[] = $rank + 1;
            } elseif ($rank <= 3) {
                $ket = "Top {$rank}";
            } else {
                $ket = "Rank {$rank}";
            }

            $rows[] = [
                $rank,
                "A{$alt->nomor} - {$alt->nama}",
                number_format($skor, 6),
                $ket,
            ];

            $rank++;
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        $styles = [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF16A34A']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];

        // Baris rank 1 (row 2) — highlight emas
        if (count($this->totalSkor) > 0) {
            $styles[2] = [
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFEF08A']],
            ];
        }

        return $styles;
    }

    public function columnWidths(): array
    {
        return ['A' => 8, 'B' => 35, 'C' => 18, 'D' => 15];
    }
}