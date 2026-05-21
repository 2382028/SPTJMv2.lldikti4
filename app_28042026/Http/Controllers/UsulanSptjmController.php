<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UsulanSptjmController extends Controller
{
  public function index()
  {
    return view('admin.usulan-sptjm');
  }

  public function getData(Request $request)
  {
    $tipeSptjm = $request->input('pilihsptjm');
    $bulan = $request->input('bulan');
    $status = $request->input('status');
    $currentYear = session('tahun') ?? date('Y'); // prefer session year

    // Query dasar dengan filter tahun sesuai session (fallback ke tahun sekarang)
    $query = DB::table('q_sptjm')->where('tahun', $currentYear);

    // Filter Tipe SPTJM/TUKIN (semua dari q_sptjm)
    if ($tipeSptjm !== 'All') {
      switch ($tipeSptjm) {
        case 'SPTJM Berjalan':
          // Include codes that start with 'B' but exclude 'BT' (TUKIN Berjalan)
          $query->where('id_usulan', 'LIKE', 'B%')
                ->whereRaw("id_usulan NOT LIKE 'BT%'");
          break;
        case 'SPTJM Susulan':
          // Include codes that start with 'S' but exclude 'ST' (TUKIN Susulan)
          $query->where('id_usulan', 'LIKE', 'S%')
                ->whereRaw("id_usulan NOT LIKE 'ST%'");
          break;
        case 'TUKIN Berjalan':
          $query->where('id_usulan', 'LIKE', 'BT%');
          break;
        case 'TUKIN Susulan':
          $query->where('id_usulan', 'LIKE', 'ST%');
          break;
      }
    }

    // Filter Bulan
    if ($bulan !== 'All') {
      $query->where('bulan', $bulan);
    }

    // Filter Status
    if (!empty($status) && $status !== 'All') {
      $query->where('status', $status);
    }

    // Ambil data utama (B% atau S%)
    $dataUtama = $query->get();

    // Ambil data `id_usulan = 0` hanya jika status = 'Tolak'
    $dataZero = collect();
    if ($status === 'Tolak') {
      $dataZero = DB::table('q_sptjm')
        ->where('id_usulan', 0)
        ->where('status', 'Tolak')
        ->where('tahun', $currentYear)
        ->when($bulan !== 'All', function ($q) use ($bulan) {
          return $q->where('bulan', $bulan);
        })
        ->get();
    }

    // Gabungkan hasilnya
    $dataUsulan = $dataUtama->merge($dataZero);

    return response()->json([
      'success' => true,
      'data' => $dataUsulan,
    ]);
  }
}