<?php

namespace App\Http\Controllers\Dosen;

use App\Exports\MonitoringPembayaranExport;
use App\Helpers\SelisihBayar;
use App\Http\Controllers\MonitoringPembayaranController as AdminMonitoringPembayaranController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class MonitoringPembayaranDosenController extends Controller
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

  private function getDefaultYear(): ?string
  {
    $tahun = trim((string) session('tahun'));
    if ($tahun === '') {
      return null;
    }

    $tahunInt = (int) $tahun;
    return $tahunInt > 0 ? (string) $tahunInt : null;
  }

  public function index(Request $request)
  {
    $years = $this->getAvailableYears();

    $defaultYear = $this->getDefaultYear();
    $startYear = $request->input('start_year') ?? $defaultYear;
    $endYear = $request->input('end_year') ?? $defaultYear;
    $selectedYear = $request->input('tahun_versi') ?? $defaultYear;

    return $this->renderMonitoring($years, $startYear, $endYear, $selectedYear);
  }

  public function cari(Request $request)
  {
    $request->validate([
      'start_year' => ['nullable'],
      'end_year' => ['nullable'],
      'tahun_versi' => ['nullable'],
    ]);

    $years = $this->getAvailableYears();

    $defaultYear = $this->getDefaultYear();
    $startYear = $request->input('start_year') ?? $defaultYear;
    $endYear = $request->input('end_year') ?? $defaultYear;
    $selectedYear = $request->input('tahun_versi') ?? $defaultYear;

    return $this->renderMonitoring($years, $startYear, $endYear, $selectedYear);
  }

  public function table(Request $request)
  {
    $request->validate([
      'start_year' => ['nullable'],
      'end_year' => ['nullable'],
      'tahun_versi' => ['required'],
    ]);

    $years = $this->getAvailableYears();

    $data = $this->buildMonitoringData(
      $years,
      $request->input('start_year'),
      $request->input('end_year'),
      $request->input('tahun_versi')
    );

    $html = view('dosen.partials.monitoring-pembayaran-table', $data)->render();
    return response()->json([
      'html' => $html,
      'selectedYear' => $data['selectedYear'] ?? null,
      'errorMessage' => $data['errorMessage'] ?? null,
    ]);
  }

  public function exportExcel(Request $request)
  {
    $request->validate([
      'tahun_versi' => ['required'],
    ]);

    $dosen = Auth::guard('dosen')->user();
    $nidnOrNuptk = $this->resolveDosenIdentifier($dosen);
    $selectedYear = trim((string) $request->input('tahun_versi'));

    $allowedYears = $this->getAvailableYears();
    if (!empty($allowedYears) && !in_array($selectedYear, array_map('strval', $allowedYears), true)) {
      return redirect()->back()->with('error', 'Tahun versi tidak valid.');
    }

    if ($nidnOrNuptk === '') {
      return redirect()->back()->with('error', 'Akun dosen tidak memiliki NIDN/NUPTK.');
    }

    $yearColumn = $this->getTransaksiYearColumn();

    $transaksi = DB::table('s_transaksi_2')
      ->where(function ($q) use ($nidnOrNuptk) {
        $q->whereRaw('TRIM(`NIDN`) = ?', [$nidnOrNuptk])
          ->orWhereRaw('TRIM(`NUPTK`) = ?', [$nidnOrNuptk]);
      })
      ->where($yearColumn, $selectedYear)
      ->first();

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

    $fileName = 'monitoring-pembayaran_' . $nidnOrNuptk . '_' . $selectedYear . '.xlsx';
    return Excel::download(new MonitoringPembayaranExport($rows), $fileName);
  }

  public function cetakSpt(Request $request)
  {
    $request->validate([
      'tahun_versi' => ['required'],
    ]);

    $dosen = Auth::guard('dosen')->user();
    $nidnOrNuptk = $this->resolveDosenIdentifier($dosen);

    if ($nidnOrNuptk === '') {
      return redirect()->back()->with('error', 'Akun dosen tidak memiliki NIDN/NUPTK.');
    }

    $tahunVersi = $request->input('tahun_versi') ?? $request->input('cetak_spt_tahun_versi') ?? $request->input('tahunVersi');
    $selectedYear = trim((string) $tahunVersi);

    $allowedYears = $this->getAvailableYears();
    if (!empty($allowedYears) && !in_array($selectedYear, array_map('strval', $allowedYears), true)) {
      return redirect()->back()->with('error', 'Tahun versi tidak valid.');
    }

    $yearColumn = $this->getTransaksiYearColumn();
    $transaksi = DB::table('s_transaksi_2')
      ->where(function ($q) use ($nidnOrNuptk) {
        $q->whereRaw('TRIM(`NIDN`) = ?', [$nidnOrNuptk])
          ->orWhereRaw('TRIM(`NUPTK`) = ?', [$nidnOrNuptk]);
      })
      ->where($yearColumn, $selectedYear)
      ->first();

    if (!$transaksi) {
      return redirect()->back()->with('error', 'Data pembayaran tidak ditemukan untuk akun dosen ini pada tahun yang dipilih.');
    }

    // Delegate PDF rendering to the existing Admin implementation.
    $request->merge([
      'nidn' => $nidnOrNuptk,
      'tahun_versi' => $selectedYear,
      'cetak_spt_tahun_versi' => $selectedYear,
    ]);

    return app(AdminMonitoringPembayaranController::class)->cetakSpt($request);
  }

  private function getAvailableYears(): array
  {
    // Primary source: storage/app/active_years.json
    try {
      if (Storage::disk('local')->exists('active_years.json')) {
        $raw = Storage::disk('local')->get('active_years.json');
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
          $years = [];
          foreach ($decoded as $y) {
            $yInt = (int) $y;
            if ($yInt > 0) {
              $years[] = (string) $yInt;
            }
          }
          $years = array_values(array_unique($years));
          sort($years);
          if (!empty($years)) {
            return $years;
          }
        }
      }
    } catch (\Throwable $e) {
      // ignore and fall back
    }

    // Fallback: distinct years from DB
    $yearColumn = $this->getTransaksiYearColumn();
    return DB::table('s_transaksi_2')
      ->select($yearColumn)
      ->distinct()
      ->orderBy($yearColumn)
      ->pluck($yearColumn)
      ->map(fn($v) => (string) $v)
      ->toArray();
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

  private function renderMonitoring(array $years, $startYear, $endYear, $selectedYear)
  {
    $data = $this->buildMonitoringData($years, $startYear, $endYear, $selectedYear);
    return view('dosen.monitoring-pembayaran', $data);
  }

  private function buildMonitoringData(array $years, $startYear, $endYear, $selectedYear): array
  {
    $dosen = Auth::guard('dosen')->user();
    $nidnOrNuptk = $this->resolveDosenIdentifier($dosen);

    if (empty($years)) {
      return [
        'years' => [],
        'errorMessage' => 'Data tahun tidak tersedia.',
        'dosen' => $dosen,
      ];
    }

    if ($nidnOrNuptk === '') {
      return [
        'years' => $years,
        'errorMessage' => 'Akun dosen tidak memiliki NIDN/NUPTK.',
        'dosen' => $dosen,
      ];
    }

    if (empty($startYear)) {
      $startYear = $years[0];
    }
    if (empty($endYear)) {
      $endYear = end($years);
    }

    // Normalize start/end year and build the available filter options for the selected range.
    $years = array_map('strval', $years);
    $startYear = (string) $startYear;
    $endYear = (string) $endYear;

    // Ensure start <= end
    $startInt = (int) $startYear;
    $endInt = (int) $endYear;
    if ($startInt > 0 && $endInt > 0 && $startInt > $endInt) {
      [$startYear, $endYear] = [$endYear, $startYear];
      [$startInt, $endInt] = [$endInt, $startInt];
    }

    $yearsForRange = array_values(array_filter($years, function ($y) use ($startInt, $endInt) {
      $yi = (int) $y;
      if ($yi <= 0) {
        return false;
      }
      if ($startInt > 0 && $yi < $startInt) {
        return false;
      }
      if ($endInt > 0 && $yi > $endInt) {
        return false;
      }
      return true;
    }));

    // If range produces no options (bad inputs), fall back to all active years.
    $yearsForNidn = !empty($yearsForRange) ? $yearsForRange : $years;

    // Default selected year to endYear (latest in range) for better UX.
    $selectedYear = trim((string) ($selectedYear ?: $endYear));
    if (!in_array($selectedYear, $yearsForNidn, true)) {
      $selectedYear = end($yearsForNidn);
    }

    $yearColumn = $this->getTransaksiYearColumn();

    $transaksi = DB::table('s_transaksi_2')
      ->where(function ($q) use ($nidnOrNuptk) {
        $q->whereRaw('TRIM(`NIDN`) = ?', [$nidnOrNuptk])
          ->orWhereRaw('TRIM(`NUPTK`) = ?', [$nidnOrNuptk]);
      })
      ->where($yearColumn, $selectedYear)
      ->first();

    if (!$transaksi) {
      return [
        'years' => $years,
        'startYear' => $startYear,
        'endYear' => $endYear,
        'selectedYear' => $selectedYear,
        'dosen' => $dosen,
        'nidn' => $nidnOrNuptk,
        'errorMessage' => 'Data pembayaran tidak ditemukan untuk akun dosen ini pada tahun yang dipilih.',
      ];
    }

    $transaksiTahun = $transaksi;

    $selisihTotals = SelisihBayar::computeFromTransaksi($transaksiTahun);

    $golonganBulanan = [];
    $gajiBulanan = [];
    $tahunBulanan = [];
    $jabatanBulanan = [];
    $kotorTpd = [];
    $kotorTkgb = [];
    $pajakTpd = [];
    $pajakTkgb = [];
    $bersihTpd = [];
    $bersihTkgb = [];
    $noSp2d = [];
    $tglSp2d = [];
    $kodeUsulanBulanan = [];

    for ($i = 1; $i <= 12; $i++) {
      $golonganBulanan[] = $transaksiTahun ? ($transaksiTahun->{'Gol' . $i} ?? '-') : '-';
      $gajiBulanan[] = $transaksiTahun ? (float) ($transaksiTahun->{'Gaji' . $i} ?? 0) : 0;
      $tahunBulanan[] = $transaksiTahun ? ($transaksiTahun->{'Tahun' . $i} ?? '-') : '-';
      $jabatanBulanan[] = $transaksiTahun ? ($transaksiTahun->{'Jabatan' . $i} ?? '-') : '-';
      $kotorTpd[] = $transaksiTahun ? (float) ($transaksiTahun->{'TPD' . $i} ?? 0) : 0;
      $kotorTkgb[] = $transaksiTahun ? (float) ($transaksiTahun->{'TKGB' . $i} ?? 0) : 0;
      $pajakTpd[] = $transaksiTahun ? (float) ($transaksiTahun->{'nilaiPajakTPD' . $i} ?? 0) : 0;
      $pajakTkgb[] = $transaksiTahun ? (float) ($transaksiTahun->{'nilaiPajakTKGB' . $i} ?? 0) : 0;
      $bersihTpd[] = $transaksiTahun ? (float) ($transaksiTahun->{'bersihTPD' . $i} ?? 0) : 0;
      $bersihTkgb[] = $transaksiTahun ? (float) ($transaksiTahun->{'bersihTKGB' . $i} ?? 0) : 0;
      $noSp2d[] = $transaksiTahun ? ($transaksiTahun->{'No_sp2d_' . $i} ?? '-') : '-';
      $tglSp2d[] = $transaksiTahun ? ($transaksiTahun->{'Tgl_sp2d_' . $i} ?? '-') : '-';

      $kodeUsulanBulanan[] = $transaksiTahun ? ($transaksiTahun->{'KodeUsulan' . $i} ?? null) : null;
    }

    $bulanSession = (int) session('bulan') ?: 12;
    if ($bulanSession < 1 || $bulanSession > 12) {
      $bulanSession = 12;
    }
    $jabatanField = 'Jabatan' . $bulanSession;
    if ($transaksi) {
      $transaksi->JabatanSelected = $transaksi->{$jabatanField} ?? $transaksi->Jabatan12 ?? null;
    }

    return [
      'years' => $years,
      'yearsForNidn' => $yearsForNidn,
      'startYear' => $startYear,
      'endYear' => $endYear,
      'selectedYear' => $selectedYear,
      'transaksi' => $transaksi,
      'nidn' => $nidnOrNuptk,
      'kodeUsulanBulanan' => $kodeUsulanBulanan,
      'jabatanBulanan' => $jabatanBulanan,
      'noSp2d' => $noSp2d,
      'tglSp2d' => $tglSp2d,
      'kotorTpd' => $kotorTpd,
      'kotorTkgb' => $kotorTkgb,
      'pajakTpd' => $pajakTpd,
      'pajakTkgb' => $pajakTkgb,
      'bersihTpd' => $bersihTpd,
      'bersihTkgb' => $bersihTkgb,
      'golonganBulanan' => $golonganBulanan,
      'gajiBulanan' => $gajiBulanan,
      'tahunBulanan' => $tahunBulanan,
      'selisihTotals' => $selisihTotals,
      'dosen' => $dosen,
    ];
  }
}
