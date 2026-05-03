<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class PenilaianSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths
{
    public function __construct(
        protected $kriterias,
        protected $alternatifs,
        protected array $penilaianMatrix,
    ) {}

    public function title(): string
    {
        return '3. Data Penilaian';
    }

    public function array(): array
    {
        // Header
        $header = ['No', 'Nama Alternatif'];
        foreach ($this->kriterias as $k) {
            $header[] = $k->nama;
        }

        $rows = [$header];

        foreach ($this->alternatifs as $i => $alt) {
            $row = [$i + 1, "A{$alt->nomor} - {$alt->nama}"];
            foreach ($this->kriterias as $k) {
                $row[] = $this->penilaianMatrix[$alt->id][$k->id] ?? '—';
            }
            $rows[] = $row;
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF16A34A']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function columnWidths(): array
    {
        $widths = ['A' => 6, 'B' => 30];
        $cols   = range('C', 'Z');

        foreach ($this->kriterias as $i => $k) {
            $widths[$cols[$i] ?? 'Z'] = 20;
        }

        return $widths;
    }
}