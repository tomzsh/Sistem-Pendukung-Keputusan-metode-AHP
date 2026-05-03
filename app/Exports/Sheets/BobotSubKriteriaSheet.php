<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class BobotSubKriteriaSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths
{
    protected array $headerRows = [];

    public function __construct(
        protected $kriterias,
        protected array $bobotSubKriteria,
    ) {}

    public function title(): string
    {
        return '2. Bobot Sub Kriteria';
    }

    public function array(): array
    {
        $rows = [];

        foreach ($this->kriterias as $k) {
            // Header per kriteria
            $this->headerRows[] = count($rows) + 1;
            $rows[] = ["[{$k->kode}] {$k->nama}", 'Sub Kriteria', 'Bobot'];

            foreach ($k->subKriteria as $sub) {
                $rows[] = [
                    '',
                    $sub->nama,
                    number_format($this->bobotSubKriteria[$k->id][$sub->id] ?? 0, 6),
                ];
            }

            // Total per kriteria
            $rows[] = ['', 'TOTAL', number_format(array_sum($this->bobotSubKriteria[$k->id] ?? []), 6)];
            $rows[] = ['', '', ''];  // spacer
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        $styles = [];

        foreach ($this->headerRows as $row) {
            $styles[$row] = [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF16A34A']],
            ];
        }

        return $styles;
    }

    public function columnWidths(): array
    {
        return ['A' => 30, 'B' => 35, 'C' => 18];
    }
}