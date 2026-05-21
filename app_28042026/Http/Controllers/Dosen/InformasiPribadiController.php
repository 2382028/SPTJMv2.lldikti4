<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InformasiPribadiController extends Controller
{
  private function getTransaksiYearColumn(): string
  {
    static $cached = null;
    if ($cached) {
      return $cached;
    }

    $table = 's_transaksi_2';
    foreach (['Tahun_Versi', 'tahun_versi', 'Tahun_versi'] as $col) {
      try {
        if (Schema::hasColumn($table, $col)) {
          $cached = $col;
          return $cached;
        }
      } catch (\Throwable $e) {
        // ignore and fall back
      }
    }

    $cached = 'tahun_versi';
    return $cached;
  }

  public function show(Request $request)
  {
    $dosen = Auth::guard('dosen')->user();
    $nidn = trim((string) ($dosen->nidn ?? ''));
    $nuptk = trim((string) ($dosen->nuptk ?? ''));
    $identifier = $nidn !== '' ? $nidn : $nuptk;

    if ($identifier === '') {
      return redirect()->route('dosen.dashboard')->with('error', 'Akun dosen tidak memiliki NIDN/NUPTK.');
    }

    $tahunVersi = (int) (session('tahun') ?: 0);
    if ($tahunVersi <= 0) {
      return redirect()->route('dosen.dashboard')->with('error', 'Tahun versi tidak ditemukan di sesi. Silakan login ulang.');
    }

    $bulanSession = (int) session('bulan') ?: 12;
    $bulanSession = max(1, min(12, $bulanSession));

    $masaKerjaExpr = "NULLIF(Tahun{$bulanSession}, '')";
    $golonganExpr = "NULLIF(Gol{$bulanSession}, '')";
    $jabatanExpr = "NULLIF(Jabatan{$bulanSession}, '')";
    $gajiExpr = "NULLIF(Gaji{$bulanSession}, 0)";

    $yearColumn = $this->getTransaksiYearColumn();

    $dataDosen = Transaksi::where(function ($q) use ($identifier) {
      $q->where('NIDN', $identifier)
        ->orWhere('NUPTK', $identifier);
    })
      ->where($yearColumn, $tahunVersi)
      ->select(
        '*',
        DB::raw("{$masaKerjaExpr} AS masa_kerja"),
        DB::raw("{$golonganExpr} AS gol"),
        DB::raw("{$jabatanExpr} AS jabatan"),
        DB::raw("{$gajiExpr} AS gaji")
      )
      ->first();

    if (!$dataDosen) {
      return redirect()->route('dosen.dashboard')->with('error', 'Data dosen tidak ditemukan pada tahun versi aktif.');
    }

    return view('dosen.informasi-pribadi', [
      'dosen' => $dataDosen,
    ]);
  }
}
