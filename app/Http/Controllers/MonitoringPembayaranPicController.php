<?php

namespace App\Http\Controllers;

use App\Exports\MonitoringPembayaranExport;
use App\Helpers\SelisihBayar;
use App\Http\Controllers\MonitoringPembayaranController as AdminMonitoringPembayaranController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class MonitoringPembayaranPicController extends Controller
{
  private function picEmail(): string
  {
    $user = Auth::guard('web')->user();
    return trim((string) ($user->email ?? ''));
  }

  public function index()
  {
    $email = $this->picEmail();
    if ($email === '') {
      abort(403);
    }

    $years = DB::table('s_transaksi_2')
      ->select('tahun_versi')
      ->whereRaw('TRIM(`Pemegang_Wilayah`) = ?', [$email])
      ->distinct()
      ->orderBy('tahun_versi')
      ->pluck('tahun_versi')
      ->toArray();

    return view('pic.monitoring-pembayaran', compact('years'));
  }

  public function cari(Request $request)
  {
    $email = $this->picEmail();
    if ($email === '') {
      abort(403);
    }

    $nidn = trim((string) $request->input('nidn'));
    $startYear = $request->input('start_year');
    $endYear = $request->input('end_year');
    $selectedYear = $request->input('tahun_versi');

    $availableYears = DB::table('s_transaksi_2')
      ->select('tahun_versi')
      ->whereRaw('TRIM(`Pemegang_Wilayah`) = ?', [$email])
      ->distinct()
      ->orderBy('tahun_versi')
      ->pluck('tahun_versi')
      ->toArray();

    if (empty($availableYears)) {
      return redirect()->back()->with('error', 'Data tahun tidak tersedia.');
    }

    $years = $availableYears;

    if (empty($startYear)) {
      $startYear = $availableYears[0];
    }
    if (empty($endYear)) {
      $endYear = end($availableYears);
    }

    if ($startYear > $endYear) {
      $tmp = $startYear;
      $startYear = $endYear;
      $endYear = $tmp;
    }

    $startYearInt = (int) $startYear;
    $endYearInt = (int) $endYear;
    $yearsForNidn = [];
    for ($y = $startYearInt; $y <= $endYearInt; $y++) {
      $yearsForNidn[] = (string) $y;
    }

    $transaksi = DB::table('s_transaksi_2')
      ->where(function ($q) use ($nidn) {
        $q->where('nidn', $nidn)
          ->orWhere('NUPTK', $nidn);
      })
      ->whereRaw('TRIM(`Pemegang_Wilayah`) = ?', [$email])
      ->whereBetween('tahun_versi', [$startYear, $endYear])
      ->orderBy('tahun_versi', 'desc')
      ->first();

    if (!$transaksi) {
      return redirect()->back()->with('error', 'Data dengan NIDN tersebut tidak ditemukan untuk wilayah Anda.');
    }

    if (empty($selectedYear)) {
      $selectedYear = $startYear;
    }

    if (!empty($selectedYear) && is_string($selectedYear)) {
      $selectedYear = trim($selectedYear);
    }

    $transaksiTahun = null;
    if (!empty($selectedYear)) {
      $transaksiTahun = DB::table('s_transaksi_2')
        ->where(function ($q) use ($nidn) {
          $q->where('nidn', $nidn)
            ->orWhere('NUPTK', $nidn);
        })
        ->whereRaw('TRIM(`Pemegang_Wilayah`) = ?', [$email])
        ->where('tahun_versi', $selectedYear)
        ->first();
    }

    if (!$transaksiTahun && !empty($selectedYear) && (string) ($transaksi->tahun_versi ?? '') === (string) $selectedYear) {
      $transaksiTahun = $transaksi;
    }

    $selisihTotals = SelisihBayar::computeFromTransaksi($transaksiTahun);

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
      $golonganBulanan[] = $transaksiTahun ? ($transaksiTahun->{'Gol' . $suffix} ?? '-') : '-';
      $gajiBulanan[] = $transaksiTahun ? (float) ($transaksiTahun->{'Gaji' . $suffix} ?? 0) : 0;
      $tahunBulanan[] = $transaksiTahun ? ($transaksiTahun->{'Tahun' . $suffix} ?? '-') : '-';
      $kotorTpd[] = $transaksiTahun ? (float) ($transaksiTahun->{'TPD' . $suffix} ?? 0) : 0;
      $kotorTkgb[] = $transaksiTahun ? (float) ($transaksiTahun->{'TKGB' . $suffix} ?? 0) : 0;
      $pajakTpd[] = $transaksiTahun ? (float) ($transaksiTahun->{'nilaiPajakTPD' . $suffix} ?? 0) : 0;
      $pajakTkgb[] = $transaksiTahun ? (float) ($transaksiTahun->{'nilaiPajakTKGB' . $suffix} ?? 0) : 0;
      $bersihTpd[] = $transaksiTahun ? (float) ($transaksiTahun->{'bersihTPD' . $suffix} ?? 0) : 0;
      $bersihTkgb[] = $transaksiTahun ? (float) ($transaksiTahun->{'bersihTKGB' . $suffix} ?? 0) : 0;
      $noSp2d[] = $transaksiTahun ? ($transaksiTahun->{'No_sp2d_' . $suffix} ?? '-') : '-';
      $tglSp2d[] = $transaksiTahun ? ($transaksiTahun->{'Tgl_sp2d_' . $suffix} ?? '-') : '-';
    }

    $kodeUsulanBulanan = [];
    for ($i = 1; $i <= 12; $i++) {
      $kode = $transaksiTahun ? ($transaksiTahun->{'KodeUsulan' . $i} ?? null) : null;
      $kodeUsulanBulanan[] = $kode;
    }

    $bulanSession = (int) session('bulan') ?: 12;
    if ($bulanSession < 1 || $bulanSession > 12) {
      $bulanSession = 12;
    }
    $jabatanField = 'Jabatan' . $bulanSession;
    if ($transaksi) {
      $transaksi->JabatanSelected = $transaksi->{$jabatanField} ?? $transaksi->Jabatan12 ?? null;
    }

    return view(
      'pic.monitoring-pembayaran',
      compact(
        'years',
        'yearsForNidn',
        'startYear',
        'endYear',
        'selectedYear',
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
        'selisihTotals'
      )
    );
  }

  public function data(Request $request)
  {
    $email = $this->picEmail();
    if ($email === '') {
      abort(403);
    }

    $nidn = $request->input('nidn');
    $startYear = $request->input('start_year');
    $endYear = $request->input('end_year');
    $selectedYear = $request->input('tahun_versi');

    $availableYears = DB::table('s_transaksi_2')
      ->select('tahun_versi')
      ->whereRaw('TRIM(`Pemegang_Wilayah`) = ?', [$email])
      ->distinct()
      ->orderBy('tahun_versi')
      ->pluck('tahun_versi')
      ->toArray();

    if (empty($availableYears)) {
      return response()->json(['success' => false, 'message' => 'Data tahun tidak tersedia.']);
    }

    if (empty($startYear)) {
      $startYear = $availableYears[0];
    }
    if (empty($endYear)) {
      $endYear = end($availableYears);
    }

    if ($startYear > $endYear) {
      $tmp = $startYear;
      $startYear = $endYear;
      $endYear = $tmp;
    }

    if (empty($selectedYear)) {
      $selectedYear = $startYear;
    }

    $transaksi = DB::table('s_transaksi_2')
      ->where(function ($q) use ($nidn) {
        $q->where('nidn', $nidn)
          ->orWhere('NUPTK', $nidn);
      })
      ->whereRaw('TRIM(`Pemegang_Wilayah`) = ?', [$email])
      ->whereBetween('tahun_versi', [$startYear, $endYear])
      ->orderBy('tahun_versi', 'desc')
      ->first();

    if (!$transaksi) {
      return response()->json(['success' => false, 'message' => 'Data profil tidak ditemukan untuk wilayah Anda.']);
    }

    $transaksiTahun = DB::table('s_transaksi_2')
      ->where(function ($q) use ($nidn) {
        $q->where('nidn', $nidn)
          ->orWhere('NUPTK', $nidn);
      })
      ->whereRaw('TRIM(`Pemegang_Wilayah`) = ?', [$email])
      ->where('tahun_versi', $selectedYear)
      ->first();

    if (!$transaksiTahun && (string) ($transaksi->tahun_versi ?? '') === (string) $selectedYear) {
      $transaksiTahun = $transaksi;
    }

    $selisihTotals = SelisihBayar::computeFromTransaksi($transaksiTahun);

    $months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    $kodeUsulanBulanan = [];
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
      $s = $i;
      $kodeUsulanBulanan[] = $transaksiTahun ? ($transaksiTahun->{'KodeUsulan' . $s} ?? null) : null;
      $golonganBulanan[] = $transaksiTahun ? ($transaksiTahun->{'Gol' . $s} ?? '-') : '-';
      $gajiBulanan[] = $transaksiTahun ? (float) ($transaksiTahun->{'Gaji' . $s} ?? 0) : 0;
      $tahunBulanan[] = $transaksiTahun ? ($transaksiTahun->{'Tahun' . $s} ?? '-') : '-';
      $kotorTpd[] = $transaksiTahun ? (float) ($transaksiTahun->{'TPD' . $s} ?? 0) : 0;
      $kotorTkgb[] = $transaksiTahun ? (float) ($transaksiTahun->{'TKGB' . $s} ?? 0) : 0;
      $pajakTpd[] = $transaksiTahun ? (float) ($transaksiTahun->{'nilaiPajakTPD' . $s} ?? 0) : 0;
      $pajakTkgb[] = $transaksiTahun ? (float) ($transaksiTahun->{'nilaiPajakTKGB' . $s} ?? 0) : 0;
      $bersihTpd[] = $transaksiTahun ? (float) ($transaksiTahun->{'bersihTPD' . $s} ?? 0) : 0;
      $bersihTkgb[] = $transaksiTahun ? (float) ($transaksiTahun->{'bersihTKGB' . $s} ?? 0) : 0;
      $noSp2d[] = $transaksiTahun ? ($transaksiTahun->{'No_sp2d_' . $s} ?? '-') : '-';
      $tglSp2d[] = $transaksiTahun ? ($transaksiTahun->{'Tgl_sp2d_' . $s} ?? '-') : '-';
    }

    $totals = [
      'gaji' => array_sum($gajiBulanan),
      'kotorTpd' => array_sum($kotorTpd),
      'kotorTkgb' => array_sum($kotorTkgb),
      'pajakTpd' => array_sum($pajakTpd),
      'pajakTkgb' => array_sum($pajakTkgb),
      'bersihTpd' => array_sum($bersihTpd),
      'bersihTkgb' => array_sum($bersihTkgb),
    ];

    $header = [
      'NIDN' => $transaksi->NIDN ?? '',
      'Nama' => $transaksi->Nama ?? '',
      'JabatanSelected' => ($transaksiTahun && isset($transaksiTahun->{'Jabatan' . ((int) session('bulan') ?: 12)}) ? $transaksiTahun->{'Jabatan' . ((int) session('bulan') ?: 12)} : ($transaksi->Jabatan12 ?? '')),
      'Aktif' => $transaksi->Aktif ?? 0,
      'Kode_PT' => $transaksi->Kode_PT ?? '',
      'PTS' => $transaksi->PTS ?? '',
    ];

    return response()->json([
      'success' => true,
      'header' => $header,
      'selectedYear' => $selectedYear,
      'months' => $months,
      'kodeUsulanBulanan' => $kodeUsulanBulanan,
      'golonganBulanan' => $golonganBulanan,
      'tahunBulanan' => $tahunBulanan,
      'gajiBulanan' => $gajiBulanan,
      'kotorTpd' => $kotorTpd,
      'kotorTkgb' => $kotorTkgb,
      'pajakTpd' => $pajakTpd,
      'pajakTkgb' => $pajakTkgb,
      'bersihTpd' => $bersihTpd,
      'bersihTkgb' => $bersihTkgb,
      'noSp2d' => $noSp2d,
      'tglSp2d' => $tglSp2d,
      'totals' => $totals,
      'selisihTotals' => $selisihTotals,
    ]);
  }

  public function exportExcel(Request $request)
  {
    $email = $this->picEmail();
    if ($email === '') {
      abort(403);
    }

    $request->validate([
      'nidn' => ['required', 'string'],
      'tahun_versi' => ['required'],
    ]);

    $nidn = trim((string) $request->input('nidn'));
    $selectedYear = trim((string) $request->input('tahun_versi'));

    $transaksi = DB::table('s_transaksi_2')
      ->where(function ($q) use ($nidn) {
        $q->where('nidn', $nidn)
          ->orWhere('NUPTK', $nidn);
      })
      ->whereRaw('TRIM(`Pemegang_Wilayah`) = ?', [$email])
      ->where('tahun_versi', $selectedYear)
      ->first();

    if (!$transaksi) {
      return redirect()->back()->with('error', 'Data tidak ditemukan untuk wilayah Anda.');
    }

    $monthNames = [
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

    $rows = [];
    $totals = [
      'gaji' => 0,
      'kotorTpd' => 0,
      'kotorTkgb' => 0,
      'pajakTpd' => 0,
      'pajakTkgb' => 0,
      'bersihTpd' => 0,
      'bersihTkgb' => 0,
    ];

    for ($m = 1; $m <= 12; $m++) {
      $kodeUsulan = $transaksi?->{'KodeUsulan' . $m} ?? '-';
      $gol = $transaksi?->{'Gol' . $m} ?? '-';
      $tahun = $transaksi?->{'Tahun' . $m} ?? '-';

      $gaji = (float) ($transaksi?->{'Gaji' . $m} ?? 0);
      $kotorTpd = (float) ($transaksi?->{'TPD' . $m} ?? 0);
      $kotorTkgb = (float) ($transaksi?->{'TKGB' . $m} ?? 0);
      $pajakTpd = (float) ($transaksi?->{'nilaiPajakTPD' . $m} ?? 0);
      $pajakTkgb = (float) ($transaksi?->{'nilaiPajakTKGB' . $m} ?? 0);
      $bersihTpd = (float) ($transaksi?->{'bersihTPD' . $m} ?? 0);
      $bersihTkgb = (float) ($transaksi?->{'bersihTKGB' . $m} ?? 0);

      $noSp2d = $transaksi?->{'No_sp2d_' . $m} ?? '-';
      $tglSp2d = $transaksi?->{'Tgl_sp2d_' . $m} ?? '-';

      $totals['gaji'] += $gaji;
      $totals['kotorTpd'] += $kotorTpd;
      $totals['kotorTkgb'] += $kotorTkgb;
      $totals['pajakTpd'] += $pajakTpd;
      $totals['pajakTkgb'] += $pajakTkgb;
      $totals['bersihTpd'] += $bersihTpd;
      $totals['bersihTkgb'] += $bersihTkgb;

      $rows[] = [
        $selectedYear,
        $monthNames[$m],
        $kodeUsulan,
        $gol . ' - ' . $tahun,
        (int) round($gaji),
        (int) round($kotorTpd),
        (int) round($kotorTkgb),
        (int) round($pajakTpd),
        (int) round($pajakTkgb),
        (int) round($bersihTpd),
        (int) round($bersihTkgb),
        $noSp2d,
        $tglSp2d,
      ];
    }

    $selisihTotals = SelisihBayar::computeFromTransaksi($transaksi);

    $rows[] = [
      $selectedYear,
      'Jumlah',
      '-',
      '-',
      (int) round($totals['gaji']),
      (int) round($totals['kotorTpd']),
      (int) round($totals['kotorTkgb']),
      (int) round($totals['pajakTpd']),
      (int) round($totals['pajakTkgb']),
      (int) round($totals['bersihTpd']),
      (int) round($totals['bersihTkgb']),
      '-',
      '-',
    ];

    $rows[] = [
      $selectedYear,
      'Jumlah Selisih Bayar',
      '-',
      '-',
      '-',
      (int) round((float) ($selisihTotals['selisihTpd'] ?? 0)),
      (int) round((float) ($selisihTotals['selisihTkgb'] ?? 0)),
      (int) round((float) ($selisihTotals['selisihPajakTpd'] ?? 0)),
      (int) round((float) ($selisihTotals['selisihPajakTkgb'] ?? 0)),
      (int) round((float) ($selisihTotals['selisihBersihTpd'] ?? 0)),
      (int) round((float) ($selisihTotals['selisihBersihTkgb'] ?? 0)),
      '-',
      '-',
    ];

    $fileName = 'monitoring-pembayaran_' . $nidn . '_' . $selectedYear . '.xlsx';
    return Excel::download(new MonitoringPembayaranExport($rows), $fileName);
  }

  public function cetakSpt(Request $request)
  {
    $email = $this->picEmail();
    if ($email === '') {
      abort(403);
    }

    $request->validate([
      'nidn' => ['required', 'string'],
      'tahun_versi' => ['required'],
    ]);

    $nidn = trim((string) $request->input('nidn'));
    $tahunVersi = $request->input('tahun_versi') ?? $request->input('cetak_spt_tahun_versi') ?? $request->input('tahunVersi');
    $selectedYear = trim((string) $tahunVersi);

    $transaksi = DB::table('s_transaksi_2')
      ->where(function ($q) use ($nidn) {
        $q->where('nidn', $nidn)
          ->orWhere('NUPTK', $nidn);
      })
      ->whereRaw('TRIM(`Pemegang_Wilayah`) = ?', [$email])
      ->where('tahun_versi', $selectedYear)
      ->first();

    if (!$transaksi) {
      return redirect()->back()->with('error', 'Data tidak ditemukan untuk wilayah Anda.');
    }

    // Delegate PDF rendering to the existing Admin implementation.
    // Force nidn/tahun_versi to the validated values to avoid request tampering.
    $request->merge([
      'nidn' => $nidn,
      'tahun_versi' => $selectedYear,
      'cetak_spt_tahun_versi' => $selectedYear,
    ]);

    return app(AdminMonitoringPembayaranController::class)->cetakSpt($request);
  }
}
