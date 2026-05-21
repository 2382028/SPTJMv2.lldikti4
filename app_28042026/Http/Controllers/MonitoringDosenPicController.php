<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class MonitoringDosenPicController extends Controller
{
  public function index()
  {
    return view('pic.monitoring-dosen');
  }
  public function cari(Request $request)
  {
    $nidn = trim((string) $request->input('nidn'));
    $tahunSekarang = session('tahun') ?: date('Y');
    $pemegangWilayah = Auth::user()->email;

    $transaksi = DB::table('s_transaksi_2')
      ->where(function ($q) use ($nidn) {
        // support exact match and partial match for both NIDN and NUPTK
        $q->where('NIDN', $nidn)
          ->orWhere('NUPTK', $nidn)
          ->orWhere('NIDN', 'like', "%{$nidn}%")
          ->orWhere('NUPTK', 'like', "%{$nidn}%");
      })
      ->where('Tahun_Versi', $tahunSekarang)
      ->where('Pemegang_Wilayah', $pemegangWilayah)
      ->first();
    if (!$transaksi) {
      return redirect()
        ->back()
        ->with('error', 'Data dengan NIDN/NUPTK tersebut tidak ditemukan untuk tahun ' . $tahunSekarang);
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
      $golonganBulanan[] = $transaksi->{"Gol{$suffix}"} ?? '-';
      $gajiBulanan[] = $transaksi->{"Gaji{$suffix}"} ?? 0;
      $tahunBulanan[] = $transaksi->{"Tahun{$suffix}"} ?? 0;
      $kotorTpd[] = $transaksi->{"TPD{$suffix}"} ?? 0;
      $kotorTkgb[] = $transaksi->{"TKGB{$suffix}"} ?? 0;
      $pajakTpd[] = $transaksi->{"nilaiPajakTPD{$suffix}"} ?? 0;
      $pajakTkgb[] = $transaksi->{"nilaiPajakTKGB{$suffix}"} ?? 0;
      $bersihTpd[] = $transaksi->{"bersihTPD{$suffix}"} ?? 0;
      $bersihTkgb[] = $transaksi->{"bersihTKGB{$suffix}"} ?? 0;
      $noSp2d[] = $transaksi->{"No_sp2d_{$suffix}"} ?? '-';
      $tglSp2d[] = $transaksi->{"Tgl_sp2d_{$suffix}"} ?? '-';
    }



    $kodeUsulanBulanan = [];

    for ($i = 1; $i <= 12; $i++) {
      $kode = $transaksi->{"KodeUsulan{$i}"} ?? null;
      $kodeUsulanBulanan[] = $kode;
    }

    // Tentukan kolom Jabatan berdasarkan session('bulan') dan tambahkan atribut `jabatan` pada transaksi
    $bulanSession = (int) session('bulan') ?: 12;
    $jabCol = 'Jabatan' . $bulanSession;
    $jabVal = $transaksi->{$jabCol} ?? $transaksi->Jabatan12 ?? null;
    $transaksi->jabatan = $jabVal && trim((string) $jabVal) !== '' ? trim((string) $jabVal) : '-';

    return view(
      'pic.monitoring-dosen',
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
      )
    );
  }
}
