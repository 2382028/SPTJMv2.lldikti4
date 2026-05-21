<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class PenggunaController extends Controller
{
  public function index()
  {
    return view('admin.pengguna-akun');
  }

   // Simpan pengguna baru
   public function store(Request $request)
   {
       $request->validate([
           'username' => 'required|string|max:255',
           'email' => 'required|email|unique:users,email',
           'role' => 'required|in:admin,pic,auditor',
           'status' => 'required|in:active,inactive'
       ]);

       User::create($request->all());

       return redirect()->back()->with('success', 'Pengguna berhasil ditambahkan.');
   }

   // Ambil data pengguna untuk diedit
   public function edit($id)
   {
       $user = User::findOrFail($id);
       return response()->json($user);
   }

   // Update data pengguna
   public function update(Request $request, $id)
   {
       $user = User::findOrFail($id);

       $request->validate([
           'username' => 'required|string|max:255',
           'email' => "required|email|unique:users,email,$id",
           'role' => 'required|in:admin,pic,auditor',
           'status' => 'required|in:active,inactive'
       ]);

       $user->update($request->all());

       return redirect()->back()->with('success', 'Pengguna berhasil diperbarui.');
   }

   // Hapus pengguna
   public function destroy($id)
   {
       $user = User::findOrFail($id);
       $user->delete();

       return redirect()->back()->with('success', 'Pengguna berhasil dihapus.');
   }


}
