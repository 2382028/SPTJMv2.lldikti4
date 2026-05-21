<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PTSMiddleware
{
  public function handle(Request $request, Closure $next)
  {
    if (
      !$request->session()->has('pts_login') ||
      !$request->session()->has('kode_pts') ||
      !$request->session()->has('tahun')
    ) {
      return redirect('/login')->with('error', 'Silakan login terlebih dahulu sebagai PTS.');
    }

    return $next($request);
  }
}
