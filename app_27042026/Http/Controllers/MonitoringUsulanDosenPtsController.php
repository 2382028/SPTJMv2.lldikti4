<?php

namespace App\Http\Controllers;

use App\Exports\MonitoringUsulanDosenExport;
use Carbon\Carbon;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class MonitoringUsulanDosenPtsController extends Controller
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

    $awal = (int) $request->input('awalPeriode', 1);
    $akhir = (int) $request->input('akhirPeriode', now()->month);
    if ($awal > $akhir) {
      [$awal, $akhir] = [$akhir, $awal];
    }

    // Ambil kode_pts dari user login
    if (Auth::guard('pts')->check()) {
      $kodePtsLogin = Auth::guard('pts')->user()->kode_pts;
    } elseif (Auth::guard('web')->check()) {
      // fallback untuk admin/pic jika diperlukan
      $kodePtsLogin = Auth::user()->email;
    } else {
      $kodePtsLogin = null;
    }

    if (!$kodePtsLogin) {
      return redirect()
        ->back()
        ->with('error', 'Tidak terautentikasi.');
    }

    //kodelama
    // SQL dinamis untuk cek bulan yang belum ada usulan
    // $jumlahBulanNull = [];
    // $kodeConcatParts = [];

    // for ($i = $awal; $i <= $akhir; $i++) {
    //   $jumlahBulanNull[] = "SUM(IF(t.kode_usulan$i IS NULL, 1, 0))";

    //   $bulanNama = $bulanIndonesia[$i]; // Ambil nama bulan dari array
    //   $kodeConcatParts[] = "CASE WHEN COUNT(t.kode_usulan$i) = 0 THEN '$bulanNama' ELSE NULL END";
    // }

    // $nullCheckSQL = implode(' + ', $jumlahBulanNull);
    // $kodeConcatSQL = "CONCAT_WS(', ', " . implode(', ', $kodeConcatParts) . ')';

    // $dosenList = DB::table('s_transaksi_2 as d')
    //   ->leftJoin('s_transaksi as t', 'd.nidn', '=', 't.nidn')
    //   ->select(
    //     'd.nidn',
    //     'd.nama',
    //     'd.jenis',
    //     'd.kode_pt',
    //     'd.pts',
    //     DB::raw("($nullCheckSQL) as bulan_belum_usulan"),
    //     DB::raw("($kodeConcatSQL) as kode_belum_usulan")
    //   )
    //   ->where('d.aktif', 1)
    //   ->where('d.kode_pt', $kodePtsLogin)
    //   ->groupBy('d.nidn', 'd.nama', 'd.jenis', 'd.kode_pt', 'd.pts')
    //   ->havingRaw("($nullCheckSQL) > 0") // <-- Tambahkan ini!
    //   ->orderByDesc(DB::raw("($nullCheckSQL)"))
    //   ->get();


    for ($i = $awal; $i <= $akhir; $i++) {
      $namaBulan = $bulanIndonesia[$i];
      $concatKodeBelumUsulan[] = "MAX(CASE WHEN KodeUsulan$i IS NULL THEN '$namaBulan' END)";
      $concatBulanBelumUsulan[] = "SUM(IF(KodeUsulan$i IS NULL, 1, 0))";
    }
    $kodeBelumUsulan = implode(',', $concatKodeBelumUsulan);
    $bulanBelumUsulan = implode("+", $concatBulanBelumUsulan);
    $query = DB::Table('s_transaksi_2')
      ->select(
        'NIDN',
        'NUPTK',
        'Nama',
        'Jenis',
        'Kode_PT',
        'PTS',
        DB::raw("($bulanBelumUsulan) as bulan_belum_usulan"),
        DB::raw("CONCAT_WS(', ',$kodeBelumUsulan) as kode_belum_usulan")
      )
      ->where('aktif', '1')
      ->where('kode_pt', $kodePtsLogin)
      ->where('tahun_versi', session('tahun'));

    // Jika ada parameter pencarian, cari di NIDN atau NUPTK
    if ($request->has('search') && trim($request->input('search')) !== '') {
      $s = $request->input('search');
      $query->where(function ($q) use ($s) {
        $q->where('NIDN', 'like', "%{$s}%")
          ->orWhere('NUPTK', 'like', "%{$s}%");
      });
    }

    $dosenList = $query
      ->groupBy("NIDN", "NUPTK", "Nama", "Jenis", "Kode_PT", "PTS")
      ->havingRaw("bulan_belum_usulan > 0")
      ->orderBy('bulan_belum_usulan', 'DESC')
      ->get();
    return view('pts.monitoring-usulan-dosen', compact('dosenList', 'bulanIndonesia'));
  }

  public function exportExcelMonitoringDosenUsulan(Request $request)
  {
    $tgl = Carbon::now()->format('Ymd_His');
    $export =  Excel::download(new MonitoringUsulanDosenExport($request), 'monitoring-belum-usulan-' . $tgl . '.xlsx');
    return $export;
  }
}