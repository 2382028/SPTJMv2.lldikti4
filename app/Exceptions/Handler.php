<?php

namespace App\Exceptions;

use App\Helpers\ErrorAlias;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Handler extends ExceptionHandler
{
  /**
   * The list of the inputs that are never flashed to the session on validation exceptions.
   *
   * @var array<int, string>
   */
  protected $dontFlash = ['current_password', 'password', 'password_confirmation'];

  /**
   * Register the exception handling callbacks for the application.
   */
  public function register(): void
  {
    $this->reportable(function (Throwable $e) {
      //
    });
  }

  private function guessScope($request): string
  {
    try {
      if ($request && method_exists($request, 'expectsJson') && $request->expectsJson()) {
        return 'API-UNHANDLED';
      }

      $path = $request && method_exists($request, 'path') ? strtolower((string) $request->path()) : '';
      if ($path !== '') {
        if (strpos($path, 'admin') === 0) return 'ADM-UNHANDLED';
        if (strpos($path, 'pic') === 0) return 'PIC-UNHANDLED';
        if (strpos($path, 'pts') === 0) return 'PTS-UNHANDLED';
        if (strpos($path, 'dosen') === 0) return 'DOSEN-UNHANDLED';
      }

      if (Auth::guard('pts')->check()) return 'PTS-UNHANDLED';
      if (Auth::guard('dosen')->check()) return 'DOSEN-UNHANDLED';
      if (Auth::guard('web')->check()) {
        $role = Auth::user()->role ?? null;
        if ($role === 'admin') return 'ADM-UNHANDLED';
        if ($role === 'pic') return 'PIC-UNHANDLED';
      }
    } catch (Throwable $ignored) {
      // ignore
    }

    return 'APP-UNHANDLED';
  }

  public function render($request, Throwable $e)
  {
    // Keep default behavior for validation and known HTTP errors
    if ($e instanceof ValidationException) {
      return parent::render($request, $e);
    }

    if ($e instanceof HttpExceptionInterface) {
      $status = (int) $e->getStatusCode();
      if ($status > 0 && $status < 500) {
        return parent::render($request, $e);
      }
    }

    $scope = $this->guessScope($request);
    $alias = ErrorAlias::fromThrowable($e, $scope);

    Log::error('Unhandled exception', [
      'alias' => $alias['code'],
      'scope' => $scope,
      'url' => method_exists($request, 'fullUrl') ? $request->fullUrl() : null,
      'method' => method_exists($request, 'method') ? $request->method() : null,
      'ip' => method_exists($request, 'ip') ? $request->ip() : null,
      'user_id' => Auth::id(),
      'error' => $e->getMessage(),
      'trace' => $e->getTraceAsString(),
    ]);

    if ($request && method_exists($request, 'expectsJson') && $request->expectsJson()) {
      return response()->json([
        'success' => false,
        'message' => $alias['message'],
        'code' => $alias['code'],
      ], 500);
    }

    // For form submits: return back with a friendly error message
    try {
      $method = method_exists($request, 'method') ? strtoupper((string) $request->method()) : '';
      if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
        return redirect()->back()->with('error', $alias['message']);
      }
    } catch (Throwable $ignored) {
      // ignore
    }

    return response()->view('errors.500', ['message' => $alias['message'], 'code_alias' => $alias['code']], 500);
  }

  protected function unauthenticated($request, AuthenticationException $exception)
  {
    if ($request->expectsJson()) {
      return response()->json(['message' => $exception->getMessage()], 401);
    }

    $guard = $exception->guards()[0] ?? null;

    switch ($guard) {
      case 'pts':
        $login = route('login'); // atau ganti ke route khusus pts login jika ada
        break;
      default:
        $login = route('login');
        break;
    }

    return redirect()->guest($login);
  }
}
