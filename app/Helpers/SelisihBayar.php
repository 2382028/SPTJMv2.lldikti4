<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class SelisihBayar
{
  public static function computeFromTransaksi($transaksiTahun): array
  {
    $selisihTpd = 0.0;
    $selisihTkgb = 0.0;
    $selisihPajakTpd = 0.0;
    $selisihPajakTkgb = 0.0;
    $selisihBersihTpd = 0.0;
    $selisihBersihTkgb = 0.0;

    if (!$transaksiTahun) {
      return [
        'selisihTpd' => 0,
        'selisihTkgb' => 0,
        'selisihPajakTpd' => 0,
        'selisihPajakTkgb' => 0,
        'selisihBersihTpd' => 0,
        'selisihBersihTkgb' => 0,
      ];
    }

    $tarifMap = self::loadTarifPajakMap();
    $jenis = trim((string) ($transaksiTahun->Jenis ?? ''));

    for ($i = 1; $i <= 12; $i++) {
      $noSp2d = trim((string) ($transaksiTahun->{"No_sp2d_{$i}"} ?? ''));
      $tglSp2d = trim((string) ($transaksiTahun->{"Tgl_sp2d_{$i}"} ?? ''));
      // Match /admin/kekurangan-bayar: only check non-empty (no special '-' handling)
      if ($noSp2d === '' || $tglSp2d === '') {
        continue;
      }

      $dbTpd = self::parseMoney($transaksiTahun->{"TPD{$i}"} ?? 0);
      $dbTkgb = self::parseMoney($transaksiTahun->{"TKGB{$i}"} ?? 0);
      $gaji = self::parseMoney($transaksiTahun->{"Gaji{$i}"} ?? 0);
      $jabatan = $transaksiTahun->{"Jabatan{$i}"} ?? ($transaksiTahun->Jabatan12 ?? '');

      $gol = trim((string) ($transaksiTahun->{"Gol{$i}"} ?? ''));
      $tarif = 0.0;
      if ($jenis !== '' && $gol !== '' && isset($tarifMap[$jenis][$gol])) {
        $tarif = (float) ($tarifMap[$jenis][$gol] ?? 0);
      }

      $kenaTkgb = self::isGuruBesarAtauProfesor($jabatan);
      [$aktTpd, $aktTkgb] = self::splitAktualKotorFromGaji($gaji, $kenaTkgb);

      $selisihTpd += ($dbTpd - $aktTpd);
      $selisihTkgb += ($dbTkgb - $aktTkgb);

      // Pajak + Bersih mengikuti kekurangan-bayar (tarif pajak berdasarkan Jenis & Gol)
      $dbPajakTpd = $dbTpd * $tarif;
      $dbPajakTkgb = $kenaTkgb ? ($dbTkgb * $tarif) : 0.0;

      $aktPajakTpd = $aktTpd * $tarif;
      $aktPajakTkgb = $kenaTkgb ? ($aktTkgb * $tarif) : 0.0;

      $selisihPajakTpd += ($dbPajakTpd - $aktPajakTpd);
      $selisihPajakTkgb += ($dbPajakTkgb - $aktPajakTkgb);

      $dbBersihTpd = $dbTpd - $dbPajakTpd;
      $dbBersihTkgb = $dbTkgb - $dbPajakTkgb;
      $aktBersihTpd = $aktTpd - $aktPajakTpd;
      $aktBersihTkgb = $aktTkgb - $aktPajakTkgb;

      $selisihBersihTpd += ($dbBersihTpd - $aktBersihTpd);
      $selisihBersihTkgb += ($dbBersihTkgb - $aktBersihTkgb);
    }

    return [
      'selisihTpd' => (float) $selisihTpd,
      'selisihTkgb' => (float) $selisihTkgb,
      'selisihPajakTpd' => (float) $selisihPajakTpd,
      'selisihPajakTkgb' => (float) $selisihPajakTkgb,
      'selisihBersihTpd' => (float) $selisihBersihTpd,
      'selisihBersihTkgb' => (float) $selisihBersihTkgb,
    ];
  }

  private static function loadTarifPajakMap(): array
  {
    static $cache = null;
    if (is_array($cache)) {
      return $cache;
    }

    $tarifMap = [];
    try {
      $rows = DB::table('d_pajak')->select('status', 'akumulasi', 'tarif_pajak')->get();
      foreach ($rows as $r) {
        $status = trim((string) ($r->status ?? ''));
        $akum = trim((string) ($r->akumulasi ?? ''));
        if ($status === '' || $akum === '') {
          continue;
        }
        $tarifMap[$status][$akum] = (float) ($r->tarif_pajak ?? 0);
      }
    } catch (\Throwable $e) {
      // If d_pajak missing or DB unavailable, tariff defaults to 0.
      $tarifMap = [];
    }

    $cache = $tarifMap;
    return $tarifMap;
  }

  private static function isGuruBesarAtauProfesor($jabatan): bool
  {
    $text = strtolower(trim((string) $jabatan));
    if ($text === '') {
      return false;
    }
    return str_contains($text, 'guru besar') || str_contains($text, 'profesor');
  }

  /**
   * Split actual kotor from the Gaji field, matching /admin/kekurangan-bayar:
   * - Non Guru Besar/Profesor: TPD = Gaji, TKGB = 0
   * - Guru Besar/Profesor: TPD = 1/3 Gaji, TKGB = 2/3 Gaji
   */
  private static function splitAktualKotorFromGaji(float $gaji, bool $kenaTkgb): array
  {
    $gaji = (float) $gaji;
    if ($gaji <= 0) {
      return [0.0, 0.0];
    }

    if (!$kenaTkgb) {
      return [$gaji, 0.0];
    }

    $tpd = $gaji / 3.0;
    $tkgb = $gaji - $tpd;
    return [$tpd, $tkgb];
  }

  private static function parseMoney($value): float
  {
    // Match KekuranganBayarController::parseMoney
    if ($value === null) {
      return 0.0;
    }
    if (is_int($value) || is_float($value)) {
      return (float) $value;
    }
    $text = trim((string) $value);
    if ($text === '') {
      return 0.0;
    }
    // common DB values are like "1.234.567" or "1234567"; treat as integer money
    $text = preg_replace('/[^0-9\-]/', '', $text);
    if ($text === '' || $text === '-') {
      return 0.0;
    }
    return (float) $text;
  }
}
