<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use App\Exports\MonitoringUsulanDosenExport;
use Maatwebsite\Excel\Facades\Excel;

class MonitoringUsulanDosenController extends Controller
{
  public function index(Request $request)
  {
    $bulanIndonesia = [
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

    $awal = max(1, min(12, (int) $request->input('awalPeriode', 1)));
    $akhir = max(1, min(12, (int) $request->input('akhirPeriode', now()->month)));

    //swap nilai
    if ($awal > $akhir) {
      [$awal, $akhir] = [$akhir, $awal];
    }

    $search = $request->filled('search') ? trim((string) $request->input('search')) : '';

    $allowedPerPage = [15, 25, 50, 100];
    $perPage = (int) $request->input('perPage', 15);
    if (!in_array($perPage, $allowedPerPage, true)) {
      $perPage = 15;
    }

    $currentPage = max(1, (int) $request->input('page', 1));
    $pageItems = $this->buildMonitoringPageInChunks($awal, $akhir, $bulanIndonesia, $search, $perPage, $currentPage);

    $dosenList = new Paginator(
      $pageItems,
      $perPage,
      $currentPage,
      [
        'path' => $request->url(),
        'query' => $request->query(),
      ]
    );

    return view('admin.monitoring-usulan-dosen', compact('dosenList', 'bulanIndonesia'));
  }

  public function exportExcel(Request $request)
  {
    $namaFile = 'monitoring_dosen_belum_usulan_' . now()->format('Ymd_His') . '.xlsx';
    return Excel::download(new MonitoringUsulanDosenExport($request), $namaFile);
  }

  private function buildMonitoringPageInChunks(
    int $awal,
    int $akhir,
    array $bulanIndonesia,
    string $search,
    int $perPage,
    int $currentPage
  ): array
  {
    $tahun = (string) session('tahun');

    $activePts = DB::table('a_pts')
      ->where('aktif', '1')
      ->pluck('kode_pts')
      ->filter()
      ->values()
      ->all();

    $query = DB::table('s_transaksi_2')
      ->select(
        's_transaksi_2.no as no',
        's_transaksi_2.NIDN',
        's_transaksi_2.NUPTK',
        's_transaksi_2.Nama',
        's_transaksi_2.Jenis',
        's_transaksi_2.Kode_PT',
        's_transaksi_2.PTS'
      )
      ->where('s_transaksi_2.Aktif', '1')
      ->where('s_transaksi_2.tahun_versi', $tahun);

    if (!empty($activePts)) {
      $query->whereIn('s_transaksi_2.Kode_PT', $activePts);
    } else {
      return [];
    }

    for ($i = $awal; $i <= $akhir; $i++) {
      $query->addSelect('s_transaksi_2.KodeUsulan' . $i);
    }

    if ($search !== '') {
      $query->where(function ($q) use ($search) {
        $q->where('s_transaksi_2.NIDN', 'like', "%{$search}%")
          ->orWhere('s_transaksi_2.NUPTK', 'like', "%{$search}%")
          ->orWhere('s_transaksi_2.Nama', 'like', "%{$search}%")
          ->orWhere('s_transaksi_2.PTS', 'like', "%{$search}%")
          ->orWhere('s_transaksi_2.Kode_PT', 'like', "%{$search}%");
      });
    }

    $offset = ($currentPage - 1) * $perPage;
    $need = $perPage + 1;
    $filteredSeen = 0;
    $pageRows = [];

    // Chunk per batch agar query per request tetap ringan, lalu berhenti saat data halaman sudah cukup.
    $query->orderBy('s_transaksi_2.no')->chunkById(200, function ($chunk) use (
      &$pageRows,
      &$filteredSeen,
      $awal,
      $akhir,
      $bulanIndonesia,
      $offset,
      $need
    ) {
      foreach ($chunk as $row) {
        $bulanKosong = [];

        for ($i = $awal; $i <= $akhir; $i++) {
          $kolom = 'KodeUsulan' . $i;
          $nilai = $row->{$kolom} ?? null;
          if ($nilai === null || $nilai === '') {
            $bulanKosong[] = $bulanIndonesia[$i];
          }
        }

        $jumlahBulanKosong = count($bulanKosong);
        if ($jumlahBulanKosong === 0) {
          continue;
        }

        if ($filteredSeen < $offset) {
          $filteredSeen++;
          continue;
        }

        $filteredSeen++;

        $pageRows[] = (object) [
          'NIDN' => $row->NIDN,
          'NUPTK' => $row->NUPTK,
          'Nama' => $row->Nama,
          'Jenis' => $row->Jenis,
          'Kode_PT' => $row->Kode_PT,
          'PTS' => $row->PTS,
          'bulan_belum_usulan' => $jumlahBulanKosong,
          'kode_belum_usulan' => implode(', ', $bulanKosong),
        ];

        if (count($pageRows) >= $need) {
          return false;
        }
      }

      return true;
    }, 's_transaksi_2.no', 'no');

      usort($pageRows, function ($a, $b) {
        if ($a->bulan_belum_usulan === $b->bulan_belum_usulan) {
          return strcmp((string) $a->Nama, (string) $b->Nama);
        }

        return $b->bulan_belum_usulan <=> $a->bulan_belum_usulan;
      });

    return $pageRows;
  }
}
