<?php

namespace App\Exports;

use Barryvdh\DomPDF\Facade\Pdf;

class PerhitunganPdfExport
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
        $pdf = Pdf::loadView('exports.perhitungan-pdf', [
            'kriterias'        => $this->kriterias,
            'alternatifs'      => $this->alternatifs,
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
}
