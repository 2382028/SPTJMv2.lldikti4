<?php

namespace App\Http\Controllers\Pts;

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

class MonitoringPembayaranPtsController extends Controller
{
  private const SESSION_KEY = 'pts_monitoring_identifier';

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
        // ignore
      }
    }

    $cached = 'tahun_versi';
    return $cached;
  }

  private function getAvailableYears(): array
  {
    try {
      if (Storage::disk('local')->exists('active_years.json')) {
        $raw = Storage::disk('local')->get('active_years.json');
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
          $years = [];
          foreach ($decoded as $y) {
            $yi = (int) $y;
            if ($yi > 0) {
              $years[] = (string) $yi;
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
      // ignore
    }

    $yearColumn = $this->getTransaksiYearColumn();
    return DB::table('s_transaksi_2')
      ->select($yearColumn)
      ->distinct()
      ->orderBy($yearColumn)
      ->pluck($yearColumn)
      ->map(fn($v) => (string) $v)
      ->toArray();
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

  private function getIdentifierFromSession(): string
  {
    return trim((string) session(self::SESSION_KEY));
  }

  public function index(Request $request)
  {
    $years = $this->getAvailableYears();
    $defaultYear = $this->getDefaultYear();

    $startYear = $request->input('start_year') ?? $defaultYear;
    $endYear = $request->input('end_year') ?? $defaultYear;
    $selectedYear = $request->input('tahun_versi') ?? $defaultYear;

    $identifier = $this->getIdentifierFromSession();

    return $this->renderMonitoring($years, $identifier, $startYear, $endYear, $selectedYear);
  }

  public function cari(Request $request)
  {
    $request->validate([
      'nidn' => ['required'],
      'start_year' => ['nullable'],
      'end_year' => ['nullable'],
      'tahun_versi' => ['nullable'],
    ]);

    $identifier = trim((string) $request->input('nidn'));
    session([self::SESSION_KEY => $identifier]);

    $years = $this->getAvailableYears();
    $defaultYear = $this->getDefaultYear();

    $startYear = $request->input('start_year') ?? $defaultYear;
    $endYear = $request->input('end_year') ?? $defaultYear;
    $selectedYear = $request->input('tahun_versi') ?? $defaultYear;

    return $this->renderMonitoring($years, $identifier, $startYear, $endYear, $selectedYear);
  }

  public function table(Request $request)
  {
    $request->validate([
      'start_year' => ['nullable'],
      'end_year' => ['nullable'],
      'tahun_versi' => ['required'],
    ]);

    $years = $this->getAvailableYears();
    $identifier = $this->getIdentifierFromSession();

    $data = $this->buildMonitoringData(
      $years,
      $identifier,
      $request->input('start_year'),
      $request->input('end_year'),
      $request->input('tahun_versi')
    );

    $html = view('pts.partials.monitoring-pembayaran-table', $data)->render();
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

    $identifier = $this->getIdentifierFromSession();
    if ($identifier === '') {
      return redirect()->back()->with('error', 'Silakan cari NIDN/NUPTK terlebih dahulu.');
    }

    $ptsUser = Auth::guard('pts')->user();
    $kodePts = trim((string) ($ptsUser->kode_pts ?? ''));

    $selectedYear = trim((string) $request->input('tahun_versi'));
    $allowedYears = $this->getAvailableYears();
    if (!empty($allowedYears) && !in_array($selectedYear, array_map('strval', $allowedYears), true)) {
      return redirect()->back()->with('error', 'Tahun versi tidak valid.');
    }

    $yearColumn = $this->getTransaksiYearColumn();

    $transaksi = DB::table('s_transaksi_2')
      ->where(function ($q) use ($identifier) {
        $q->whereRaw('TRIM(`NIDN`) = ?', [$identifier])
          ->orWhereRaw('TRIM(`NUPTK`) = ?', [$identifier]);
      })
      ->where('Kode_PT', $kodePts)
      ->where($yearColumn, $selectedYear)
      ->first();

    if (!$transaksi) {
      return redirect()->back()->with('error', 'Data tidak ditemukan atau bukan milik PTS Anda.');
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
      $tahunGol = $transaksi?->{'Tahun' . $m} ?? '-';
      $jabatan = $transaksi?->{'Jabatan' . $m} ?? '-';

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
        $jabatan,
        $kodeUsulan,
        $gol . ' - ' . $tahunGol,
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

    $fileName = 'monitoring-pembayaran_' . $identifier . '_' . $selectedYear . '.xlsx';
    $headings = [
      'Tahun',
      'Bulan',
      'Jabatan',
      'Kode Usulan',
      'Pangkat Golongan',
      'Gaji',
      'Kotor TPD',
      'Kotor TKGB',
      'Pajak TPD',
      'Pajak TKGB',
      'Bersih TPD',
      'Bersih TKGB',
      'NO SP2D',
      'TGL SP2D',
    ];

    $mergeEndByLabel = [
      'Jumlah' => 'E',
      'Jumlah Selisih Bayar' => 'F',
    ];

    return Excel::download(new MonitoringPembayaranExport($rows, $headings, $mergeEndByLabel), $fileName);
  }

  public function cetakSpt(Request $request)
  {
    $request->validate([
      'tahun_versi' => ['required'],
    ]);

    $identifier = $this->getIdentifierFromSession();
    if ($identifier === '') {
      return redirect()->back()->with('error', 'Silakan cari NIDN/NUPTK terlebih dahulu.');
    }

    $ptsUser = Auth::guard('pts')->user();
    $kodePts = trim((string) ($ptsUser->kode_pts ?? ''));

    $tahunVersi = $request->input('tahun_versi') ?? $request->input('cetak_spt_tahun_versi') ?? $request->input('tahunVersi');
    $selectedYear = trim((string) $tahunVersi);

    $allowedYears = $this->getAvailableYears();
    if (!empty($allowedYears) && !in_array($selectedYear, array_map('strval', $allowedYears), true)) {
      return redirect()->back()->with('error', 'Tahun versi tidak valid.');
    }

    $yearColumn = $this->getTransaksiYearColumn();
    $transaksi = DB::table('s_transaksi_2')
      ->where(function ($q) use ($identifier) {
        $q->whereRaw('TRIM(`NIDN`) = ?', [$identifier])
          ->orWhereRaw('TRIM(`NUPTK`) = ?', [$identifier]);
      })
      ->where('Kode_PT', $kodePts)
      ->where($yearColumn, $selectedYear)
      ->first();

    if (!$transaksi) {
      return redirect()->back()->with('error', 'Data tidak ditemukan atau bukan milik PTS Anda.');
    }

    // Delegate PDF rendering to the existing Admin implementation.
    // Force nidn/tahun_versi from session + validated year to avoid request tampering.
    $request->merge([
      'nidn' => $identifier,
      'tahun_versi' => $selectedYear,
      'cetak_spt_tahun_versi' => $selectedYear,
    ]);

    return app(AdminMonitoringPembayaranController::class)->cetakSpt($request);
  }

  private function renderMonitoring(array $years, string $identifier, $startYear, $endYear, $selectedYear)
  {
    $data = $this->buildMonitoringData($years, $identifier, $startYear, $endYear, $selectedYear);
    return view('pts.monitoring-pembayaran', $data);
  }

  private function buildMonitoringData(array $years, string $identifier, $startYear, $endYear, $selectedYear): array
  {
    $ptsUser = Auth::guard('pts')->user();
    $kodePts = trim((string) ($ptsUser->kode_pts ?? ''));

    if (empty($years)) {
      return [
        'years' => [],
        'errorMessage' => 'Data tahun tidak tersedia.',
        'identifier' => $identifier,
      ];
    }

    if ($identifier === '') {
      return [
        'years' => $years,
        'identifier' => '',
      ];
    }

    if (empty($startYear)) {
      $startYear = $years[0];
    }
    if (empty($endYear)) {
      $endYear = end($years);
    }

    $years = array_map('strval', $years);
    $startYear = (string) $startYear;
    $endYear = (string) $endYear;

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

    $yearsForNidn = !empty($yearsForRange) ? $yearsForRange : $years;

    $selectedYear = trim((string) ($selectedYear ?: $endYear));
    if (!in_array($selectedYear, $yearsForNidn, true)) {
      $selectedYear = end($yearsForNidn);
    }

    $yearColumn = $this->getTransaksiYearColumn();

    $transaksi = DB::table('s_transaksi_2')
      ->where(function ($q) use ($identifier) {
        $q->whereRaw('TRIM(`NIDN`) = ?', [$identifier])
          ->orWhereRaw('TRIM(`NUPTK`) = ?', [$identifier]);
      })
      ->where('Kode_PT', $kodePts)
      ->where($yearColumn, $selectedYear)
      ->first();

    if (!$transaksi) {
      return [
        'years' => $years,
        'yearsForNidn' => $yearsForNidn,
        'startYear' => $startYear,
        'endYear' => $endYear,
        'selectedYear' => $selectedYear,
        'identifier' => $identifier,
        'errorMessage' => 'Data pembayaran tidak ditemukan untuk NIDN/NUPTK tersebut pada tahun yang dipilih atau bukan milik PTS Anda.',
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
      $golonganBulanan[] = $transaksiTahun->{'Gol' . $i} ?? '-';
      $gajiBulanan[] = (float) ($transaksiTahun->{'Gaji' . $i} ?? 0);
      $tahunBulanan[] = $transaksiTahun->{'Tahun' . $i} ?? '-';
      $jabatanBulanan[] = $transaksiTahun->{'Jabatan' . $i} ?? '-';
      $kotorTpd[] = (float) ($transaksiTahun->{'TPD' . $i} ?? 0);
      $kotorTkgb[] = (float) ($transaksiTahun->{'TKGB' . $i} ?? 0);
      $pajakTpd[] = (float) ($transaksiTahun->{'nilaiPajakTPD' . $i} ?? 0);
      $pajakTkgb[] = (float) ($transaksiTahun->{'nilaiPajakTKGB' . $i} ?? 0);
      $bersihTpd[] = (float) ($transaksiTahun->{'bersihTPD' . $i} ?? 0);
      $bersihTkgb[] = (float) ($transaksiTahun->{'bersihTKGB' . $i} ?? 0);
      $noSp2d[] = $transaksiTahun->{'No_sp2d_' . $i} ?? '-';
      $tglSp2d[] = $transaksiTahun->{'Tgl_sp2d_' . $i} ?? '-';
      $kodeUsulanBulanan[] = $transaksiTahun->{'KodeUsulan' . $i} ?? null;
    }

    $bulanSession = (int) session('bulan') ?: 12;
    if ($bulanSession < 1 || $bulanSession > 12) {
      $bulanSession = 12;
    }
    $jabatanField = 'Jabatan' . $bulanSession;
    $transaksi->JabatanSelected = $transaksi->{$jabatanField} ?? $transaksi->Jabatan12 ?? null;

    return [
      'years' => $years,
      'yearsForNidn' => $yearsForNidn,
      'startYear' => $startYear,
      'endYear' => $endYear,
      'selectedYear' => $selectedYear,
      'transaksi' => $transaksi,
      'identifier' => $identifier,
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
    ];
  }
}
