<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Jabatan;

class JabatanController extends Controller
{
  public function index()
  {
    $e_jabatan = Jabatan::all();
    return view('admin.jabatan', compact('e_jabatan'));
  }

  public function store(Request $request)
  {
    $request->validate([
      'kode' => 'required|string|max:50|unique:e_jabatan,kode',
      'jabatan' => 'required|string|max:50',
      'nominal' => 'required|numeric',
    ]);

    Jabatan::create([
      'kode' => $request->kode,
      'jabatan' => $request->jabatan,
      'nominal' => $request->nominal,
    ]);

    return redirect()->back()->with('success', 'Jabatan berhasil ditambahkan.');
  }

  public function edit($kode)
  {
    $jabatan = Jabatan::findOrFail($kode);
    return response()->json($jabatan);
  }

  public function update(Request $request, $kode)
  {
    $jabatan = Jabatan::findOrFail($kode);

    $request->validate([
      'kode' => 'required|string|max:50',
      'jabatan' => 'nullable|string|max:50',
      'nominal' => 'nullable|numeric',
    ]);

    $jabatan->update([
      'kode' => $request->kode,
      'jabatan' => $request->jabatan,
      'nominal' => $request->nominal,
    ]);

    return redirect()->back()->with('success', 'Jabatan berhasil diperbarui.');
  }

  public function destroy($kode)
  {
    $jabatan = Jabatan::findOrFail($kode);
    $jabatan->delete();
    return redirect()->back()->with('success', 'Jabatan berhasil dihapus.');
  }
}
