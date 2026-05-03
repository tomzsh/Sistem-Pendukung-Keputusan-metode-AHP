<?php

namespace App\Exports;

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Style\Font;
use PhpOffice\PhpWord\SimpleType\JcTable;

class PerhitunganWordExport
{
    public function __construct(
        protected array $bobotKriteria,
        protected array $bobotSubKriteria,
        protected array $penilaianMatrix,
        protected array $nilaiMatrix,
        protected array $skorMatrix,
        protected array $totalSkor,
        protected $kriterias,
        protected $alternatifs,
    ) {}

    public function download(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Calibri');
        $phpWord->setDefaultFontSize(10);

        // Style
        $phpWord->addTitleStyle(1, ['bold' => true, 'size' => 14, 'color' => '15803d']);
        $phpWord->addTitleStyle(2, ['bold' => true, 'size' => 11, 'color' => '15803d']);

        $headerStyle  = ['bgColor' => '16a34a', 'color' => 'ffffff', 'bold' => true, 'size' => 9];
        $cellStyle    = ['borderSize' => 4, 'borderColor' => 'd1fae5'];
        $centerAlign  = ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER];
        $tableStyle   = ['borderSize' => 4, 'borderColor' => 'd1fae5', 'cellMargin' => 80];

        $section = $phpWord->addSection([
            'marginTop'    => 800,
            'marginBottom' => 800,
            'marginLeft'   => 900,
            'marginRight'  => 900,
        ]);

        // Judul
        $section->addTitle('Laporan Perhitungan AHP', 1);
        $section->addText(
            'Dicetak pada: ' . now()->format('d F Y, H:i') . ' WIB',
            ['size' => 8, 'color' => '6b7280'],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]
        );
        $section->addTextBreak(1);

        // ── 1. Bobot Kriteria ─────────────────────────────────────────
        $section->addTitle('1. Bobot Prioritas Kriteria', 2);
        $table = $section->addTable($tableStyle);

        $table->addRow();
        foreach (['No', 'Kode', 'Nama Kriteria', 'Bobot Prioritas'] as $h) {
            $table->addCell(null, ['bgColor' => '16a34a'])->addText($h, $headerStyle, $centerAlign);
        }

        foreach ($this->kriterias as $i => $k) {
            $table->addRow();
            $table->addCell(500)->addText($i + 1, null, $centerAlign);
            $table->addCell(1000)->addText($k->kode, null, $centerAlign);
            $table->addCell(null)->addText($k->nama);
            $table->addCell(1500)->addText(
                number_format($this->bobotKriteria[$k->id] ?? 0, 6), null, $centerAlign
            );
        }

        // Total
        $table->addRow();
        $table->addCell(null, ['bgColor' => 'd1fae5', 'gridSpan' => 3])
            ->addText('TOTAL', ['bold' => true], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::RIGHT]);
        $table->addCell(1500, ['bgColor' => 'd1fae5'])
            ->addText(number_format(array_sum($this->bobotKriteria), 6), ['bold' => true], $centerAlign);

        $section->addTextBreak(1);

        // ── 2. Bobot Sub Kriteria ─────────────────────────────────────
        $section->addTitle('2. Bobot Prioritas Sub Kriteria', 2);

        foreach ($this->kriterias as $k) {
            $section->addText("[{$k->kode}] {$k->nama}", ['bold' => true, 'size' => 9, 'color' => '15803d']);

            $table = $section->addTable($tableStyle);
            $table->addRow();
            foreach (['Sub Kriteria', 'Bobot'] as $h) {
                $table->addCell(null, ['bgColor' => '16a34a'])->addText($h, $headerStyle, $centerAlign);
            }

            foreach ($k->subKriteria as $sub) {
                $table->addRow();
                $table->addCell(null)->addText($sub->nama);
                $table->addCell(1500)->addText(
                    number_format($this->bobotSubKriteria[$k->id][$sub->id] ?? 0, 6), null, $centerAlign
                );
            }
            $section->addTextBreak(1);
        }

        // ── 3-6 menggunakan helper yang sama ─────────────────────────
        $this->addMatrixTable(
            $section, '3. Data Penilaian Alternatif',
            $this->alternatifs, $this->kriterias,
            $this->penilaianMatrix, $headerStyle, $centerAlign, $tableStyle, false
        );

        $this->addMatrixTable(
            $section, '4. Matriks Keputusan (X)',
            $this->alternatifs, $this->kriterias,
            $this->nilaiMatrix, $headerStyle, $centerAlign, $tableStyle, true, 4
        );

        // 5. Nilai Atribut (dengan kolom Total)
        $section->addTitle('5. Perhitungan Nilai Atribut', 2);
        $table = $section->addTable($tableStyle);
        $table->addRow();
        $table->addCell(500, ['bgColor' => '16a34a'])->addText('No', $headerStyle, $centerAlign);
        $table->addCell(2000, ['bgColor' => '16a34a'])->addText('Nama', $headerStyle, $centerAlign);
        foreach ($this->kriterias as $k) {
            $table->addCell(null, ['bgColor' => '16a34a'])->addText($k->nama, $headerStyle, $centerAlign);
        }
        $table->addCell(1500, ['bgColor' => '166534'])->addText('Total', $headerStyle, $centerAlign);

        foreach ($this->alternatifs as $i => $alt) {
            $table->addRow();
            $table->addCell(500)->addText($i + 1, null, $centerAlign);
            $table->addCell(2000)->addText("A{$alt->nomor} - {$alt->nama}");
            foreach ($this->kriterias as $k) {
                $table->addCell(null)->addText(
                    number_format($this->skorMatrix[$alt->id][$k->id] ?? 0, 4), null, $centerAlign
                );
            }
            $table->addCell(1500, ['bgColor' => 'dcfce7'])->addText(
                number_format($this->totalSkor[$alt->id] ?? 0, 6), ['bold' => true], $centerAlign
            );
        }
        $section->addTextBreak(1);

        // 6. Ranking
        $section->addTitle('6. Ranking Akhir', 2);
        $table = $section->addTable($tableStyle);
        $table->addRow();
        foreach (['Rank', 'Nama Alternatif', 'Total Skor', 'Keterangan'] as $h) {
            $table->addCell(null, ['bgColor' => '16a34a'])->addText($h, $headerStyle, $centerAlign);
        }

        $rank = 1;
        foreach ($this->totalSkor as $altId => $skor) {
            $alt = $this->alternatifs->firstWhere('id', $altId);
            if (!$alt) continue;

            $bg  = $rank === 1 ? 'fef08a' : ($rank === 2 ? 'e5e7eb' : ($rank === 3 ? 'fed7aa' : null));
            $ket = $rank === 1 ? 'TERBAIK' : "Rank {$rank}";

            $table->addRow();
            $table->addCell(600, $bg ? ['bgColor' => $bg] : [])->addText($rank, ['bold' => $rank <= 3], $centerAlign);
            $table->addCell(null)->addText("A{$alt->nomor} - {$alt->nama}");
            $table->addCell(1500)->addText(number_format($skor, 6), null, $centerAlign);
            $table->addCell(1200)->addText($ket, ['bold' => $rank === 1], $centerAlign);

            $rank++;
        }

        // Stream response
        $filename = 'perhitungan-ahp-' . now()->format('Ymd-His') . '.docx';

        return response()->streamDownload(function () use ($phpWord) {
            $writer = IOFactory::createWriter($phpWord, 'Word2007');
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);
    }

    private function addMatrixTable(
        $section, string $title, $alternatifs, $kriterias,
        array $matrix, array $headerStyle, array $centerAlign,
        array $tableStyle, bool $isNumber = false, int $decimals = 6
    ): void {
        $section->addTitle($title, 2);
        $table = $section->addTable($tableStyle);

        $table->addRow();
        $table->addCell(500, ['bgColor' => '16a34a'])->addText('No', $headerStyle, $centerAlign);
        $table->addCell(2000, ['bgColor' => '16a34a'])->addText('Nama', $headerStyle, $centerAlign);
        foreach ($kriterias as $k) {
            $table->addCell(null, ['bgColor' => '16a34a'])->addText($k->nama, $headerStyle, $centerAlign);
        }

        foreach ($alternatifs as $i => $alt) {
            $table->addRow();
            $table->addCell(500)->addText($i + 1, null, $centerAlign);
            $table->addCell(2000)->addText("A{$alt->nomor} - {$alt->nama}");
            foreach ($kriterias as $k) {
                $val = $matrix[$alt->id][$k->id] ?? ($isNumber ? 0 : '—');
                $table->addCell(null)->addText(
                    $isNumber ? number_format((float)$val, $decimals) : $val,
                    null, $centerAlign
                );
            }
        }
        $section->addTextBreak(1);
    }
}