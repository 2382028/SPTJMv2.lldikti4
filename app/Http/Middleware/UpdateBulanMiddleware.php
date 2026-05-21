<?php

namespace App\Http\Middleware;

use Closure;
use Carbon\Carbon;
use Illuminate\Http\Request;

class UpdateBulanMiddleware
{
  /**
   * Update session 'bulan' to current month (1-12) on each web request.
   */
  public function handle(Request $request, Closure $next)
  {
    $current = (int) Carbon::now()->month;

    // Hanya tulis session jika belum ada atau nilainya berubah
    if (!$request->session()->has('bulan') || (int) $request->session()->get('bulan') !== $current) {
      $request->session()->put('bulan', $current);
    }

    return $next($request);
  }
}
