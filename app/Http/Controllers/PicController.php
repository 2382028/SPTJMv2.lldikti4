<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PicController extends Controller
{
  public function index()
  {
    // prefer year stored in session (consistent with other pages), fallback to current year
    $year = session('tahun', Carbon::now()->year);
    $pemegangWilayah = Auth::user()->email;
    $jumlahDosen = DB::table('s_transaksi_2')
      ->where('pemegang_wilayah', $pemegangWilayah)
      ->where('tahun_versi', $year)
      ->count();
    $jumlahDosenPNSAktif = DB::table('s_transaksi_2')
      ->where('pemegang_wilayah', $pemegangWilayah)
      ->where('jenis', 'PNS')
      ->where('aktif', 1)
      ->where('Tahun_Versi', $year)
      ->count();

    $jumlahDosenPNSTidakAktif = DB::table('s_transaksi_2')
      ->where('pemegang_wilayah', $pemegangWilayah)
      ->where('jenis', 'PNS')
      ->where('aktif', 0)
      ->where('Tahun_Versi', $year)
      ->count();

    $jumlahDosenPNS = DB::table('s_transaksi_2')
      ->where('pemegang_wilayah', $pemegangWilayah)
      ->where('jenis', 'PNS')
      ->where('tahun_versi', $year)
      ->count();
    $jumlahDosenNonPNSAktif = DB::table('s_transaksi_2')
      ->where('pemegang_wilayah', $pemegangWilayah)
      ->where('jenis', 'NON PNS')
      ->where('Tahun_Versi', $year)
      ->where('aktif', 1)
      ->count();

    $jumlahDosenNonPNSTidakAktif = DB::table('s_transaksi_2')
      ->where('jenis', 'NON PNS')
      ->where('Tahun_Versi', $year)
      ->where('aktif', 0)
      ->where('pemegang_wilayah', $pemegangWilayah)
      ->count();

    $jumlahDosenNonPNS = DB::table('s_transaksi_2')
      ->where('pemegang_wilayah', $pemegangWilayah)
      ->where('jenis', 'NON PNS')
      ->where('Tahun_Versi', $year)
      ->count();

    return view(
      'pic.dashboard',
      compact(
        'jumlahDosen',
        'jumlahDosenPNSAktif',
        'jumlahDosenPNSTidakAktif',
        'jumlahDosenPNS',
        'jumlahDosenNonPNSAktif',
        'jumlahDosenNonPNSTidakAktif',
        'jumlahDosenNonPNS'
      )
    );
  }
}
