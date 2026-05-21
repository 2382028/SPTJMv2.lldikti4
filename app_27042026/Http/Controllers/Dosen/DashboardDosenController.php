<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Helpers\ActiveYears;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardDosenController extends Controller
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
        // ignore schema lookup errors and fall back
      }
    }

    $cached = 'tahun_versi';
    return $cached;
  }

  private function resolveDosenIdentifier($dosen): string
  {
    $nidn = trim((string) ($dosen->nidn ?? ''));
    if ($nidn !== '') {
      return $nidn;
    }

    $nuptk = trim((string) ($dosen->nuptk ?? ''));
    return $nuptk;
  }

  private function pickLatestNonEmptyField($transaksi, string $prefix, int $max = 12): ?string
  {
    if (!$transaksi) {
      return null;
    }

    for ($i = $max; $i >= 1; $i--) {
      $field = $prefix . $i;
      $val = trim((string) ($transaksi->{$field} ?? ''));
      if ($val !== '') {
        return $val;
      }
    }

    return null;
  }

  public function index(Request $request)
  {
    $dosen = Auth::guard('dosen')->user();
    $nidnOrNuptk = $this->resolveDosenIdentifier($dosen);

    $yearColumn = $this->getTransaksiYearColumn();

    $activeYears = ActiveYears::load();
    $yearsForDropdown = !empty($activeYears)
      ? array_map('strval', $activeYears)
      : [(string) date('Y')];

    $selectedYear = trim((string) $request->input('tahun_versi'));
    if ($selectedYear === '') {
      $selectedYear = trim((string) session('tahun'));
    }
    if ($selectedYear === '' && !empty($yearsForDropdown)) {
      $selectedYear = end($yearsForDropdown);
    }

    // Guard against invalid selected year
    if ($selectedYear !== '' && !empty($yearsForDropdown) && !in_array($selectedYear, $yearsForDropdown, true)) {
      $selectedYear = end($yearsForDropdown);
    }

    $transaksi = null;
    $errorMessage = null;

    if ($nidnOrNuptk === '') {
      $errorMessage = 'Akun dosen tidak memiliki NIDN/NUPTK.';
    } else {
      $query = DB::table('s_transaksi_2')
        ->where(function ($q) use ($nidnOrNuptk) {
          $q->whereRaw('TRIM(`NIDN`) = ?', [$nidnOrNuptk])
            ->orWhereRaw('TRIM(`NUPTK`) = ?', [$nidnOrNuptk]);
        });

      if ($selectedYear !== '') {
        $query->where($yearColumn, $selectedYear);
      }

      // Prefer latest record if year isn't available or doesn't match
      $transaksi = $query
        ->orderBy($yearColumn, 'desc')
        ->orderBy('No', 'desc')
        ->first();

      if (!$transaksi) {
        $errorMessage = 'Data dosen tidak ditemukan pada tabel transaksi.';
      } elseif ($selectedYear === '') {
        $selectedYear = (string) ($transaksi->{$yearColumn} ?? '');
      }
    }

    $totals = [
      'gaji' => 0,
      'kotorTpd' => 0,
      'kotorTkgb' => 0,
      'pajakTpd' => 0,
      'pajakTkgb' => 0,
      'bersihTpd' => 0,
      'bersihTkgb' => 0,
      'sp2dCount' => 0,
    ];

    for ($m = 1; $m <= 12; $m++) {
      $gaji = (float) ($transaksi?->{'Gaji' . $m} ?? 0);
      $kotorTpd = (float) ($transaksi?->{'TPD' . $m} ?? 0);
      $kotorTkgb = (float) ($transaksi?->{'TKGB' . $m} ?? 0);
      $pajakTpd = (float) ($transaksi?->{'nilaiPajakTPD' . $m} ?? 0);
      $pajakTkgb = (float) ($transaksi?->{'nilaiPajakTKGB' . $m} ?? 0);
      $bersihTpd = (float) ($transaksi?->{'bersihTPD' . $m} ?? 0);
      $bersihTkgb = (float) ($transaksi?->{'bersihTKGB' . $m} ?? 0);

      $noSp2d = trim((string) ($transaksi?->{'No_sp2d_' . $m} ?? ''));
      $tglSp2d = trim((string) ($transaksi?->{'Tgl_sp2d_' . $m} ?? ''));
      $kodeUsulan = trim((string) ($transaksi?->{'KodeUsulan' . $m} ?? ''));
      $gol = trim((string) ($transaksi?->{'Gol' . $m} ?? ''));
      $tahunGol = trim((string) ($transaksi?->{'Tahun' . $m} ?? ''));

      $totals['gaji'] += $gaji;
      $totals['kotorTpd'] += $kotorTpd;
      $totals['kotorTkgb'] += $kotorTkgb;
      $totals['pajakTpd'] += $pajakTpd;
      $totals['pajakTkgb'] += $pajakTkgb;
      $totals['bersihTpd'] += $bersihTpd;
      $totals['bersihTkgb'] += $bersihTkgb;
      if ($noSp2d !== '' && $noSp2d !== '-') {
        $totals['sp2dCount']++;
      }
    }

    $jabatanTerakhir = $this->pickLatestNonEmptyField($transaksi, 'Jabatan');
    $golTerakhir = $this->pickLatestNonEmptyField($transaksi, 'Gol');
    $masaKerjaTerakhir = $this->pickLatestNonEmptyField($transaksi, 'Tahun');
    $noRekening = trim((string) ($transaksi?->No_Rekening ?? ''));

    return view('dosen.dashboard', [
      'dosen' => $dosen,
      'nidnOrNuptk' => $nidnOrNuptk,
      'yearColumn' => $yearColumn,
      'yearsForDosen' => $yearsForDropdown,
      'selectedYear' => $selectedYear,
      'transaksi' => $transaksi,
      'jabatanTerakhir' => $jabatanTerakhir,
      'golTerakhir' => $golTerakhir,
      'masaKerjaTerakhir' => $masaKerjaTerakhir,
      'noRekening' => $noRekening,
      'totals' => $totals,
      'errorMessage' => $errorMessage,
    ]);
  }

  /**
   * AJAX endpoint to return the summary card for a selected year.
   */
  public function summary(Request $request)
  {
    $dosen = Auth::guard('dosen')->user();
    if (!$dosen) {
      return response()->json(['html' => ''], 401);
    }

    $nidnOrNuptk = $this->resolveDosenIdentifier($dosen);
    $yearColumn = $this->getTransaksiYearColumn();
    $selectedYear = trim((string) $request->input('tahun_versi'));
    if ($selectedYear === '') {
      $selectedYear = trim((string) session('tahun')) ?: (string) date('Y');
    }

    $transaksi = null;
    if ($nidnOrNuptk !== '') {
      $transaksi = DB::table('s_transaksi_2')
        ->where(function ($q) use ($nidnOrNuptk) {
          $q->whereRaw('TRIM(`NIDN`) = ?', [$nidnOrNuptk])
            ->orWhereRaw('TRIM(`NUPTK`) = ?', [$nidnOrNuptk]);
        })
        ->where($yearColumn, $selectedYear)
        ->first();
    }

    $totals = [
      'gaji' => 0,
      'kotorTpd' => 0,
      'kotorTkgb' => 0,
      'pajakTpd' => 0,
      'pajakTkgb' => 0,
      'bersihTpd' => 0,
      'bersihTkgb' => 0,
      'sp2dCount' => 0,
    ];

    if ($transaksi) {
      for ($m = 1; $m <= 12; $m++) {
        $gaji = (float) ($transaksi->{'Gaji' . $m} ?? 0);
        $kotorTpd = (float) ($transaksi->{'TPD' . $m} ?? 0);
        $kotorTkgb = (float) ($transaksi->{'TKGB' . $m} ?? 0);
        $pajakTpd = (float) ($transaksi->{'nilaiPajakTPD' . $m} ?? 0);
        $pajakTkgb = (float) ($transaksi->{'nilaiPajakTKGB' . $m} ?? 0);
        $bersihTpd = (float) ($transaksi->{'bersihTPD' . $m} ?? 0);
        $bersihTkgb = (float) ($transaksi->{'bersihTKGB' . $m} ?? 0);
        $noSp2d = trim((string) ($transaksi->{'No_sp2d_' . $m} ?? ''));

        $totals['gaji'] += $gaji;
        $totals['kotorTpd'] += $kotorTpd;
        $totals['kotorTkgb'] += $kotorTkgb;
        $totals['pajakTpd'] += $pajakTpd;
        $totals['pajakTkgb'] += $pajakTkgb;
        $totals['bersihTpd'] += $bersihTpd;
        $totals['bersihTkgb'] += $bersihTkgb;
        if ($noSp2d !== '' && $noSp2d !== '-') {
          $totals['sp2dCount']++;
        }
      }
    }

    $html = view('dosen.partials.year-summary', [
      'selectedYear' => $selectedYear,
      'totals' => $totals,
      'transaksi' => $transaksi,
    ])->render();

    return response()->json([
      'html' => $html,
      'selectedYear' => $selectedYear,
      'totals' => $totals,
    ]);
  }
}
