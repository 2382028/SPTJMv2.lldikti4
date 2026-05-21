<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\APts;
use App\Models\ADosen;
use Illuminate\Support\Facades\DB;
use App\Helpers\ActiveYears;
use App\Services\LoginBackgroundService;

class AuthController extends Controller
{
  // Menampilkan form login
  public function showLoginForm()
  {
    // Jika sudah login (guard manapun), arahkan ke dashboard masing-masing
    if (Auth::guard('pts')->check()) {
      return redirect()->route('pts.dashboard');
    }
    if (Auth::guard('dosen')->check()) {
      return redirect()->route('dosen.dashboard');
    }
    if (Auth::guard('web')->check()) {
      $user = Auth::guard('web')->user();
      switch ($user->role) {
        case 'admin':
          return redirect()->route('admin.dashboard');
        case 'pic':
          return redirect()->route('pic.dashboard');
        case 'auditor':
          return redirect()->route('auditor.dashboard');
        case 'pts':
          return redirect()->route('pts.dashboard');
      }
    }

    $activeYears = ActiveYears::load();
    if (!empty($activeYears)) {
      $tahun_versi = DB::table('s_transaksi_2')
        ->whereIn('Tahun_Versi', $activeYears)
        ->groupBy('Tahun_Versi')
        ->orderBy('Tahun_Versi', 'desc')
        ->pluck('Tahun_Versi');
    } else {
      // Jika semua non-aktif, tampilkan tahun saat ini
      $tahun_versi = collect([date('Y')]);
    }

    $loginBackgroundUrl = LoginBackgroundService::getAssetUrl();
    $loginHeaderMode = LoginBackgroundService::getHeaderMode();
    return view('auth.login', compact('tahun_versi', 'loginBackgroundUrl', 'loginHeaderMode'));
  }

  // Proses login
  public function login(Request $request)
  {
    $request->validate([
      'login' => 'required', // login = kode_pts / email / nidn / nuptk
      'password' => 'required',
      'tahun' => 'required|numeric',
    ]);

    $login = trim((string) $request->login);

    // === Coba login sebagai PTS ===
    $pts = APts::where('kode_pts', $login)
      ->where('aktif', 1)
      ->first();

    if ($pts) {
      if ($request->password === $pts->password) {
        session(['tahun' => $request->tahun]);
        // Inisialisasi bulan (1-12) pada login PTS
        session(['bulan' => (int) date('n')]);
        Auth::guard('pts')->login($pts);
        $request->session()->regenerate();
        // Debug: tampilkan session 'bulan' lalu hentikan eksekusi
        // print_r(session('bulan'));
        // exit;
        return redirect()->route('pts.dashboard');
      } else {
        return redirect()
          ->back()
          ->with('error', 'Password PTS salah.');
      }
    }

    // === Coba login sebagai DOSEN (a_dosen) ===
    $dosen = ADosen::where(function ($q) use ($login) {
      $q->where('nidn', $login)
        ->orWhere('nuptk', $login);
    })->first();

    if ($dosen && (int) ($dosen->aktif ?? 0) !== 1) {
      return redirect()
        ->back()
        ->with('error', 'Akun dosen Anda belum aktif.');
    }

    if ($dosen && (int) ($dosen->aktif ?? 0) === 1) {
      $storedPassword = (string) ($dosen->password ?? '');
      $ok = false;

      if ($storedPassword !== '') {
        // Plaintext password check (sesuai permintaan: tanpa hash)
        $ok = hash_equals($storedPassword, (string) $request->password);
      }

      if (!$ok) {
        return redirect()
          ->back()
          ->with('error', 'Password dosen salah.');
      }

      // If the dosen is still using default password (nidn/nuptk), force reset via modal on dashboard.
      $nidn = trim((string) ($dosen->nidn ?? ''));
      $nuptk = trim((string) ($dosen->nuptk ?? ''));
      $forceReset = false;
      $typedPassword = trim((string) $request->password);
      if ($typedPassword !== '') {
        if ($nidn !== '') {
          $forceReset = $forceReset || hash_equals($typedPassword, $nidn);
        }
        if (!$forceReset && $nuptk !== '') {
          $forceReset = $forceReset || hash_equals($typedPassword, $nuptk);
        }
      }

      session([
        'tahun' => $request->tahun,
        'bulan' => (int) date('n'),
        'role' => 'dosen',
        'nidn' => $dosen->nidn,
        'nuptk' => $dosen->nuptk,
        'kode_pts' => $dosen->kode_pts,
      ]);

      if ($forceReset) {
        session(['dosen_force_password_reset' => true]);
      } else {
        session()->forget('dosen_force_password_reset');
      }

      Auth::guard('dosen')->login($dosen);
      $request->session()->regenerate();
      return redirect()->route('dosen.dashboard');
    }

    // === Coba login sebagai user biasa ===
    $user = User::where('email', $login)->first();

    if (!$user) {
      return redirect()
        ->back()
        ->with('error', 'Email / Kode PTS tidak ditemukan.');
    }

    if ($request->password !== $user->password) {
      return redirect()
        ->back()
        ->with('error', 'Password salah.');
    }

    if ($user->active != 1) {
      return redirect()
        ->back()
        ->with('error', 'Akun Anda belum aktif.');
    }
    session(['tahun' => $request->tahun]);
    // Inisialisasi bulan (1-12) pada login user biasa
    session(['bulan' => (int) date('n')]);
    $sesi = session('tahun');
    Auth::login($user);
    $request->session()->regenerate();
    // Debug: tampilkan session 'bulan' lalu hentikan eksekusi
    // print_r(session('bulan'));
    // exit;
    // Redirect berdasarkan role
    switch ($user->role) {
      case 'admin':
        return redirect()->route('admin.dashboard');
      case 'pic':
        return redirect()->route('pic.dashboard');
      case 'auditor':
        return redirect()->route('auditor.dashboard');
      case 'pts':
        return redirect()->route('pts.dashboard');
      default:
        return redirect()
          ->route('login')
          ->with('error', 'Role tidak valid.');
    }
  }

  public function logout(Request $request)
  {
    if (Auth::guard('pts')->check()) {
      Auth::guard('pts')->logout();
    }
    if (Auth::guard('dosen')->check()) {
      Auth::guard('dosen')->logout();
    }
    if (Auth::guard('web')->check()) {
      Auth::guard('web')->logout();
    }

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/login');
  }
}