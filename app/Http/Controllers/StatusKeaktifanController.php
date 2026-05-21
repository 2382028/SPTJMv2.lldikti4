<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Keaktifan;

class StatusKeaktifanController extends Controller
{
  public function index()
  {
    $f_keaktifan = Keaktifan::all();
    return view('admin.status-keaktifan', compact('f_keaktifan'));
  }

  public function store(Request $request)
  {
    $request->validate([
      'kode' => 'required|string|max:50|unique:f_keaktifan,kode',
      'aktif' => 'required|string|max:50',
    ]);

    Keaktifan::create([
      'kode' => $request->kode,
      'aktif' => $request->aktif,
    ]);

    return redirect()->back()->with('success', 'Status Keaktifan berhasil ditambahkan.');
  }

  public function edit($kode)
  {
    $keaktifan = Keaktifan::findOrFail($kode);
    return response()->json($keaktifan);
  }

  public function update(Request $request, $kode)
  {
    $keaktifan = Keaktifan::findOrFail($kode);

    $request->validate([
      'kode' => 'required|string|max:50',
      'aktif' => 'nullable|string|max:50',
    ]);

    $keaktifan->update([
      'kode' => $request->kode,
      'aktif' => $request->aktif,
    ]);

    return redirect()->back()->with('success', 'Jabatan berhasil diperbarui.');
  }

  public function destroy($kode)
  {
    $keaktifan = Keaktifan::findOrFail($kode);
    $keaktifan->delete();
    return redirect()->back()->with('success', 'Status Keaktifan berhasil dihapus.');
  }
}
