<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class MonitoringDosenPtsController extends Controller
{
  public function index()
  {
    return view('pts.monitoring-dosen');
  }

  // public function cari(Request $request)
  // {
  //   $nidn = $request->input('nidn');

  //   // Ambil kode_pts dari user yang login sebagai PTS
  //   $kode_pts = Auth::guard('pts')->user()->kode_pts;

  //   // Filter berdasarkan NIDN dan hanya untuk PTS yang sedang login
  //   $transaksi = DB::table('s_transaksi')
  //     ->where('nidn', $nidn)
  //     ->where('kode_pt', $kode_pts)
  //     ->first();

  //   $dosen = DB::table('s_transaksi_2')
  //     ->where('nidn', $nidn)
  //     ->where('kode_pt', $kode_pts)
  //     ->first();

  //   if (!$transaksi || !$dosen) {
  //     return redirect()
  //       ->back()
  //       ->with('error', 'Data dengan NIDN tersebut tidak ditemukan atau tidak masuk dalam PTS Anda.');
  //   }

  //   $kodeUsulanBulanan = [];

  //   // Ambil kode usulan yang tidak kosong
  //   for ($i = 1; $i <= 12; $i++) {
  //     $kode = $transaksi->{'kode_usulan' . $i} ?? null;
  //     if ($kode) {
  //       $kodeUsulanBulanan[] = $kode;
  //     }
  //   }

  //   $jumlahKodeUsulan = count($kodeUsulanBulanan);

  //   // Ambil nilai dasar dari transaksi
  //   $nilaiTpd = $transaksi->tpd1 ?? 0;
  //   $nilaiTkgb = $transaksi->tkgb1 ?? 0;
  //   $nilaiPajakTpd = $transaksi->nilai_pajak_tpd_1 ?? 0;
  //   $nilaiPajakTkgb = $transaksi->nilai_pajak_tkgb_1 ?? 0;
  //   $nilaiBersihTpd = $transaksi->bersih_tpd_1 ?? 0;
  //   $nilaiBersihTkgb = $transaksi->bersih_tkgb_1 ?? 0;
  //   $nilaiSp2d = $transaksi->no_sp2d_1 ?? '-';
  //   $nilaitglSp2d = $transaksi->tgl_sp2d_1 ?? '-';

  //   // Buat array sesuai jumlah data kode usulan
  //   $kotorTpd = array_fill(0, $jumlahKodeUsulan, $nilaiTpd);
  //   $kotorTkgb = array_fill(0, $jumlahKodeUsulan, $nilaiTkgb);
  //   $pajakTpd = array_fill(0, $jumlahKodeUsulan, $nilaiPajakTpd);
  //   $pajakTkgb = array_fill(0, $jumlahKodeUsulan, $nilaiPajakTkgb);
  //   $bersihTpd = array_fill(0, $jumlahKodeUsulan, $nilaiBersihTpd);
  //   $bersihTkgb = array_fill(0, $jumlahKodeUsulan, $nilaiBersihTkgb);

  //   $noSp2d = array_fill(0, $jumlahKodeUsulan, $nilaiSp2d);
  //   $tglSp2d = array_fill(0, $jumlahKodeUsulan, $nilaitglSp2d);

  //   return view(
  //     'pts.monitoring-dosen',
  //     compact(
  //       'transaksi',
  //       'dosen',
  //       'nidn',
  //       'kodeUsulanBulanan',
  //       'noSp2d',
  //       'tglSp2d',
  //       'kotorTpd',
  //       'kotorTkgb',
  //       'pajakTpd',
  //       'pajakTkgb',
  //       'bersihTpd',
  //       'bersihTkgb'
  //     )
  //   );
  // }

  //new code (kode baru)
  public function cari(Request $request)
  {
    $identifier = $request->input('nidn');
    $tahunSekarang = date(session('tahun')); // Tahun saat ini
    $kode_pts = Auth::guard('pts')->user()->kode_pts;

    $transaksi = DB::table('s_transaksi_2')
      ->where(function ($q) use ($identifier) {
        $q->where('nidn', $identifier)
          ->orWhere('nuptk', $identifier);
      })
      ->where('tahun_versi', $tahunSekarang)
      ->where('kode_pt', $kode_pts)
      ->first();
    if (!$transaksi) {
      return redirect()
        ->back()
        ->with('error', 'Data dengan NIDN atau NUPTK tersebut tidak ditemukan untuk tahun ' . $tahunSekarang);
    }
    // Ambil data dari bulan 1–12
    $golonganBulanan = [];
    $gajiBulanan = [];
    $tahunBulanan = [];
    $kotorTpd = [];
    $kotorTkgb = [];
    $pajakTpd = [];
    $pajakTkgb = [];
    $bersihTpd = [];
    $bersihTkgb = [];
    $noSp2d = [];
    $tglSp2d = [];

    for ($i = 1; $i <= 12; $i++) {
      $suffix = $i;
      $golonganBulanan[] = $transaksi->{'Gol' . $suffix} ?? '-';
      $gajiBulanan[] = $transaksi->{'Gaji' . $suffix} ?? 0;
      $tahunBulanan[] = $transaksi->{'Tahun' . $suffix} ?? 0;
      $kotorTpd[] = $transaksi->{'TPD' . $suffix ?? 0};
      $kotorTkgb[] = $transaksi->{'TKGB' . $suffix ?? 0};
      $pajakTpd[] = $transaksi->{'nilaiPajakTPD' . $suffix ?? 0};
      $pajakTkgb[] = $transaksi->{'nilaiPajakTKGB' . $suffix ?? 0};
      $bersihTpd[] = $transaksi->{'bersihTPD' . $suffix ?? 0};
      $bersihTkgb[] = $transaksi->{'bersihTKGB' . $suffix ?? 0};
      $noSp2d[] = $transaksi->{'No_sp2d_' . $suffix ?? 0};
      $tglSp2d[] = $transaksi->{'Tgl_sp2d_' . $suffix ?? '-'};
    }

    // pick jabatan for selected month
    $bulan = (int) session('bulan');
    if ($bulan < 1 || $bulan > 12) $bulan = 12;
    $selectedJabatan = $transaksi->{'Jabatan' . $bulan} ?? ($transaksi->Jabatan12 ?? '-');



    $kodeUsulanBulanan = [];

    for ($i = 1; $i <= 12; $i++) {
      $kode = $transaksi->{'KodeUsulan' . $i} ?? null;
      $kodeUsulanBulanan[] = $kode;
    }

    // Provide nidn/nuptk variables for the view (compatibility)
    $nidn = $transaksi->NIDN ?? $transaksi->nidn ?? null;
    $nuptk = $transaksi->NUPTK ?? $transaksi->nuptk ?? null;

    return view(
      'pts.monitoring-dosen',
      compact(
        'transaksi',
        'nidn',
        'kodeUsulanBulanan',
        'noSp2d',
        'tglSp2d',
        'kotorTpd',
        'kotorTkgb',
        'pajakTpd',
        'pajakTkgb',
        'bersihTpd',
        'bersihTkgb',
        'golonganBulanan',
        'gajiBulanan',
        'tahunBulanan',
        'selectedJabatan',
      )
    );
  }
}
