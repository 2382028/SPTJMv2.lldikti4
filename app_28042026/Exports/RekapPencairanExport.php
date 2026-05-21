<?php

namespace App\Exports;

use App\Models\RekapPencairan;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapPencairanExport implements FromView, ShouldAutoSize
{
  protected $dataKeuangan, $bulanPendek, $prosesCair, $months, $pejabat, $showMonths;

  public function __construct($dataKeuangan, $prosesCair, $months, $bulanPendek, $pejabat, $showMonths = [])
  {
    $this->prosesCair = $prosesCair;
    $this->dataKeuangan = $dataKeuangan;
    $this->months = $months;
    $this->bulanPendek = $bulanPendek;
    $this->pejabat = $pejabat;
    $this->showMonths = $showMonths;
  }

  public function view(): View
  {
    return view('admin.export-pencairan', [
      'dataKeuangan' => $this->dataKeuangan,
      'prosesCair' => $this->prosesCair,
      'months' => $this->months,
      'bulanPendek' => $this->bulanPendek,
      'pejabat' => $this->pejabat,
      'showMonths' => $this->showMonths,
    ]);
  }
}
