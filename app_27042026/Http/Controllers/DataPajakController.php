<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pajak;
use App\Services\IdentitasPemotongJsonStore;

class DataPajakController extends Controller
{
  public function index()
  {
    $d_pajak = Pajak::all();
    $identitas_pemotong = app(IdentitasPemotongJsonStore::class)->all();
    return view('admin.data-pajak', compact('d_pajak', 'identitas_pemotong'));
  }

  public function store(Request $request)
  {
    $request->validate([
      'status' => 'required|string|max:50',
      'akumulasi' => 'required|string|max:50',
      'tarif_pajak' => 'required|numeric|min:0'
    ]);

    $tarif = str_replace(',', '.', $request->tarif_pajak);
    Pajak::create([
      'status' => $request->status,
      'akumulasi' => $request->akumulasi,
      'tarif_pajak' => (float) $tarif
    ]);

    return redirect()->back()->with('success', 'Data pajak berhasil ditambahkan!');
  }

  public function update(Request $request, $no)
  {
    $pajak = Pajak::findOrFail($no);

    $request->validate([
      'status' => 'required|string|max:50',
      'akumulasi' => 'required|string|max:50',
      'tarif_pajak' => 'required|numeric|min:0'
    ]);

    $pajak->update($request->only('status', 'akumulasi', 'tarif_pajak'));

    return redirect()->back()->with('success', 'Data pajak berhasil diperbarui!');
  }


  public function destroy($no)
  {
    Pajak::findOrFail($no)->delete();
    return redirect()->back()->with('success', 'Data pajak berhasil dihapus.');
  }
}
