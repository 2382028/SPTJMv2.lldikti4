<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pegawai;

class StatusPegawaiController extends Controller
{
  public function index()
  {
    $g_pegawai = Pegawai::all();
    return view('admin.status-pegawai', compact('g_pegawai'));
  }

  public function store(Request $request)
    {
      $request->validate([
        'kode' => 'required|string|max:50|unique:g_pegawai,kode',
        'jenis' => 'required|string|max:50',
    ]);

    Pegawai::create([
        'kode' => $request->kode,
        'jenis' => $request->jenis,
    ]);

    return redirect()->back()->with('success', 'Status Pegawai berhasil ditambahkan.');
    }

    public function edit($kode)
    {
        $pegawai = Pegawai::findOrFail($kode);
        return response()->json($pegawai);
    }

    public function update(Request $request, $kode)
    {
        $pegawai = Pegawai::findOrFail($kode);

        $request->validate([
            'kode' => 'required|string|max:50',
            'jenis' => 'nullable|string|max:50',
        ]);

        $pegawai->update([
            'kode' => $request->kode,
            'jenis' => $request->jenis,
        ]);

        return redirect()->back()->with('success', 'Status Pegawai berhasil diperbarui.');
    }

    public function destroy($kode)
{
  $pegawai = Pegawai::findOrFail($kode);
  $pegawai->delete();
  return redirect()->back()->with('success', 'Status Pegawai berhasil dihapus.');
}

}