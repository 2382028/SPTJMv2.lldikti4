<?php

namespace App\Http\Controllers;

use App\Helpers\ErrorAlias;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class KekuranganBayarController extends Controller
{
  private function parseMoney($value): float
  {
    if ($value === null) return 0.0;
    if (is_int($value) || is_float($value)) return (float) $value;
    $text = trim((string) $value);
    if ($text === '') return 0.0;
    // common DB values are like "1.234.567" or "1234567"; treat as integer money
    $text = preg_replace('/[^0-9\-]/', '', $text);
    if ($text === '' || $text === '-') return 0.0;
    return (float) $text;
  }

  private function insertRekapRow(array $payload): void
  {
    try {
      DB::table('rekap_kekurangan')->insert($payload);
    } catch (\Throwable $e) {
      DB::table('u_rekap_kekurangan')->insert($payload);
    }
  }

  private function ensurePublicFolder(string $folderName): string
  {
    // Backward-compatible method name.
    // We now store files in storage/app/public/{folderName} (served via /storage symlink).
    $disk = Storage::disk('public');
    if (!$disk->exists($folderName)) {
      $disk->makeDirectory($folderName);
    }
    return $folderName;
  }

  private function toExcelTsv(array $rows, string $tipe): string
  {
    $lines = [];
    $header = ['NIDN', 'Nama', 'Jenis', 'Bank'];

    if ($tipe === 'Semua' || $tipe === 'TPD') {
      for ($i = 1; $i <= 12; $i++) {
        $header[] = "TPD{$i}";
      }
    }
    if ($tipe === 'Semua' || $tipe === 'TKGB') {
      for ($i = 1; $i <= 12; $i++) {
        $header[] = "TKGB{$i}";
      }
    }

    $header = array_merge($header, ['Jumlah TPD', 'Jumlah TKGB', 'Pajak TPD', 'Pajak TKGB', 'Total Bersih']);
    $lines[] = implode("\t", $header);

    foreach ($rows as $row) {
      $cols = [
        $row->NIDN ?? $row->nidn ?? '',
        $row->Nama ?? $row->nama ?? '',
        $row->Jenis ?? $row->jenis ?? '',
        $row->Bank ?? $row->bank ?? '',
      ];

      if ($tipe === 'Semua' || $tipe === 'TPD') {
        for ($i = 1; $i <= 12; $i++) {
          $key = 'k_tpd' . $i;
          $cols[] = $row->$key ?? 0;
        }
      }
      if ($tipe === 'Semua' || $tipe === 'TKGB') {
        for ($i = 1; $i <= 12; $i++) {
          $key = 'k_tkgb' . $i;
          $cols[] = $row->$key ?? 0;
        }
      }

      $cols[] = $row->jml_tpd ?? 0;
      $cols[] = $row->jml_tkgb ?? 0;
      $cols[] = $row->nilai_pjk_tpd ?? 0;
      $cols[] = $row->nilai_pjk_tkgb ?? 0;
      $cols[] = $row->bersih ?? 0;

      $lines[] = implode("\t", $cols);
    }

    return implode("\n", $lines) . "\n";
  }

  private function toExcelHtmlLikeTable(array $rows, array $tarifMap): string
  {
    $fmt = function ($value): string {
      $num = (float) ($value ?? 0);
      return number_format((int) round($num), 0, ',', '.');
    };

    $bg = function (float $diff): string {
      if ($diff == 0.0) return '';
      // DB > Aktual => lebih bayar (green), DB < Aktual => kurang bayar (red)
      return $diff > 0 ? 'background-color:#d4edda;' : 'background-color:#f8d7da;';
    };

    $escape = function ($text): string {
      return htmlspecialchars((string) ($text ?? ''), ENT_QUOTES, 'UTF-8');
    };

    $months = [
      1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
      7 => 'Jul', 8 => 'Ags', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
    ];

    $html = [];
    $html[] = '<html><head><meta charset="utf-8" />';
    $html[] = '<style>';
    $html[] = 'table{border-collapse:collapse;font-family:Calibri,Arial,sans-serif;font-size:11pt;}';
    $html[] = 'th,td{border:1px solid #333;padding:4px;vertical-align:middle;}';
    $html[] = 'th{text-align:center;font-weight:bold;background-color:#f2f2f2;}';
    $html[] = 'td.num{text-align:right;}';
    $html[] = '</style></head><body>';
    $html[] = '<table>';

    // Header row 1
    $html[] = '<tr>'
      . '<th rowspan="3">No</th>'
      . '<th rowspan="3">NIDN</th>'
      . '<th rowspan="3">Nama</th>'
      . '<th rowspan="3">Jenis</th>'
      . '<th rowspan="3">Jabatan</th>'
      . '<th rowspan="3">Status</th>'
      . '<th colspan="48">Januari - Desember</th>'
      . '<th colspan="11">Jumlah Kotor, Nilai Pajak, dan Bersih</th>'
      . '</tr>';

    // Header row 2
    $row2 = '<tr>';
    foreach ($months as $label) {
      $row2 .= '<th colspan="2">' . $label . '</th>';
      $row2 .= '<th colspan="2">' . $label . ' (Aktual)</th>';
    }
    $row2 .= '<th colspan="2">Jumlah Kotor</th>';
    $row2 .= '<th colspan="2">Nilai Pajak</th>';
    $row2 .= '<th rowspan="2">Bersih</th>';
    $row2 .= '<th colspan="2">Jumlah Kotor (Aktual)</th>';
    $row2 .= '<th colspan="2">Nilai Pajak (Aktual)</th>';
    $row2 .= '<th rowspan="2">Bersih (Aktual)</th>';
    $row2 .= '<th rowspan="2">Kesimpulan</th>';
    $row2 .= '</tr>';
    $html[] = $row2;

    // Header row 3
    $row3 = '<tr>';
    for ($i = 1; $i <= 12; $i++) {
      $row3 .= '<th>TPD</th><th>TKGB</th><th>TPD</th><th>TKGB</th>';
    }
    $row3 .= '<th>TPD</th><th>TKGB</th><th>TPD</th><th>TKGB</th>';
    $row3 .= '<th>TPD</th><th>TKGB</th><th>TPD</th><th>TKGB</th>';
    $row3 .= '</tr>';
    $html[] = $row3;

    $no = 1;
    foreach ($rows as $r) {
      $jenisRow = (string) ($r->Jenis ?? $r->jenis ?? '');
      $jenisKey = trim($jenisRow);

      $sumDbKotorTPD = 0.0;
      $sumDbKotorTKGB = 0.0;
      $sumDbPajakTPD = 0.0;
      $sumDbPajakTKGB = 0.0;
      $sumDbBersih = 0.0;

      $sumAktKotorTPD = 0.0;
      $sumAktKotorTKGB = 0.0;
      $sumAktPajakTPD = 0.0;
      $sumAktPajakTKGB = 0.0;
      $sumAktBersih = 0.0;

      $status = ((int) ($r->Aktif ?? 0)) === 1 ? 'Aktif' : 'Tidak Aktif';
      $jabatan12 = (string) ($r->Jabatan12 ?? '');

      $tr = '<tr>'
        . '<td class="num">' . $no . '</td>'
        . '<td>' . $escape($r->NIDN ?? $r->nidn ?? '') . '</td>'
        . '<td>' . $escape($r->Nama ?? $r->nama ?? '') . '</td>'
        . '<td>' . $escape($jenisRow) . '</td>'
        . '<td>' . $escape($jabatan12) . '</td>'
        . '<td>' . $escape($status) . '</td>';

      for ($i = 1; $i <= 12; $i++) {
        $noSp2d = trim((string) ($r->{'No_sp2d_' . $i} ?? $r->{'NoSP2D' . $i} ?? ''));
        $tglSp2d = trim((string) ($r->{'Tgl_sp2d_' . $i} ?? $r->{'TglSP2D' . $i} ?? ''));
        $sp2dOk = ($noSp2d !== '' && $tglSp2d !== '');

        $dbKotorTPD = 0.0;
        $dbKotorTKGB = 0.0;
        $aktKotorTPD = 0.0;
        $aktKotorTKGB = 0.0;
        $tarif = 0.0;
        $kenaTKGB = false;

        if ($sp2dOk) {
          $dbKotorTPD = (float) $this->parseMoney($r->{'TPD' . $i} ?? 0);
          $dbKotorTKGB = (float) $this->parseMoney($r->{'TKGB' . $i} ?? 0);

          $gol = trim((string) ($r->{'Gol' . $i} ?? ''));
          $jabatan = (string) ($r->{'Jabatan' . $i} ?? $jabatan12);
          $kenaTKGB = $this->isGuruBesarAtauProfesor($jabatan);

          $gaji = (float) $this->parseMoney($r->{'Gaji' . $i} ?? 0);
          [$aktKotorTPD, $aktKotorTKGB] = $this->splitAktualKotorFromGaji($gaji, $kenaTKGB);

          $tarif = (float) (($tarifMap[$jenisKey][$gol] ?? 0) ?: 0);
        }

        $diffTpd = $dbKotorTPD - $aktKotorTPD;
        $diffTkgb = $dbKotorTKGB - $aktKotorTKGB;

        $tr .= '<td class="num" style="' . $bg($diffTpd) . '">' . $fmt($dbKotorTPD) . '</td>';
        $tr .= '<td class="num" style="' . $bg($diffTkgb) . '">' . $fmt($dbKotorTKGB) . '</td>';
        $tr .= '<td class="num">' . $fmt($aktKotorTPD) . '</td>';
        $tr .= '<td class="num">' . $fmt($aktKotorTKGB) . '</td>';

        if ($sp2dOk) {
          $dbPajakTPD = $dbKotorTPD * $tarif;
          $dbPajakTKGB = $kenaTKGB ? ($dbKotorTKGB * $tarif) : 0.0;
          $dbBersih = ($dbKotorTPD - $dbPajakTPD) + ($dbKotorTKGB - $dbPajakTKGB);

          $aktPajakTPD = $aktKotorTPD * $tarif;
          $aktPajakTKGB = $kenaTKGB ? ($aktKotorTKGB * $tarif) : 0.0;
          $aktBersih = ($aktKotorTPD - $aktPajakTPD) + ($aktKotorTKGB - $aktPajakTKGB);

          $sumDbKotorTPD += $dbKotorTPD;
          $sumDbKotorTKGB += $dbKotorTKGB;
          $sumDbPajakTPD += $dbPajakTPD;
          $sumDbPajakTKGB += $dbPajakTKGB;
          $sumDbBersih += $dbBersih;

          $sumAktKotorTPD += $aktKotorTPD;
          $sumAktKotorTKGB += $aktKotorTKGB;
          $sumAktPajakTPD += $aktPajakTPD;
          $sumAktPajakTKGB += $aktPajakTKGB;
          $sumAktBersih += $aktBersih;
        }
      }

      $clsSumTpd = $bg($sumDbKotorTPD - $sumAktKotorTPD);
      $clsSumTkgb = $bg($sumDbKotorTKGB - $sumAktKotorTKGB);
      $clsPjkTpd = $bg($sumDbPajakTPD - $sumAktPajakTPD);
      $clsPjkTkgb = $bg($sumDbPajakTKGB - $sumAktPajakTKGB);
      $clsBersih = $bg($sumDbBersih - $sumAktBersih);

      $kesimpulan = $sumDbBersih - $sumAktBersih;
      $clsKesimpulan = $bg($kesimpulan);

      $tr .= '<td class="num" style="' . $clsSumTpd . '">' . $fmt($sumDbKotorTPD) . '</td>';
      $tr .= '<td class="num" style="' . $clsSumTkgb . '">' . $fmt($sumDbKotorTKGB) . '</td>';
      $tr .= '<td class="num" style="' . $clsPjkTpd . '">' . $fmt($sumDbPajakTPD) . '</td>';
      $tr .= '<td class="num" style="' . $clsPjkTkgb . '">' . $fmt($sumDbPajakTKGB) . '</td>';
      $tr .= '<td class="num" style="' . $clsBersih . '">' . $fmt($sumDbBersih) . '</td>';

      $tr .= '<td class="num">' . $fmt($sumAktKotorTPD) . '</td>';
      $tr .= '<td class="num">' . $fmt($sumAktKotorTKGB) . '</td>';
      $tr .= '<td class="num">' . $fmt($sumAktPajakTPD) . '</td>';
      $tr .= '<td class="num">' . $fmt($sumAktPajakTKGB) . '</td>';
      $tr .= '<td class="num">' . $fmt($sumAktBersih) . '</td>';
      $tr .= '<td class="num" style="' . $clsKesimpulan . '">' . $fmt($kesimpulan) . '</td>';

      $tr .= '</tr>';
      $html[] = $tr;
      $no++;
    }

    $html[] = '</table></body></html>';
    return implode("\n", $html);
  }

  private function loadTarifPajakMap(): array
  {
    $tarifMap = [];
    $pajakRows = DB::table('d_pajak')->select('status', 'akumulasi', 'tarif_pajak')->get();
    foreach ($pajakRows as $p) {
      $status = trim((string) ($p->status ?? ''));
      $akum = trim((string) ($p->akumulasi ?? ''));
      if ($status === '' || $akum === '') continue;
      $tarifMap[$status][$akum] = (float) ($p->tarif_pajak ?? 0);
    }
    return $tarifMap;
  }

  /**
   * Hitung payload kekurangan/kelebihan untuk tahun sesi dan filter.
   * - Skip NIDN yang sudah ada di t_kekurangan jika $skipExisting=true.
   * - Skip jika total paid=0.
   * - Skip jika belum ada SP2D satupun.
   */
  private function computeKekuranganPayload(string $versi, string $tipe, string $jenis, string $bank, array $tarifMap, bool $skipExisting = true): array
  {
    @set_time_limit(0);
    @ini_set('memory_limit', '-1');
    DB::disableQueryLog();

    $existingNidnSet = [];
    if ($skipExisting) {
      try {
        $existingNidn = DB::table('t_kekurangan')
          ->where('tahun', $versi)
          ->whereNotNull('nidn')
          ->pluck('nidn');
        foreach ($existingNidn as $n) {
          $t = trim((string) $n);
          if ($t !== '') {
            $existingNidnSet[$t] = true;
          }
        }
      } catch (\Throwable $e) {
        Log::warning('KekuranganBayarController::compute - gagal memuat existing NIDN: ' . $e->getMessage());
      }
    }

    $baseQuery = DB::table('s_transaksi_2 as s')
      ->where('s.Tahun_Versi', $versi);

    if ($jenis !== 'Semua') {
      $baseQuery->where('s.Jenis', $jenis);
    }
    if ($bank !== 'Semua') {
      $baseQuery->whereRaw('TRIM(s.Bank) = ?', [trim($bank)]);
    }

    $selects = [
      's.NIDN',
      's.Nama',
      's.Jenis',
      's.Jabatan12',
      's.Aktif',
      's.Bank',
    ];

    for ($i = 1; $i <= 12; $i++) {
      $selects[] = DB::raw('s.Gol' . $i . ' as Gol' . $i);
      $selects[] = DB::raw('s.Jabatan' . $i . ' as Jabatan' . $i);
      $selects[] = DB::raw('s.TPD' . $i . ' as ExpTPD' . $i);
      $selects[] = DB::raw('s.TKGB' . $i . ' as ExpTKGB' . $i);
      $selects[] = DB::raw('s.No_sp2d_' . $i . ' as NoSP2D' . $i);
      $selects[] = DB::raw('s.Tgl_sp2d_' . $i . ' as TglSP2D' . $i);
      // Prefer paid component values if available; fallback will use Gaji{i} (total) and split proportionally.
      $selects[] = DB::raw('s.bersihTPD' . $i . ' as PaidTPD' . $i);
      $selects[] = DB::raw('s.bersihTKGB' . $i . ' as PaidTKGB' . $i);
      $selects[] = DB::raw('s.Gaji' . $i . ' as PaidGaji' . $i);
    }

    $cursor = $baseQuery->select($selects)->orderBy('s.NIDN')->cursor();
    $payloadRows = [];

    foreach ($cursor as $row) {
      $nidn = trim((string) ($row->NIDN ?? ''));
      if ($nidn === '') continue;
      if ($skipExisting && isset($existingNidnSet[$nidn])) continue;

      $nama = (string) ($row->Nama ?? '');
      $jenisRow = (string) ($row->Jenis ?? '');

      $sumTPD = 0.0;
      $sumTKGB = 0.0;
      $sumPajakTPD = 0.0;
      $sumPajakTKGB = 0.0;
      $sumPaid = 0.0;
      $sumExpectedBersih = 0.0;
      $hasAnySP2D = false;

      // Prepare ID components; final prefix will be chosen after bersih is computed.
      // Rule: bersih < 0 => KKB (kurang bayar), bersih > 0 => KLB (lebih bayar)
      $rand = strtoupper(substr(md5(uniqid((string) mt_rand(), true)), 0, 6));
      $ts = date('YmdHis');
      $nidnCompact = preg_replace('/[^A-Za-z0-9]/', '', $nidn);

      $payload = [
        'nidn' => $nidn,
        'nama' => $nama,
        'tahun' => (string) $versi,
        // fields for view (match alias used by index())
        'NIDN' => $nidn,
        'Nama' => $nama,
        'Jenis' => $jenisRow,
        'Jabatan12' => (string) ($row->Jabatan12 ?? ''),
        'Aktif' => (int) ($row->Aktif ?? 0),
      ];

      for ($i = 1; $i <= 12; $i++) {
        $noSp2d = trim((string) ($row->{'NoSP2D' . $i} ?? ''));
        $tglSp2d = trim((string) ($row->{'TglSP2D' . $i} ?? ''));
        $sp2dOk = ($noSp2d !== '' && $tglSp2d !== '');
        if ($sp2dOk) {
          $hasAnySP2D = true;
        }

        $kTPD = 0.0;
        $kTKGB = 0.0;
        $aktTPDInt = 0;
        $aktTKGBInt = 0;
        $dbTPDInt = 0;
        $dbTKGBInt = 0;

        if ($sp2dOk) {
          $gol = trim((string) ($row->{'Gol' . $i} ?? ''));
          $jabatan = (string) ($row->{'Jabatan' . $i} ?? '');
          // Keep reference fields for preview (not inserted to DB due to allowlist)
          $payload['Gol' . $i] = $gol;
          $payload['Jabatan' . $i] = $jabatan;
          $payload['sp2d_ok' . $i] = 1;
          $tarif = (float) (($tarifMap[$jenisRow][$gol] ?? 0) ?: 0);

          // DB (plain) untuk kolom Jan-Des: kotor (belum dikurangi pajak)
          $dbKotorTPD = $this->parseMoney($row->{'ExpTPD' . $i} ?? 0);
          $dbKotorTKGB = $this->parseMoney($row->{'ExpTKGB' . $i} ?? 0);
          $dbTPDInt = (int) round($dbKotorTPD);
          $dbTKGBInt = (int) round($dbKotorTKGB);

          // Aktual untuk kolom (Aktual): hasil hitung ala sinkronisasi (penentuan TPD/TKGB dari Gaji)
          $gaji = $this->parseMoney($row->{'PaidGaji' . $i} ?? 0);
          $kenaTKGB = $this->isGuruBesarAtauProfesor($jabatan);
          [$aktKotorTPD, $aktKotorTKGB] = $this->splitAktualKotorFromGaji($gaji, $kenaTKGB);
          $aktTPDInt = (int) round($aktKotorTPD);
          $aktTKGBInt = (int) round($aktKotorTKGB);

          // Selisih kotor (DB - Aktual): lebih bayar = positif, kurang bayar = negatif
          $kTPD = $dbKotorTPD - $aktKotorTPD;
          $kTKGB = $dbKotorTKGB - $aktKotorTKGB;

          // Nilai pajak mengikuti perhitungan sinkronisasi (tarif * kotor, TKGB kena pajak hanya untuk Guru Besar/Profesor)
          $dbPajakTPD = $dbKotorTPD * $tarif;
          $dbPajakTKGB = $kenaTKGB ? ($dbKotorTKGB * $tarif) : 0.0;
          $aktPajakTPD = $aktKotorTPD * $tarif;
          $aktPajakTKGB = $kenaTKGB ? ($aktKotorTKGB * $tarif) : 0.0;

          // Simpan sebagai selisih pajak (DB - Aktual)
          $sumPajakTPD += ($dbPajakTPD - $aktPajakTPD);
          $sumPajakTKGB += ($dbPajakTKGB - $aktPajakTKGB);

          // Bersih = kotor - pajak (selisih bersih)
          $dbBersih = ($dbKotorTPD - $dbPajakTPD) + ($dbKotorTKGB - $dbPajakTKGB);
          $aktBersih = ($aktKotorTPD - $aktPajakTPD) + ($aktKotorTKGB - $aktPajakTKGB);
          $sumExpectedBersih += ($dbBersih - $aktBersih);

          // dipakai untuk filter "gaji 0" / kosong
          $sumPaid += $gaji;
        }
        if (!$sp2dOk) {
          $payload['sp2d_ok' . $i] = 0;
        }

        $payload['k_tpd' . $i] = $kTPD;
        $payload['k_tkgb' . $i] = $kTKGB;
        // nilai aktual (kotor) per bulan
        $payload['exp_tpd' . $i] = $aktTPDInt;
        $payload['exp_tkgb' . $i] = $aktTKGBInt;
        // nilai asli dari DB (kotor) per bulan
        $payload['db_tpd' . $i] = $dbTPDInt;
        $payload['db_tkgb' . $i] = $dbTKGBInt;
        $sumTPD += $kTPD;
        $sumTKGB += $kTKGB;
      }

      if ($tipe === 'TPD' && $sumTPD == 0.0) continue;
      if ($tipe === 'TKGB' && $sumTKGB == 0.0) continue;
      if (!$hasAnySP2D) continue;
      // Skip rows that have no effective delta (avoid inserting/displaying 0-only rows)
      if (abs($sumTPD) < 0.0000001 && abs($sumTKGB) < 0.0000001) continue;
      // Skip truly empty financial rows (both expected and paid are 0)
      if (abs($sumExpectedBersih) < 0.0000001 && abs($sumPaid) < 0.0000001) continue;

      $payload['jml_tpd'] = $sumTPD;
      $payload['jml_tkgb'] = $sumTKGB;
      $payload['nilai_pjk_tpd'] = $sumPajakTPD;
      $payload['nilai_pjk_tkgb'] = $sumPajakTKGB;
      $payload['bersih'] = (($sumTPD + $sumTKGB) - ($sumPajakTPD + $sumPajakTKGB));
      $payload['total_pembayaran'] = $sumPaid;

      $prefix = ((float) $payload['bersih']) > 0 ? 'KLB' : 'KKB';
      $payload['id'] = substr(sprintf('%s-%s-%s-%s-%s', $prefix, $versi, $ts, $nidnCompact ?: 'X', $rand), 0, 50);

      $payloadRows[] = (object) $payload;
      if ($skipExisting) {
        $existingNidnSet[$nidn] = true;
      }
    }

    return $payloadRows;
  }

  /**
   * Cek Data: hitung seperti proses namun tanpa insert.
   * Menampilkan data ke tabel (DB vs Aktual + totals + kesimpulan).
   */
  public function cek(Request $request)
  {
    $request->validate([
      'periode' => 'required',
      'tipe' => 'required',
      'jenis' => 'required',
      'bank' => 'required',
    ]);

    $versi = session('tahun');
    if (!$versi) {
      return redirect()->route('admin.kekurangan-bayar')->with('error', 'Tahun versi belum dipilih pada sesi.');
    }

    $tipe = $request->input('tipe', 'Semua');
    $jenis = $request->input('jenis', 'Semua');
    $bank = $request->input('bank', 'Semua');

    try {
      $tarifMap = $this->loadTarifPajakMap();
    } catch (\Throwable $e) {
      $alias = ErrorAlias::fromThrowable($e, 'ADM-KEKURANGAN-PAJAK');
      Log::error('KekuranganBayarController::cek - gagal memuat tarif pajak', [
        'alias' => $alias['code'],
        'versi' => $versi,
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
      ]);
      return redirect()->route('admin.kekurangan-bayar')->with('error', 'Gagal memuat tarif pajak. ' . $alias['message']);
    }

    try {
      // Same selection rules as proses (skip existing + SP2D-only months)
      $rowsPayload = $this->computeKekuranganPayload((string) $versi, $tipe, $jenis, $bank, $tarifMap, true);
      if (empty($rowsPayload)) {
        return redirect()->route('admin.kekurangan-bayar')->with('warning', 'Tidak ada data untuk dicek (mungkin sudah pernah diproses / total pembayaran 0 / belum ada SP2D).');
      }

      // Shape rows to match the on-screen table (absolute DB totals vs absolute Aktual totals)
      $displayRows = [];
      foreach ($rowsPayload as $obj) {
        $row = (object) ((array) $obj);
        $jenisRow = (string) ($row->Jenis ?? '');
        $jenisKey = trim($jenisRow);

        $sumDbKotorTPD = 0.0;
        $sumDbKotorTKGB = 0.0;
        $sumDbPajakTPD = 0.0;
        $sumDbPajakTKGB = 0.0;
        $sumDbBersih = 0.0;

        $sumAktKotorTPD = 0.0;
        $sumAktKotorTKGB = 0.0;
        $sumAktPajakTPD = 0.0;
        $sumAktPajakTKGB = 0.0;
        $sumAktBersih = 0.0;

        for ($i = 1; $i <= 12; $i++) {
          $sp2dOk = (int) ($row->{'sp2d_ok' . $i} ?? 0) === 1;
          if (!$sp2dOk) {
            // already 0-filled
            continue;
          }

          $dbKotorTPD = (float) $this->parseMoney($row->{'db_tpd' . $i} ?? 0);
          $dbKotorTKGB = (float) $this->parseMoney($row->{'db_tkgb' . $i} ?? 0);
          $aktKotorTPD = (float) $this->parseMoney($row->{'exp_tpd' . $i} ?? 0);
          $aktKotorTKGB = (float) $this->parseMoney($row->{'exp_tkgb' . $i} ?? 0);

          $gol = trim((string) ($row->{'Gol' . $i} ?? ''));
          $jabatan = (string) ($row->{'Jabatan' . $i} ?? ($row->Jabatan12 ?? ''));
          $kenaTKGB = $this->isGuruBesarAtauProfesor($jabatan);
          $tarif = (float) (($tarifMap[$jenisKey][$gol] ?? 0) ?: 0);

          $dbPajakTPD = $dbKotorTPD * $tarif;
          $dbPajakTKGB = $kenaTKGB ? ($dbKotorTKGB * $tarif) : 0.0;
          $dbBersih = ($dbKotorTPD - $dbPajakTPD) + ($dbKotorTKGB - $dbPajakTKGB);

          $aktPajakTPD = $aktKotorTPD * $tarif;
          $aktPajakTKGB = $kenaTKGB ? ($aktKotorTKGB * $tarif) : 0.0;
          $aktBersih = ($aktKotorTPD - $aktPajakTPD) + ($aktKotorTKGB - $aktPajakTKGB);

          $sumDbKotorTPD += $dbKotorTPD;
          $sumDbKotorTKGB += $dbKotorTKGB;
          $sumDbPajakTPD += $dbPajakTPD;
          $sumDbPajakTKGB += $dbPajakTKGB;
          $sumDbBersih += $dbBersih;

          $sumAktKotorTPD += $aktKotorTPD;
          $sumAktKotorTKGB += $aktKotorTKGB;
          $sumAktPajakTPD += $aktPajakTPD;
          $sumAktPajakTKGB += $aktPajakTKGB;
          $sumAktBersih += $aktBersih;
        }

        // Fill totals expected by blade
        $row->jml_tpd = $sumDbKotorTPD;
        $row->jml_tkgb = $sumDbKotorTKGB;
        $row->nilai_pjk_tpd = $sumDbPajakTPD;
        $row->nilai_pjk_tkgb = $sumDbPajakTKGB;
        $row->bersih = $sumDbBersih;

        $row->jml_tpd_akt = $sumAktKotorTPD;
        $row->jml_tkgb_akt = $sumAktKotorTKGB;
        $row->nilai_pjk_tpd_akt = $sumAktPajakTPD;
        $row->nilai_pjk_tkgb_akt = $sumAktPajakTKGB;
        $row->bersih_akt = $sumAktBersih;

        $displayRows[] = $row;
      }

      return view('admin.kekurangan-bayar', [
        'versi' => $versi,
        'detailKekurangan' => collect($displayRows),
        'rekapKurang' => collect(),
        'rekapLebih' => collect(),
        'bankList' => DB::table('b_bank')->select('nama_bank')->whereNotNull('nama_bank')->where('nama_bank','!=','')->distinct()->orderBy('nama_bank')->pluck('nama_bank'),
        'flashInfo' => 'Cek Data selesai (tanpa insert). Klik Proses untuk menyimpan ke database.',
      ]);
    } catch (\Throwable $e) {
      $alias = ErrorAlias::fromThrowable($e, 'ADM-KEKURANGAN-CEK');
      Log::error('KekuranganBayarController::cek - gagal hitung cek data', [
        'alias' => $alias['code'],
        'versi' => $versi,
        'tipe' => $tipe,
        'jenis' => $jenis,
        'bank' => $bank,
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
      ]);
      return redirect()->route('admin.kekurangan-bayar')->with('error', 'Gagal cek data. ' . $alias['message']);
    }
  }

  private function isGuruBesarAtauProfesor($jabatan): bool
  {
    $text = strtolower(trim((string) $jabatan));
    if ($text === '') {
      return false;
    }
    return strpos($text, 'guru besar') !== false || strpos($text, 'profesor') !== false;
  }

  private function splitAktualKotorFromGaji(float $gaji, bool $kenaTKGB): array
  {
    // Penentuan TPD/TKGB (kotor) mengikuti pola sinkronisasi gaji:
    // - Non Guru Besar/Profesor: TPD = Gaji, TKGB = 0
    // - Guru Besar/Profesor: TPD = 1/3 Gaji, TKGB = 2/3 Gaji
    if ($gaji == 0.0) {
      return [0.0, 0.0];
    }
    if (!$kenaTKGB) {
      return [$gaji, 0.0];
    }

    $tpd = $gaji / 3.0;
    $tkgb = $gaji - $tpd;
    return [$tpd, $tkgb];
  }

  public function index()
  {
    $versi = session('tahun');

    // Data detail kurang/lebih bayar per NIDN dari tabel s_transaksi_2 (Tahun_Versi) dan t_kekurangan
    $detailKekurangan = DB::table('s_transaksi_2 as k')
      ->join('t_kekurangan as k2', function ($join) {
        // Banyak data NIDN memiliki spasi, jadi pakai TRIM agar join konsisten
        $join->on(DB::raw('TRIM(k.NIDN)'), '=', DB::raw('TRIM(k2.nidn)'));
      })
      // Tampilkan kurang dan lebih bayar (selain 0)
      ->where(function ($q) {
        $q->whereRaw('COALESCE(k2.jml_tpd, 0) <> 0')
          ->orWhereRaw('COALESCE(k2.jml_tkgb, 0) <> 0');
      })
      ->where('k2.tahun', $versi)
      ->where('k.Tahun_Versi', $versi)
      ->select(
        'k.NIDN',
        'k.Nama',
        'k.Jenis',
        'k.Jabatan12',
        'k.Aktif',
        'k.Bank',
        'k2.k_tpd1', 'k2.k_tkgb1',
        'k2.k_tpd2', 'k2.k_tkgb2',
        'k2.k_tpd3', 'k2.k_tkgb3',
        'k2.k_tpd4', 'k2.k_tkgb4',
        'k2.k_tpd5', 'k2.k_tkgb5',
        'k2.k_tpd6', 'k2.k_tkgb6',
        'k2.k_tpd7', 'k2.k_tkgb7',
        'k2.k_tpd8', 'k2.k_tkgb8',
        'k2.k_tpd9', 'k2.k_tkgb9',
        'k2.k_tpd10', 'k2.k_tkgb10',
        'k2.k_tpd11', 'k2.k_tkgb11',
        'k2.k_tpd12', 'k2.k_tkgb12',
        'k2.jml_tpd',
        'k2.jml_tkgb',
        'k2.nilai_pjk_tpd',
        'k2.nilai_pjk_tkgb',
        'k2.bersih',
        'k.Gol1', 'k.Gol2', 'k.Gol3', 'k.Gol4', 'k.Gol5', 'k.Gol6', 'k.Gol7', 'k.Gol8', 'k.Gol9', 'k.Gol10', 'k.Gol11', 'k.Gol12',
        'k.Jabatan1', 'k.Jabatan2', 'k.Jabatan3', 'k.Jabatan4', 'k.Jabatan5', 'k.Jabatan6', 'k.Jabatan7', 'k.Jabatan8', 'k.Jabatan9', 'k.Jabatan10', 'k.Jabatan11', 'k.Jabatan12 as Jabatan12Monthly',
        'k.TPD1', 'k.TPD2', 'k.TPD3', 'k.TPD4', 'k.TPD5', 'k.TPD6', 'k.TPD7', 'k.TPD8', 'k.TPD9', 'k.TPD10', 'k.TPD11', 'k.TPD12',
        'k.TKGB1', 'k.TKGB2', 'k.TKGB3', 'k.TKGB4', 'k.TKGB5', 'k.TKGB6', 'k.TKGB7', 'k.TKGB8', 'k.TKGB9', 'k.TKGB10', 'k.TKGB11', 'k.TKGB12',
        'k.Gaji1', 'k.Gaji2', 'k.Gaji3', 'k.Gaji4', 'k.Gaji5', 'k.Gaji6', 'k.Gaji7', 'k.Gaji8', 'k.Gaji9', 'k.Gaji10', 'k.Gaji11', 'k.Gaji12',
        'k.bersihTPD1', 'k.bersihTPD2', 'k.bersihTPD3', 'k.bersihTPD4', 'k.bersihTPD5', 'k.bersihTPD6', 'k.bersihTPD7', 'k.bersihTPD8', 'k.bersihTPD9', 'k.bersihTPD10', 'k.bersihTPD11', 'k.bersihTPD12',
        'k.bersihTKGB1', 'k.bersihTKGB2', 'k.bersihTKGB3', 'k.bersihTKGB4', 'k.bersihTKGB5', 'k.bersihTKGB6', 'k.bersihTKGB7', 'k.bersihTKGB8', 'k.bersihTKGB9', 'k.bersihTKGB10', 'k.bersihTKGB11', 'k.bersihTKGB12',
        'k.No_sp2d_1', 'k.No_sp2d_2', 'k.No_sp2d_3', 'k.No_sp2d_4', 'k.No_sp2d_5', 'k.No_sp2d_6', 'k.No_sp2d_7', 'k.No_sp2d_8', 'k.No_sp2d_9', 'k.No_sp2d_10', 'k.No_sp2d_11', 'k.No_sp2d_12',
        'k.Tgl_sp2d_1', 'k.Tgl_sp2d_2', 'k.Tgl_sp2d_3', 'k.Tgl_sp2d_4', 'k.Tgl_sp2d_5', 'k.Tgl_sp2d_6', 'k.Tgl_sp2d_7', 'k.Tgl_sp2d_8', 'k.Tgl_sp2d_9', 'k.Tgl_sp2d_10', 'k.Tgl_sp2d_11', 'k.Tgl_sp2d_12'
      )
      ->get();

    // Attach nilai DB kotor (belum pajak) per bulan untuk tampilan
    // + nilai Aktual kotor per bulan (hasil hitung ala sinkronisasi: split dari Gaji)
    try {
      $tarifMap = $this->loadTarifPajakMap();
    } catch (\Throwable $e) {
      $tarifMap = [];
      Log::warning('KekuranganBayarController::index - gagal memuat tarif pajak untuk tampilan: ' . $e->getMessage());
    }

    foreach ($detailKekurangan as $row) {
      $jenisRow = (string) ($row->Jenis ?? '');
      $jenisKey = trim($jenisRow);

      // Total DB (plain) - absolute
      $sumDbKotorTPD = 0.0;
      $sumDbKotorTKGB = 0.0;
      $sumDbPajakTPD = 0.0;
      $sumDbPajakTKGB = 0.0;
      $sumDbBersih = 0.0;

      // Total Aktual (hasil hitung sinkronisasi) - absolute
      $sumAktKotorTPD = 0.0;
      $sumAktKotorTKGB = 0.0;
      $sumAktPajakTPD = 0.0;
      $sumAktPajakTKGB = 0.0;
      $sumAktBersih = 0.0;
      for ($i = 1; $i <= 12; $i++) {
        $noSp2d = trim((string) ($row->{'No_sp2d_' . $i} ?? ''));
        $tglSp2d = trim((string) ($row->{'Tgl_sp2d_' . $i} ?? ''));
        $sp2dOk = ($noSp2d !== '' && $tglSp2d !== '');

        $dbTPD = 0;
        $dbTKGB = 0;
        $aktTPD = 0;
        $aktTKGB = 0;
        if ($sp2dOk) {
          $dbKotorTPD = (float) $this->parseMoney($row->{'TPD' . $i} ?? 0);
          $dbKotorTKGB = (float) $this->parseMoney($row->{'TKGB' . $i} ?? 0);
          $dbTPD = (int) round($dbKotorTPD);
          $dbTKGB = (int) round($dbKotorTKGB);

          $gol = trim((string) ($row->{'Gol' . $i} ?? ''));
          $jabatan = (string) ($row->{'Jabatan' . $i} ?? ($row->Jabatan12 ?? ''));
          $kenaTKGB = $this->isGuruBesarAtauProfesor($jabatan);

          $gaji = (float) $this->parseMoney($row->{'Gaji' . $i} ?? 0);
          [$aktKotorTPD, $aktKotorTKGB] = $this->splitAktualKotorFromGaji($gaji, $kenaTKGB);
          $aktTPD = (int) round($aktKotorTPD);
          $aktTKGB = (int) round($aktKotorTKGB);

          // Recompute totals for display using sinkronisasi rules (ABSOLUTE totals)
          $tarif = (float) (($tarifMap[$jenisKey][$gol] ?? 0) ?: 0);

          $dbPajakTPD = $dbKotorTPD * $tarif;
          $dbPajakTKGB = $kenaTKGB ? ($dbKotorTKGB * $tarif) : 0.0;
          $dbBersih = ($dbKotorTPD - $dbPajakTPD) + ($dbKotorTKGB - $dbPajakTKGB);

          $aktPajakTPD = $aktKotorTPD * $tarif;
          $aktPajakTKGB = $kenaTKGB ? ($aktKotorTKGB * $tarif) : 0.0;
          $aktBersih = ($aktKotorTPD - $aktPajakTPD) + ($aktKotorTKGB - $aktPajakTKGB);

          $sumDbKotorTPD += $dbKotorTPD;
          $sumDbKotorTKGB += $dbKotorTKGB;
          $sumDbPajakTPD += $dbPajakTPD;
          $sumDbPajakTKGB += $dbPajakTKGB;
          $sumDbBersih += $dbBersih;

          $sumAktKotorTPD += $aktKotorTPD;
          $sumAktKotorTKGB += $aktKotorTKGB;
          $sumAktPajakTPD += $aktPajakTPD;
          $sumAktPajakTKGB += $aktPajakTKGB;
          $sumAktBersih += $aktBersih;
        }

        $row->{'db_tpd' . $i} = $dbTPD;
        $row->{'db_tkgb' . $i} = $dbTKGB;

        $row->{'exp_tpd' . $i} = $aktTPD;
        $row->{'exp_tkgb' . $i} = $aktTKGB;
      }

      // Override stored totals for display (avoid stale values in t_kekurangan)
      // Kolom tanpa "(Aktual)" = DB
      $row->jml_tpd = $sumDbKotorTPD;
      $row->jml_tkgb = $sumDbKotorTKGB;
      $row->nilai_pjk_tpd = $sumDbPajakTPD;
      $row->nilai_pjk_tkgb = $sumDbPajakTKGB;
      $row->bersih = $sumDbBersih;

      // Kolom "(Aktual)" = hasil hitung sinkronisasi
      $row->jml_tpd_akt = $sumAktKotorTPD;
      $row->jml_tkgb_akt = $sumAktKotorTKGB;
      $row->nilai_pjk_tpd_akt = $sumAktPajakTPD;
      $row->nilai_pjk_tkgb_akt = $sumAktPajakTKGB;
      $row->bersih_akt = $sumAktBersih;
    }

    // Rekap dipisah berdasarkan prefix excel path
    // - rekap_kekurangan/* : Kurang Bayar
    // - rekap_kelebihan/*  : Lebih Bayar
    $rekapKurang = DB::table('u_rekap_kekurangan')
      ->whereRaw('RIGHT(periode, 4) = ?', [$versi])
      ->where(function ($q) {
        $q->where('excel', 'like', 'rekap_kekurangan/%')
          ->orWhere('periode', 'like', 'Kurang%');
      })
      ->orderByDesc('created_at')
      ->get();

    $rekapLebih = DB::table('u_rekap_kekurangan')
      ->whereRaw('RIGHT(periode, 4) = ?', [$versi])
      ->where(function ($q) {
        $q->where('excel', 'like', 'rekap_kelebihan/%')
          ->orWhere('periode', 'like', 'Lebih%');
      })
      ->orderByDesc('created_at')
      ->get();

    return view('admin.kekurangan-bayar', [
      'versi' => $versi,
      'detailKekurangan' => $detailKekurangan,
      'rekapKurang' => $rekapKurang,
      'rekapLebih' => $rekapLebih,
      'bankList' => DB::table('b_bank')->select('nama_bank')->whereNotNull('nama_bank')->where('nama_bank','!=','')->distinct()->orderBy('nama_bank')->pluck('nama_bank'),
    ]);
  }

  public function proses(Request $request)
  {
    $request->validate([
      'periode' => 'required',
      'tipe' => 'required',
      'jenis' => 'required',
      'bank' => 'required',
    ]);

    $versi = session('tahun');
    if (!$versi) {
      return redirect()->route('admin.kekurangan-bayar')->with('error', 'Tahun versi belum dipilih pada sesi.');
    }

    $tipe = $request->input('tipe', 'Semua');
    $jenis = $request->input('jenis', 'Semua');
    $bank = $request->input('bank', 'Semua');

    // Ambil data sesuai logika admin/sinkronisasi:
    // - tarif_pajak dari d_pajak (status=Jenis, akumulasi=Gol{bulan})
    // - pajak TKGB hanya untuk Guru Besar/Profesor
    // - kolom Jan-Des (DB) adalah nilai kotor (belum pajak)
    // - kolom Jan-Des (Aktual) dihitung seperti sinkronisasi: split TPD/TKGB dari Gaji
    // Selisih disimpan sebagai (DB kotor - Aktual kotor) sehingga:
    // - nilai positif = lebih bayar
    // - nilai negatif = kurang bayar
    // Nilai pajak dan bersih dihitung sesuai perhitungan sinkronisasi.
    // Hanya menghitung bulan yang sudah terisi No_sp2d_{n} dan Tgl_sp2d_{n} di s_transaksi_2.

    @set_time_limit(0);
    @ini_set('memory_limit', '-1');
    DB::disableQueryLog();

    try {
      $tarifMap = $this->loadTarifPajakMap();
    } catch (\Throwable $e) {
      $alias = ErrorAlias::fromThrowable($e, 'ADM-KEKURANGAN-PAJAK');
      Log::error('KekuranganBayarController::proses - gagal memuat tarif pajak', [
        'alias' => $alias['code'],
        'versi' => $versi,
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
      ]);
      return redirect()->route('admin.kekurangan-bayar')->with('error', 'Gagal memuat tarif pajak. ' . $alias['message']);
    }

    // Ambil data untuk dihitung
    try {
      $rowsPayload = $this->computeKekuranganPayload((string) $versi, $tipe, $jenis, $bank, $tarifMap, true);
      if (empty($rowsPayload)) {
        return redirect()->route('admin.kekurangan-bayar')->with('warning', 'Tidak ada data baru untuk diproses (mungkin sudah pernah diproses / total pembayaran 0 / belum ada SP2D).');
      }

      $batch = [];
      $batchSize = 300;
      foreach ($rowsPayload as $obj) {
        // convert computed object to insert payload
        $arr = (array) $obj;
        // keep only columns that exist in t_kekurangan
        $allowed = [
          'id','nidn','nama','total_gaji','total_pembayaran',
          'k_tpd1','k_tpd2','k_tpd3','k_tpd4','k_tpd5','k_tpd6','k_tpd7','k_tpd8','k_tpd9','k_tpd10','k_tpd11','k_tpd12',
          'k_tkgb1','k_tkgb2','k_tkgb3','k_tkgb4','k_tkgb5','k_tkgb6','k_tkgb7','k_tkgb8','k_tkgb9','k_tkgb10','k_tkgb11','k_tkgb12',
          'jml_tpd','jml_tkgb','pajak','nilai_pjk_tpd','nilai_pjk_tkgb','bersih',
          'sp2d_tpd','sp2d_tkgb','tgl_tpd','tgl_tkgb','cek_validasi_tpd','cek_validasi_tkgb','tahun'
        ];
        // remove any auxiliary keys (exp_*, db_*, Paid*, etc.) and keep only allowed
        $arr = array_intersect_key($arr, array_flip($allowed));
        $batch[] = $arr;
        if (count($batch) >= $batchSize) {
          DB::table('t_kekurangan')->insert($batch);
          $batch = [];
        }
      }
      if (!empty($batch)) {
        DB::table('t_kekurangan')->insert($batch);
      }

      $generatedCount = DB::table('t_kekurangan')->where('tahun', $versi)->count();
      if ($generatedCount <= 0) {
        return redirect()->route('admin.kekurangan-bayar')->with('error', 'Tidak ada data yang dapat diproses. Pastikan sudah ada No SP2D dan Tgl SP2D terisi di s_transaksi_2.');
      }
    } catch (\Throwable $e) {
      $alias = ErrorAlias::fromThrowable($e, 'ADM-KEKURANGAN-PROSES');
      Log::error('KekuranganBayarController::proses - gagal hitung & simpan kekurangan', [
        'alias' => $alias['code'],
        'versi' => $versi,
        'tipe' => $tipe,
        'jenis' => $jenis,
        'bank' => $bank,
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
      ]);
      return redirect()->route('admin.kekurangan-bayar')->with('error', 'Gagal menghitung kekurangan. ' . $alias['message']);
    }

    // Ambil data Kurang dan Lebih untuk export rekap (opsional).
    $jenisLabel = strtolower(str_replace(' ', '_', $jenis === 'Semua' ? 'semua' : $jenis));
    $tipeLabel = strtolower($tipe === 'Semua' ? 'semua' : $tipe);
    $bankLabel = strtolower($bank === 'Semua' ? 'semua' : $bank);

    $rekapCreated = 0;
    try {
      $base = DB::table('t_kekurangan as ku')
        ->join('s_transaksi_2 as k', function ($join) {
          $join->on(DB::raw('TRIM(ku.nidn)'), '=', DB::raw('TRIM(k.NIDN)'));
        })
        ->where('ku.tahun', $versi)
        ->where('k.Tahun_Versi', $versi);

      if ($tipe !== 'Semua') {
        if ($tipe === 'TPD') {
          $base->whereRaw('(ku.jml_tpd + 0) <> 0');
        } elseif ($tipe === 'TKGB') {
          $base->whereRaw('(ku.jml_tkgb + 0) <> 0');
        }
      }

      if ($jenis !== 'Semua') {
        $base->where('k.Jenis', $jenis);
      }
      if ($bank !== 'Semua') {
        $base->whereRaw('TRIM(k.Bank) = ?', [trim($bank)]);
      }

      $select = [
        'k.NIDN as NIDN',
        'k.Nama as Nama',
        'k.Jenis as Jenis',
        'k.Bank as Bank',
        'k.Jabatan12 as Jabatan12',
        'k.Aktif as Aktif',
        'ku.bersih as delta_bersih',
      ];

      for ($i = 1; $i <= 12; $i++) {
        $select[] = 'k.Gol' . $i;
        $select[] = 'k.Jabatan' . $i;
        $select[] = 'k.TPD' . $i;
        $select[] = 'k.TKGB' . $i;
        $select[] = 'k.Gaji' . $i;
        $select[] = 'k.No_sp2d_' . $i;
        $select[] = 'k.Tgl_sp2d_' . $i;
      }

      // Kurang bayar
      $rowsKurang = (clone $base)
        ->whereRaw('(ku.bersih + 0) < 0')
        ->get($select);

      if (!$rowsKurang->isEmpty()) {
        $this->ensurePublicFolder('rekap_kekurangan');
        $filePrefix = "rekap_kekurangan_jan_des_{$jenisLabel}_{$tipeLabel}_{$bankLabel}_{$versi}";
        $excelFileName = $filePrefix . '.xls';
        $excelRelPath = 'rekap_kekurangan/' . $excelFileName;
        Storage::disk('public')->put($excelRelPath, $this->toExcelHtmlLikeTable($rowsKurang->all(), $tarifMap));

        $this->insertRekapRow([
          'periode' => 'Kurang Jan-Des ' . $versi,
          'pegawai' => (string) $rowsKurang->count(),
          'tipe' => $tipe,
          'bank' => $bank,
          'excel' => $excelRelPath,
          'pdf' => null,
          'created_at' => now(),
          'updated_at' => now(),
        ]);
        $rekapCreated++;
      }

      // Lebih bayar
      $rowsLebih = (clone $base)
        ->whereRaw('(ku.bersih + 0) > 0')
        ->get($select);

      if (!$rowsLebih->isEmpty()) {
        $this->ensurePublicFolder('rekap_kelebihan');
        $filePrefix = "rekap_kelebihan_jan_des_{$jenisLabel}_{$tipeLabel}_{$bankLabel}_{$versi}";
        $excelFileName = $filePrefix . '.xls';
        $excelRelPath = 'rekap_kelebihan/' . $excelFileName;
        Storage::disk('public')->put($excelRelPath, $this->toExcelHtmlLikeTable($rowsLebih->all(), $tarifMap));

        $this->insertRekapRow([
          'periode' => 'Lebih Jan-Des ' . $versi,
          'pegawai' => (string) $rowsLebih->count(),
          'tipe' => $tipe,
          'bank' => $bank,
          'excel' => $excelRelPath,
          'pdf' => null,
          'created_at' => now(),
          'updated_at' => now(),
        ]);
        $rekapCreated++;
      }
    } catch (\Throwable $e) {
      $alias = ErrorAlias::fromThrowable($e, 'ADM-KEKURANGAN-REKAP');
      Log::error('KekuranganBayarController::proses - gagal membuat rekap export', [
        'alias' => $alias['code'],
        'versi' => $versi,
        'tipe' => $tipe,
        'jenis' => $jenis,
        'bank' => $bank,
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
      ]);
      // Tetap sukses menghitung & insert t_kekurangan, hanya rekap export yang gagal.
      return redirect()->route('admin.kekurangan-bayar')->with('success', 'Proses hitung berhasil, namun gagal membuat file rekap. (Kode: ' . $alias['code'] . ')');
    }

    if ($rekapCreated <= 0) {
      return redirect()->route('admin.kekurangan-bayar')->with('success', 'Proses hitung berhasil. Tidak ada data kurang/lebih untuk direkap sesuai filter.');
    }

    return redirect()->route('admin.kekurangan-bayar')->with('success', 'Proses hitung & rekap kurang/lebih bayar berhasil.');
  }

  /**
   * Halaman untuk memilih rekap (Kurang/Lebih) yang akan dihapus.
   */
  public function rekap()
  {
    $versi = session('tahun');
    if (!$versi) {
      return redirect()->route('admin.kekurangan-bayar')->with('error', 'Tahun versi belum dipilih pada sesi.');
    }

    $rekapKurang = DB::table('u_rekap_kekurangan')
      ->whereRaw('RIGHT(periode, 4) = ?', [$versi])
      ->where(function ($q) {
        $q->where('excel', 'like', 'rekap_kekurangan/%')
          ->orWhere('periode', 'like', 'Kurang%');
      })
      ->orderByDesc('created_at')
      ->get();

    $rekapLebih = DB::table('u_rekap_kekurangan')
      ->whereRaw('RIGHT(periode, 4) = ?', [$versi])
      ->where(function ($q) {
        $q->where('excel', 'like', 'rekap_kelebihan/%')
          ->orWhere('periode', 'like', 'Lebih%');
      })
      ->orderByDesc('created_at')
      ->get();

    return view('admin.kekurangan-bayar-rekap', [
      'versi' => $versi,
      'rekapKurang' => $rekapKurang,
      'rekapLebih' => $rekapLebih,
    ]);
  }

  /**
   * Hapus semua data kekurangan & rekap_kekurangan untuk tahun sesi aktif.
   */
  public function destroyTahun(Request $request)
  {
    $versi = session('tahun');

    DB::table('t_kekurangan')->where('tahun', $versi)->delete();
    DB::table('u_rekap_kekurangan')
      ->whereRaw('RIGHT(periode, 4) = ?', [$versi])
      ->delete();

    return redirect()
      ->route('admin.kekurangan-bayar')
      ->with('success', "Semua data kekurangan dan rekap kekurangan pada tahun {$versi} berhasil dihapus.");
  }

  /**
   * Hapus hanya data "Kurang Bayar" (nilai negatif pada jml_tpd atau jml_tkgb) untuk tahun sesi.
   */
  public function destroyKurang(Request $request)
  {
    $versi = session('tahun');

    // Hapus seluruh data yang memiliki ID prefix KKB (Kurang Bayar)
    DB::table('t_kekurangan')
      ->where('tahun', $versi)
      ->where('id', 'like', 'KKB-%')
      ->delete();

    return redirect()
      ->route('admin.kekurangan-bayar')
      ->with('success', "Data 'Kurang Bayar' pada tahun {$versi} berhasil dihapus.");
  }

  /**
   * Hapus hanya data "Lebih Bayar" (nilai positif pada jml_tpd/jml_tkgb) untuk tahun sesi.
   */
  public function destroyLebih(Request $request)
  {
    $versi = session('tahun');

    DB::table('t_kekurangan')
      ->where('tahun', $versi)
      ->where('id', 'like', 'KLB-%')
      ->delete();

    return redirect()
      ->route('admin.kekurangan-bayar')
      ->with('success', "Data 'Lebih Bayar' pada tahun {$versi} berhasil dihapus.");
  }

  /**
   * Hapus rekap terpilih (Kurang/Lebih) untuk tahun sesi aktif.
   * Catatan: hanya menghapus dari tabel rekap (u_rekap_kekurangan), bukan detail t_kekurangan.
   */
  public function destroyRekapSelected(Request $request)
  {
    $versi = session('tahun');
    if (!$versi) {
      return redirect()->route('admin.kekurangan-bayar')->with('error', 'Tahun versi belum dipilih pada sesi.');
    }

    $request->validate([
      'ids' => 'required|array',
      'ids.*' => 'integer',
    ]);

    $ids = array_values(array_filter($request->input('ids', []), function ($v) {
      return is_numeric($v);
    }));

    if (empty($ids)) {
      return redirect()->route('admin.kekurangan-bayar')->with('warning', 'Tidak ada rekap yang dipilih untuk dihapus.');
    }

    // Scope safety: only delete rows for selected session year
    $rows = DB::table('u_rekap_kekurangan')
      ->whereIn('id', $ids)
      ->whereRaw('RIGHT(periode, 4) = ?', [$versi])
      ->get(['id', 'excel', 'pdf']);

    if ($rows->isEmpty()) {
      return redirect()->route('admin.kekurangan-bayar')->with('warning', 'Rekap tidak ditemukan (atau bukan untuk tahun sesi aktif).');
    }

    DB::beginTransaction();
    try {
      // Best-effort delete linked files in storage (public disk)
      foreach ($rows as $r) {
        foreach (['excel', 'pdf'] as $key) {
          $rel = trim((string) ($r->$key ?? ''));
          if ($rel === '') continue;
          try {
            $relNorm = ltrim(str_replace('\\', '/', $rel), '/');
            if (strpos($relNorm, 'storage/') === 0) {
              $relNorm = substr($relNorm, strlen('storage/'));
            }
            Storage::disk('public')->delete($relNorm);
          } catch (\Throwable $e) {
            // ignore file delete errors; still remove DB rows
          }
        }
      }

      $deleted = DB::table('u_rekap_kekurangan')
        ->whereIn('id', $rows->pluck('id')->all())
        ->whereRaw('RIGHT(periode, 4) = ?', [$versi])
        ->delete();

      DB::commit();
    } catch (\Throwable $e) {
      DB::rollBack();
      $alias = ErrorAlias::fromThrowable($e, 'ADM-KEKURANGAN-HAPUS-REKAP');
      Log::error('KekuranganBayarController::destroyRekapSelected - gagal hapus rekap', [
        'alias' => $alias['code'],
        'versi' => $versi,
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
      ]);
      return redirect()->route('admin.kekurangan-bayar')->with('error', 'Gagal menghapus rekap terpilih. ' . $alias['message']);
    }

    return redirect()
      ->route('admin.kekurangan-bayar')
      ->with('success', "Berhasil menghapus {$deleted} rekap (tahun {$versi}).");
  }
}
