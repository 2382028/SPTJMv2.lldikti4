<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PasswordDosenController extends Controller
{
  public function update(Request $request)
  {
    $request->validate([
      'new_password' => ['required', 'string', 'min:8', 'confirmed'],
    ]);

    $dosen = Auth::guard('dosen')->user();
    if (!$dosen) {
      return redirect()->route('login');
    }

    $newPassword = (string) $request->input('new_password');

    $nidn = trim((string) ($dosen->nidn ?? ''));
    $nuptk = trim((string) ($dosen->nuptk ?? ''));

    // Prevent reusing the default identifiers as password.
    if (($nidn !== '' && hash_equals($nidn, $newPassword)) || ($nuptk !== '' && hash_equals($nuptk, $newPassword))) {
      return redirect()->route('dosen.dashboard')->with('force_password_error', 'Password baru tidak boleh sama dengan NIDN/NUPTK.');
    }

    // Store as plaintext (legacy-compatible for this application)
    $dosen->password = $newPassword;
    $dosen->save();

    session()->forget('dosen_force_password_reset');

    return redirect()->route('dosen.dashboard')->with('success', 'Password berhasil diperbarui.');
  }
}
