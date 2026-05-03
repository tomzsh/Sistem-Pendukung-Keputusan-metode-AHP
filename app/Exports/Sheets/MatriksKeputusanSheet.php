<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class MatriksKeputusanSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths
{
    public function __construct(
        protected $kriterias,
        protected $alternatifs,
        protected array $nilaiMatrix,
    ) {}

    public function title(): string
    {
        return '4. Matriks Keputusan';
    }

    public function array(): array
    {
        $header = ['No', 'Nama Alternatif'];
        foreach ($this->kriterias as $k) {
            $header[] = $k->nama;
        }

        $rows = [$header];

        foreach ($this->alternatifs as $i => $alt) {
            $row = [$i + 1, "A{$alt->nomor} - {$alt->nama}"];
            foreach ($this->kriterias as $k) {
                $row[] = number_format($this->nilaiMatrix[$alt->id][$k->id] ?? 0, 6);
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
            $widths[$cols[$i] ?? 'Z'] = 16;
        }
        return $widths;
    }
}