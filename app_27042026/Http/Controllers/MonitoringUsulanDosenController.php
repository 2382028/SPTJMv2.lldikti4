<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\MonitoringUsulanDosenExport;
use App\Models\Transaksi;
use Maatwebsite\Excel\Facades\Excel;

class MonitoringUsulanDosenController extends Controller
{
  public function index(Request $request)
  {
    $tahunSekarang = now()->year;

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

    $awal = (int) $request->input('awalPeriode', 1);
    $akhir = (int) $request->input('akhirPeriode', now()->month);

    //swap nilai
    if ($awal > $akhir) {
      [$awal, $akhir] = [$akhir, $awal];
    }

    $concatKodeBelumUsulan = [];
    $concatBulanBelumUsulan = [];
    for ($i = $awal; $i <= $akhir; $i++) {
      $namaBulan = $bulanIndonesia[$i];
      $concatKodeBelumUsulan[] = "MAX(CASE WHEN KodeUsulan$i IS NULL THEN '$namaBulan' END)";
      $concatBulanBelumUsulan[] = "SUM(IF(KodeUsulan$i IS NULL, 1, 0))";
    }
    $kodeBelumUsulan = implode(',', $concatKodeBelumUsulan);
    $bulanBelumUsulan = implode("+", $concatBulanBelumUsulan);
    $baseQuery = DB::Table('s_transaksi_2')
      ->join('a_pts', 's_transaksi_2.Kode_PT', '=', 'a_pts.kode_pts')
      ->select(
        'NIDN',
        'NUPTK',
        'Nama',
        'Jenis',
        'Kode_PT',
        'PTS',
        DB::raw("($bulanBelumUsulan)" . " as bulan_belum_usulan"),
        DB::raw("CONCAT_WS(', ',$kodeBelumUsulan)" . " as kode_belum_usulan")
      )
      ->where('s_transaksi_2.Aktif', '1')
      ->where('a_pts.aktif', '1')
      ->where('s_transaksi_2.tahun_versi', session('tahun'));

    // Server-side search/filtering (supports pagination)
    if ($request->filled('search')) {
      $term = trim($request->input('search'));
      $baseQuery->where(function ($q) use ($term) {
        $q->where('NIDN', 'like', "%{$term}%")
          ->orWhere('NUPTK', 'like', "%{$term}%")
          ->orWhere('Nama', 'like', "%{$term}%")
          ->orWhere('PTS', 'like', "%{$term}%")
          ->orWhere('Kode_PT', 'like', "%{$term}%");
      });
    }

    $perPage = (int) $request->input('perPage', 15);

    $dosenList = $baseQuery
      ->groupBy("NIDN", "NUPTK", "Nama", "Jenis", "Kode_PT", "PTS")
      ->havingRaw("bulan_belum_usulan > 0")
      ->orderBy('bulan_belum_usulan', 'DESC')
      ->paginate($perPage)
      ->appends(request()->query());
    // dd($dosenList);

    return view('admin.monitoring-usulan-dosen', compact('dosenList', 'bulanIndonesia'));
  }

  public function exportExcel(Request $request)
  {
    $namaFile = 'monitoring_dosen_belum_usulan_' . now()->format('Ymd_His') . '.xlsx';
    return Excel::download(new MonitoringUsulanDosenExport($request), $namaFile);
  }
}
