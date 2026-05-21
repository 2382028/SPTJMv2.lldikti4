<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Perubahan;

class StatusPerubahanController extends Controller
{
  public function index()
  {
    $h_perubahan = Perubahan::all();
    return view('admin.status-perubahan', compact('h_perubahan'));
  }

  public function store(Request $request)
    {
      $request->validate([
        'kode' => 'required|integer|max:50|unique:h_perubahan,kode',
        'status_perubahan' => 'required|string|max:50',
    ]);

    Perubahan::create([
        'kode' => $request->kode,
        'status_perubahan' => $request->status_perubahan,
    ]);

    return redirect()->back()->with('success', 'Status Perubahan berhasil ditambahkan.');
    }

    public function edit($kode)
    {
        $perubahan = Perubahan::findOrFail($kode);
        return response()->json($perubahan);
    }

    public function update(Request $request, $kode)
    {
        $perubahan = Perubahan::findOrFail($kode);

        $request->validate([
            'kode' => 'required|integer|max:50',
            'status_oerubahan' => 'nullable|string|max:50',
        ]);

        $perubahan->update([
            'kode' => $request->kode,
            'status_perubahan' => $request->status_perubahan,
        ]);

        return redirect()->back()->with('success', 'Status Perubahan berhasil diperbarui.');
    }

    public function destroy($kode)
{
  $perubahan = Perubahan::findOrFail($kode);
  $perubahan->delete();
  return redirect()->back()->with('success', 'Status Perubahan berhasil dihapus.');
}
}