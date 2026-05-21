<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dosen;

class LengkapiDosenController extends Controller
{
  public function index($nidn)
  {
    $dosen = Dosen::where('nidn', $nidn)->first();

    if (!$dosen) {
      return redirect()
        ->back()
        ->with('error', 'Data dosen tidak ditemukan.');
    }

    return view('admin.lengkapi-dosen', compact('dosen'));
  }
}
