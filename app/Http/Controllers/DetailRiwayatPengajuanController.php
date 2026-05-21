<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class DetailRiwayatPengajuanController extends Controller
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

    // Jika data pengajuan tidak ditemukan
    if (!$pengajuan) {
      if ($request->ajax()) {
        // Kembalikan response kosong untuk DataTables
        return DataTables::of(collect())->make(true);
      }

      return view('pts.detail-riwayat-pengajuan', [
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

    $bulanAngka = $namaBulan[$bulan] ?? null;

    // Tentukan jenis usulan dari prefix ID: B/S => SPTJM, ST/BT => Tukin
    $prefix = explode(' ', trim($idUsulan))[0] ?? '';

    // Jika request dari DataTables (AJAX), kembalikan data detail dalam format JSON
    if ($request->ajax()) {
      if (in_array($prefix, ['BT', 'ST'])) {
        // BT/ST: ambil NIDN/NUPTK dari s_tunjangan_kinerja, lalu ambil SP2D dari s_transaksi_2
        // dengan mencocokkan nidn atau nuptk (dan dibatasi kode_pt + tahun_versi).
        $kolomKodeUsulan = $bulanAngka ? ('KodeUsulan' . $bulanAngka) : null;
        if ($bulanAngka) {
          $noSp2dExpr = DB::raw("MAX(st2.no_sp2d_{$bulanAngka}) as no_sp2d_1");
        } else {
          $noSp2dExpr = DB::raw("NULL as no_sp2d_1");
        }

        $usulanQuery = DB::table('s_tunjangan_kinerja as stk')
          ->where('stk.Kode_Usulan', $idUsulan)
          ->where('stk.Kode_PTS', $kodePts)
          ->leftJoin('s_transaksi_2 as st2', function ($join) use ($kodePts, $tahun, $kolomKodeUsulan, $idUsulan) {
            // Penting: grup kondisi OR agar filter kode_pt/tahun_versi berlaku untuk keduanya.
            $join->on(function ($join) {
              $join
                ->on('st2.nidn', '=', 'stk.NIDN')
                ->orOn('st2.nuptk', '=', 'stk.NUPTK');
            });

            // Filter tambahan agar tidak "nyangkut" ke transaksi PTS/tahun/usulan lain
            $join
              ->where('st2.kode_pt', '=', $kodePts)
              ->where('st2.tahun_versi', '=', $tahun);

            if ($kolomKodeUsulan) {
              $join->where("st2.$kolomKodeUsulan", '=', $idUsulan);
            }
          })
          ->select(
            DB::raw('stk.NIDN as nidn'),
            DB::raw('stk.NUPTK as nuptk'),
            DB::raw('stk.Nama as nama'),
            $noSp2dExpr
          )
          ->groupBy('stk.NIDN', 'stk.NUPTK', 'stk.Nama');
      } else {
        // Default: SPTJM dari s_transaksi_2 menggunakan KodeUsulan{bulan}
        if (!$bulanAngka) {
          // Jika bulan tidak dikenali, kembalikan dataset kosong
          $usulanQuery = DB::table('s_transaksi_2')->whereRaw('1 = 0');
        } else {
          $kolomKodeUsulan = 'KodeUsulan' . $bulanAngka;
          $usulanQuery = DB::table('s_transaksi_2')
            ->where('kode_pt', $kodePts)
            ->where('tahun_versi', $tahun)
            ->where($kolomKodeUsulan, $idUsulan)
            ->select('nidn', 'nuptk', 'nama', 'jabatan1', "no_sp2d_$bulanAngka as no_sp2d_1");
        }
      }

      return DataTables::of($usulanQuery)
        ->addColumn('tanggal_usulan', function () use ($pengajuan) {
          return $pengajuan->tanggal_usulan ?? '-';
        })
        ->addColumn('status', function () use ($pengajuan) {
          return $pengajuan->status ?? '-';
        })
        ->make(true);
    }

    // Non-AJAX: render halaman awal, data akan di-load via DataTables AJAX
    // Prepare server-side lists for PNS / NON PNS when rendering page
    // Build a query similar to AJAX branch but executed to get collections
    if (in_array($prefix, ['BT', 'ST'])) {
      $usulanList = DB::table('s_tunjangan_kinerja')
        ->where('Kode_Usulan', $idUsulan)
        ->where('Kode_PTS', $kodePts)
        ->select(DB::raw('NIDN as nidn'), DB::raw('NUPTK as nuptk'), DB::raw('Nama as nama'), DB::raw('Kode_Cair as no_sp2d_1'), DB::raw('Jenis as jenis'))
        ->get();
    } else {
      if (!$bulanAngka) {
        $usulanList = collect();
      } else {
        $kolomKodeUsulan = 'KodeUsulan' . $bulanAngka;
        $usulanList = DB::table('s_transaksi_2')
          ->where('kode_pt', $kodePts)
          ->where('tahun_versi', $tahun)
          ->where($kolomKodeUsulan, $idUsulan)
          ->select('nidn', 'nuptk', 'nama', DB::raw("no_sp2d_{$bulanAngka} as no_sp2d_1"), DB::raw('Jenis as jenis'))
          ->get();
      }
    }

    // Normalize identifier and sort by name
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

    $sorted = $usulanList->sortBy(function ($r) {
      return mb_strtolower((string) ($r->nama ?? ''), 'UTF-8');
    })->values();

    $usulanPns = $sorted->filter(function ($r) {
      return strtoupper(trim((string) ($r->jenis ?? ''))) === 'PNS';
    })->values();

    $usulanNonPns = $sorted->filter(function ($r) {
      return strtoupper(trim((string) ($r->jenis ?? ''))) === 'NON PNS';
    })->values();

    return view('pts.detail-riwayat-pengajuan', [
      'pengajuan' => $pengajuan,
      'bulan' => $bulan,
      'tahun' => $tahun,
      'usulanPns' => $usulanPns,
      'usulanNonPns' => $usulanNonPns,
    ]);
  }
}