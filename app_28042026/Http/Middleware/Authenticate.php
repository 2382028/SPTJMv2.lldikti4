<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Closure;

class Authenticate extends Middleware
{
  protected function redirectTo($request)
  {
    if (!$request->expectsJson()) {
      return abort(403, "Anda tidak punya akases ke halaman tersebut!");
      // return route('login');
    }
  }

  public function handle($request, Closure $next, ...$guards)
  {
    if (empty($guards)) {
      $guards = ['web'];
    }

    $this->authenticate($request, $guards);

    return $next($request);
  }
}
