<?php

namespace App\Exports;

use App\Models\Kriteria;
use App\Models\Alternatif;
use App\Models\KriteriaHasil;
use App\Models\SubKriteriaHasil;
use App\Models\Penilaian;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PerhitunganExport implements WithMultipleSheets
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

    public function sheets(): array
    {
        return [
            new Sheets\BobotKriteriaSheet($this->kriterias, $this->bobotKriteria),
            new Sheets\BobotSubKriteriaSheet($this->kriterias, $this->bobotSubKriteria),
            new Sheets\PenilaianSheet($this->kriterias, $this->alternatifs, $this->penilaianMatrix),
            new Sheets\MatriksKeputusanSheet($this->kriterias, $this->alternatifs, $this->nilaiMatrix),
            new Sheets\NilaiAtributSheet($this->kriterias, $this->alternatifs, $this->skorMatrix, $this->totalSkor, $this->bobotKriteria),
            new Sheets\RankingSheet($this->alternatifs, $this->totalSkor),
        ];
    }
}