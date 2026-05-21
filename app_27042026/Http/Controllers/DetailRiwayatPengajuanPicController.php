<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DetailRiwayatPengajuanPicController extends Controller
{
  public function index(Request $request, $no)
  {
    // Ambil parameter dari route `{no}` secara eksplisit untuk menghindari null
    $no = $no ?? $request->route('no');

    if (!$no) {
      return redirect()
        ->back()
        ->with('error', 'Nomor SPTJM tidak ditemukan.');
    }

    // Ambil data pengajuan berdasarkan kolom no di q_sptjm
    $pengajuan = DB::table('q_sptjm')
      ->where('no', $no)
      ->first();


    if (!$pengajuan) {
      return view('pic.detail-riwayat-pengajuan', [
        'usulanList' => collect(),
        'pengajuan' => null,
        'bulan' => '-',
        'tahun' => '-',
      ]);
    }

    $idUsulan = $pengajuan->id_usulan;
    $bulan = $pengajuan->bulan;
    $tahun = $pengajuan->tahun;
    $kodePts = $pengajuan->kode_pts;

    // Konversi nama bulan ke angka
    $namaBulan = [
      'Januari' => 1,
      'Februari' => 2,
      'Maret' => 3,
      'April' => 4,
      'Mei' => 5,
      'Juni' => 6,
      'Juli' => 7,
      'Agustus' => 8,
      'September' => 9,
      'Oktober' => 10,
      'November' => 11,
      'Desember' => 12,
    ];

    // Tentukan jenis usulan dari prefix ID:
    // - 'BT' or 'ST' => TUKIN (use s_tunjangan_kinerja)
    // - 'B' or 'S'   => SPTJM (use s_transaksi_2)
    $prefix = strtoupper(explode(' ', trim($idUsulan))[0] ?? '');

    if (in_array($prefix, ['BT', 'ST'])) {
      // TUKIN: ambil dari s_tunjangan_kinerja berdasarkan Kode_Usulan dan Kode_PTS
      $usulanList = DB::table('s_tunjangan_kinerja')
        ->where('Kode_Usulan', $idUsulan)
        ->where('Kode_PTS', $kodePts)
        ->select(
          DB::raw('NIDN as nidn'),
          DB::raw('NUPTK as nuptk'),
          DB::raw('Nama as nama'),
          DB::raw('Kode_Cair as no_sp2d'),
          DB::raw('Jenis as jenis')
        )
        ->get();
    } else {
      // SPTJM: ambil dari s_transaksi_2 menggunakan KodeUsulan{bulan}
      $bulanAngka = $namaBulan[$bulan] ?? null;
      if (!$bulanAngka) {
        return view('pic.detail-riwayat-pengajuan', [
          'usulanList' => collect(),
          'pengajuan' => null,
          'bulan' => $bulan,
          'tahun' => $tahun,
        ]);
      }

      $kolomKodeUsulan = 'KodeUsulan' . $bulanAngka;

      // Build COALESCE untuk jabatan 1..12 (opsional, tidak dipakai di view)
      $jabatanParts = [];
      for ($i = 1; $i <= 12; $i++) {
        $jabatanParts[] = "NULLIF(Jabatan{$i}, '')";
      }
      $jabatanSql = implode(', ', $jabatanParts);

      $usulanList = DB::table('s_transaksi_2')
        ->where('Kode_PT', $kodePts)
        ->where('Tahun_Versi', $tahun)
        ->where($kolomKodeUsulan, $idUsulan)
        ->select(
          DB::raw('NIDN as nidn'),
          DB::raw('NUPTK as nuptk'),
          DB::raw('Nama as nama'),
          DB::raw("COALESCE($jabatanSql) as jabatan"),
          DB::raw('Jenis as jenis'),
          DB::raw("No_sp2d_{$bulanAngka} as no_sp2d")
        )
        ->get();
    }

    // For Tukin usulan (BT/ST): if NIDN is empty, fallback to NUPTK so searches by NIDN will match NUPTK
    if (in_array($prefix, ['BT', 'ST'])) {
      $usulanList = collect($usulanList)->map(function ($item) {
        $nidn = trim((string) ($item->nidn ?? ''));
        $nuptk = trim((string) ($item->nuptk ?? ''));
        if ($nidn === '' && $nuptk !== '') {
          $item->nidn = $nuptk;
        }
        return $item;
      });
    }

    // Prepare identifier: prefer NIDN; if missing fallback to NUPTK
    $usulanList = collect($usulanList)->map(function ($item) {
      $nidn = trim((string) ($item->nidn ?? ''));
      $nuptk = trim((string) ($item->nuptk ?? ''));
      if ($nidn !== '') {
        $item->identifier = $nidn;
      } elseif ($nuptk !== '') {
        $item->identifier = $nuptk;
      } else {
        $item->identifier = '-';
      }
      return $item;
    });

    // Sort by nama (alphabet) and split into PNS vs NON PNS based on kolom 'jenis'
    $sorted = $usulanList->sortBy(function ($r) {
      return mb_strtolower((string) ($r->nama ?? ''), 'UTF-8');
    })->values();

    $usulanPns = $sorted->filter(function ($r) {
      return strtoupper(trim((string) ($r->jenis ?? ''))) === 'PNS';
    })->values();

    $usulanNonPns = $sorted->filter(function ($r) {
      return strtoupper(trim((string) ($r->jenis ?? ''))) === 'NON PNS';
    })->values();

    return view('pic.detail-riwayat-pengajuan', [
      'usulanList' => $usulanList,
      'pengajuan' => $pengajuan,
      'bulan' => $bulan,
      'tahun' => $tahun,
      'usulanPns' => $usulanPns,
      'usulanNonPns' => $usulanNonPns,
    ]);
  }
}
