<?php

namespace App\Http\Controllers\Pic;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BiayaController extends Controller
{
  public function getBiaya(Request $request)
  {
    $golongan = $request->input('golongan');
    $masa_kerja = $request->input('masa_kerja');

    $gaji = DB::table('c_grade')
      ->where('gol', $golongan)
      ->where('masa_kerja', $masa_kerja)
      ->value('nominal');

    return response()->json(['gaji' => $gaji ?? 'Data tidak ditemukan']);
  }
}
