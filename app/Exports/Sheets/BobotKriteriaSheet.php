<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class BobotKriteriaSheet implements FromArray, WithTitle, WithHeadings, WithStyles, WithColumnWidths
{
    public function __construct(
        protected $kriterias,
        protected array $bobotKriteria,
    ) {}

    public function title(): string
    {
        return '1. Bobot Kriteria';
    }

    public function headings(): array
    {
        return ['No', 'Kode', 'Nama Kriteria', 'Bobot Prioritas'];
    }

    public function array(): array
    {
        $rows = [];

        foreach ($this->kriterias as $i => $k) {
            $rows[] = [
                $i + 1,
                $k->kode,
                $k->nama,
                number_format($this->bobotKriteria[$k->id] ?? 0, 6),
            ];
        }

        // Baris total
        $rows[] = ['', '', 'TOTAL', number_format(array_sum($this->bobotKriteria), 6)];

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        $last = count($this->kriterias) + 2;

        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF16A34A']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            $last => [
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFD1FAE5']],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return ['A' => 6, 'B' => 10, 'C' => 35, 'D' => 18];
    }
}