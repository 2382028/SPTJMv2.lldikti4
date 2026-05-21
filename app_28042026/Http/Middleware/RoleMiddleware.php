<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
  public function handle(Request $request, Closure $next, ...$roles)
  {
    $user = Auth::user();

    if (!$user) {
      abort(403, 'Kamu tidak ada akses ke halaman tersebut!');
    }

    if (empty($roles)) {
      abort(403, 'Kamu tidak ada akses ke halaman tersebut!');
    }

    $role = (string) ($user->role ?? '');

    if ($role == '' || !in_array($role, $roles, true)) {
      // UX: jika auditor membuka URL admin yang memang ia audit, arahkan ke halaman auditor (read-only)
      if ($role === 'auditor' && in_array('admin', $roles, true)) {
        if ($request->is('admin/data-dosen')) {
          return redirect()->route('auditor.data-dosen');
        }
        if ($request->is('admin/laporan-keuangan')) {
          return redirect()->route('auditor.laporan-keuangan');
        }
      }

      abort(403, 'Kamu tidak ada akses ke halaman tersebut!');
    }

    return $next($request);
  }
}
