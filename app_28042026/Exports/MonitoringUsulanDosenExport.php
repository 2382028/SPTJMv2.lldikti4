<?php

namespace App\Exports;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithCustomChunkSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class MonitoringUsulanDosenExport implements FromQuery, WithHeadings, WithMapping, WithCustomChunkSize
{
  protected $request;
  protected $awal;
  protected $akhir;
  protected $bulanIndonesia;

  public function __construct(Request $request)
  {
    $this->request = $request;

    $this->awal = max(1, min(12, (int) $this->request->input('awalPeriode', 1)));
    $this->akhir = max(1, min(12, (int) $this->request->input('akhirPeriode', now()->month)));
    if ($this->awal > $this->akhir) {
      [$this->awal, $this->akhir] = [$this->akhir, $this->awal];
    }

    $this->bulanIndonesia = [
      1 => 'Januari',
      2 => 'Februari',
      3 => 'Maret',
      4 => 'April',
      5 => 'Mei',
      6 => 'Juni',
      7 => 'Juli',
      8 => 'Agustus',
      9 => 'September',
      10 => 'Oktober',
      11 => 'November',
      12 => 'Desember',
    ];
  }

  public function query()
  {
    $activePts = DB::table('a_pts')
      ->where('aktif', '1')
      ->pluck('kode_pts')
      ->filter()
      ->values()
      ->all();

    if (empty($activePts)) {
      return DB::table('s_transaksi_2')->whereRaw('1 = 0');
    }

    $query = DB::table('s_transaksi_2')
      ->select(
        'no',
        'NIDN',
        'NUPTK',
        'Nama',
        'Jenis',
        'Kode_PT',
        'PTS'
      )
      ->where('Aktif', '1')
      ->where('tahun_versi', session('tahun'))
      ->whereIn('Kode_PT', $activePts);

    for ($i = $this->awal; $i <= $this->akhir; $i++) {
      $query->addSelect('KodeUsulan' . $i);
    }

    $query->where(function ($q) {
      for ($i = $this->awal; $i <= $this->akhir; $i++) {
        $kolom = 'KodeUsulan' . $i;
        $q->orWhereNull($kolom)
          ->orWhere($kolom, '');
      }
    });

    if (Auth::guard('pts')->check()) {
      $ptsUser = Auth::guard('pts')->user();
      if (!empty($ptsUser->kode_pts)) {
        $query->where('Kode_PT', $ptsUser->kode_pts);
      }
    } elseif (Auth::check()) {
      $webUser = Auth::user();
      if (isset($webUser->role) && $webUser->role !== 'admin' && !empty($webUser->email)) {
        $query->where('pemegang_wilayah', $webUser->email);
      }
    }

    if ($this->request->filled('search')) {
      $search = trim((string) $this->request->input('search'));
      $query->where(function ($q) use ($search) {
        $q->where('NIDN', 'like', '%' . $search . '%')
          ->orWhere('NUPTK', 'like', '%' . $search . '%')
          ->orWhere('Nama', 'like', '%' . $search . '%')
          ->orWhere('PTS', 'like', '%' . $search . '%')
          ->orWhere('Kode_PT', 'like', '%' . $search . '%');
      });
    }

    return $query->orderBy('no');
  }

  public function headings(): array
  {
    return ['NIDN', 'NUPTK', 'Nama', 'Jenis', 'Kode PT', 'PTS', 'Bulan Belum Usulan', 'Kode Belum Usulan'];
  }

  public function map($row): array
  {
    $bulanKosong = [];
    for ($i = $this->awal; $i <= $this->akhir; $i++) {
      $kolom = 'KodeUsulan' . $i;
      $nilai = $row->{$kolom} ?? null;
      if ($nilai === null || $nilai === '') {
        $bulanKosong[] = $this->bulanIndonesia[$i];
      }
    }

    return [
      $row->NIDN,
      $row->NUPTK ?: '-',
      $row->Nama,
      $row->Jenis,
      $row->Kode_PT,
      $row->PTS,
      count($bulanKosong),
      implode(', ', $bulanKosong),
    ];
  }

  public function chunkSize(): int
  {
    return 200;
  }
}