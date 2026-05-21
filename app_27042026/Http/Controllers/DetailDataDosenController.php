<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dosen;

class DetailDataDosenController extends Controller
{
  public function show($nidn)
  {
    $dosen = Dosen::where('nidn', $nidn)->firstOrFail();
    return view('pts.detail-data-dosen', compact('dosen'));
  }
}
