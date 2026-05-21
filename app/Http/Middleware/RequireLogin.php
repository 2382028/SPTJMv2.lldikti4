<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RequireLogin
{
  public function handle(Request $request, Closure $next)
  {
    if (
      $request->is('/') ||
      $request->is('login') ||
      $request->is('logout') ||
      $request->is('auth/reset-password') ||
      $request->is('password/*') ||
      $request->is('storage/*') ||
      $request->is('public/*') ||
      $request->is('assets/*') ||
      $request->is('vendor/*') ||
      $request->is('css/*') ||
      $request->is('js/*') ||
      $request->is('images/*')
    ) {
      return $next($request);
    }

    if (Auth::check() || Auth::guard('pts')->check()) {
      return $next($request);
    }

    // Jika belum login, redirect ke halaman login
    return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
  }
}
