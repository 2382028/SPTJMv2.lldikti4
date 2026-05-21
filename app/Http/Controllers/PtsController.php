<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class PtsController extends Controller
{
  public function index()
  {
    $kodePts = Auth::guard('pts')->user()->kode_pts;
    $tahun = session('tahun'); // masih boleh dari session
    return view('pts.dashboard', compact('kodePts', 'tahun'));
  }
}
