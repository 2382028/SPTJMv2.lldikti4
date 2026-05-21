<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
  public function index()
  {
    $users = User::all();
    return view('admin.pengguna-akun', compact('users'));
  }

  public function store(Request $request)
  {
    $request->validate([
      'email' => 'required|string|unique:users,email',
      'password' => 'required|string|min:3',
      'role' => 'required|in:admin,pic,pts,auditor',
      'active' => 'required|in:1,0',
      'cp' => 'nullable|string|max:255',
    ]);

    User::create([
      'email' => $request->email,
      'password' => $request->password,
      'role' => $request->role,
      'active' => $request->active,
      'cp' => $request->cp,
    ]);

    return redirect()
      ->back()
      ->with('add-success', 'Pengguna berhasil ditambahkan.');
  }

  public function update(Request $request, $id)
  {
    Log::info('Update Request:', $request->all());

    $user = User::findOrFail($id);

    $request->validate([
      'email' => "required|string|unique:users,email,$id",
      'role' => 'required|in:admin,pic,pts,auditor',
      'active' => 'required|in:1,0',
      'cp' => 'nullable|string|max:255',
    ]);

    $data = $request->only('email', 'role', 'active', 'cp');

    if (!empty($request->password) && $request->password !== 'undefined') {
      $data['password'] = $request->password;
    }

    $user->update($data);

    return redirect()
      ->back()
      ->with('edit-success', 'Pengguna berhasil diperbarui.');
  }

  public function destroy($id)
  {
    $user = User::findOrFail($id);
    $user->delete();

    return redirect()
      ->back()
      ->with('success', 'Pengguna berhasil dihapus.');
  }
}
