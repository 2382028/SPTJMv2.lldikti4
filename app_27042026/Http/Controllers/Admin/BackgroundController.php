<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ErrorAlias;
use App\Http\Controllers\Controller;
use App\Services\LoginBackgroundService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class BackgroundController extends Controller
{
  private function nextBackgroundLoginFilename(string $extension): string
  {
    $extension = strtolower(trim($extension));
    if ($extension === '') {
      $extension = 'png';
    }

    $dir = public_path('background');
    File::ensureDirectoryExists($dir);

    $max = 0;
    $allowedExt = ['png', 'jpg', 'jpeg', 'webp', 'gif'];

    foreach (File::files($dir) as $file) {
      $ext = strtolower($file->getExtension());
      if (!in_array($ext, $allowedExt, true)) {
        continue;
      }

      $name = $file->getFilename();
      if (preg_match('/^background_login_?(\d+)\.[a-z0-9]+$/i', $name, $m)) {
        $n = (int) $m[1];
        if ($n > $max) {
          $max = $n;
        }
      }
    }

    $next = $max + 1;
    return 'background_login_' . $next . '.' . $extension;
  }

  private function resolveBackgroundPath(string $filename): ?string
  {
    $filename = trim($filename);

    if ($filename === '' || Str::contains($filename, ['..', '/', '\\'])) {
      return null;
    }

    $dir = public_path('background');
    $dirReal = realpath($dir);
    if ($dirReal === false) {
      return null;
    }

    $candidate = $dir . DIRECTORY_SEPARATOR . $filename;
    $candidateReal = realpath($candidate);
    if ($candidateReal === false) {
      return null;
    }

    // Ensure the resolved file stays inside public/background
    $dirPrefix = rtrim($dirReal, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    if (!str_starts_with($candidateReal, $dirPrefix)) {
      return null;
    }

    return is_file($candidateReal) ? $candidateReal : null;
  }

  public function index()
  {
    $dir = public_path('background');
    File::ensureDirectoryExists($dir);

    $allowedExt = ['png', 'jpg', 'jpeg', 'webp', 'gif'];

    $backgrounds = collect(File::files($dir))
      ->filter(function ($file) use ($allowedExt) {
        return in_array(strtolower($file->getExtension()), $allowedExt, true);
      })
      ->map(function ($file) {
        return $file->getFilename();
      })
      ->filter(function ($filename) {
        return $filename !== LoginBackgroundService::DEFAULT_FILENAME;
      })
      ->sort()
      ->values()
      ->all();

    $selected = LoginBackgroundService::getSelectedFilename();

    return view('admin.background', [
      'backgrounds' => $backgrounds,
      'selected' => $selected,
      'defaultFilename' => LoginBackgroundService::DEFAULT_FILENAME,
      'headerMode' => LoginBackgroundService::getHeaderMode(),
    ]);
  }

  public function select(Request $request)
  {
    try {
      $validated = $request->validate([
        'selected' => 'nullable|string',
      ]);

      $selected = isset($validated['selected']) ? trim((string) $validated['selected']) : '';

      if ($selected === '' || $selected === 'default') {
        LoginBackgroundService::setSelectedFilename(null);
        return redirect()->route('admin.background.index')->with('success', 'Background login dikembalikan ke default.');
      }

      $path = $this->resolveBackgroundPath($selected);
      if ($path === null) {
        return redirect()->route('admin.background.index')->with('error', 'File background tidak ditemukan.');
      }

      LoginBackgroundService::setSelectedFilename($selected);

      return redirect()->route('admin.background.index')->with('success', 'Background login berhasil diubah.');
    } catch (\Throwable $e) {
      $alias = ErrorAlias::fromThrowable($e, 'BG');
      report($e);
      \Log::error('[' . ($alias['code'] ?? 'BG-ERR') . '] ' . $e->getMessage(), ['exception' => $e]);
      try {
        $logFile = storage_path('logs/background_errors.log');
        $entry = '[' . now()->toDateTimeString() . '] [' . ($alias['code'] ?? 'BG-ERR') . '] ' . $e->getMessage() . PHP_EOL . (string) $e . PHP_EOL . PHP_EOL;
        @file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
      } catch (\Throwable $inner) {
        \Log::warning('Failed to write background_errors.log: ' . $inner->getMessage());
      }
      return redirect()->route('admin.background.index')->with('error', $alias['message']);
    }
  }

  public function upload(Request $request)
  {
    try {
      $validated = $request->validate([
        'image' => 'required|image|mimes:png,jpg,jpeg,webp,gif|max:5120',
      ], [
        'image.max' => 'Ukuran file maksimal 5MB.',
      ]);

      $file = $validated['image'];

      $dir = public_path('background');
      File::ensureDirectoryExists($dir);

      $ext = strtolower($file->getClientOriginalExtension());
      $filename = $this->nextBackgroundLoginFilename($ext);

      $file->move($dir, $filename);

      return redirect()->route('admin.background.index')->with('success', 'Gambar berhasil diupload: ' . $filename);
    } catch (\Throwable $e) {
      $alias = ErrorAlias::fromThrowable($e, 'BG');
      report($e);
      \Log::error('[' . ($alias['code'] ?? 'BG-ERR') . '] ' . $e->getMessage(), ['exception' => $e]);
      try {
        $logFile = storage_path('logs/background_errors.log');
        $entry = '[' . now()->toDateTimeString() . '] [' . ($alias['code'] ?? 'BG-ERR') . '] ' . $e->getMessage() . PHP_EOL . (string) $e . PHP_EOL . PHP_EOL;
        @file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
      } catch (\Throwable $inner) {
        \Log::warning('Failed to write background_errors.log: ' . $inner->getMessage());
      }
      return redirect()->route('admin.background.index')->with('error', $alias['message']);
    }
  }

  public function destroy(string $filename)
  {
    try {
      $filename = trim($filename);

      if ($filename === LoginBackgroundService::DEFAULT_FILENAME) {
        return redirect()->route('admin.background.index')->with('error', 'Background default tidak bisa dihapus.');
      }

      $path = $this->resolveBackgroundPath($filename);
      if ($path === null) {
        return redirect()->route('admin.background.index')->with('error', 'File background tidak ditemukan.');
      }

      // If the deleted file is currently selected, revert to default
      if (LoginBackgroundService::getSelectedFilename() === $filename) {
        LoginBackgroundService::setSelectedFilename(null);
      }

      @unlink($path);

      return redirect()->route('admin.background.index')->with('success', 'Background berhasil dihapus.');
    } catch (\Throwable $e) {
      $alias = ErrorAlias::fromThrowable($e, 'BG');
      report($e);
      \Log::error('[' . ($alias['code'] ?? 'BG-ERR') . '] ' . $e->getMessage(), ['exception' => $e]);
      try {
        $logFile = storage_path('logs/background_errors.log');
        $entry = '[' . now()->toDateTimeString() . '] [' . ($alias['code'] ?? 'BG-ERR') . '] ' . $e->getMessage() . PHP_EOL . (string) $e . PHP_EOL . PHP_EOL;
        @file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
      } catch (\Throwable $inner) {
        \Log::warning('Failed to write background_errors.log: ' . $inner->getMessage());
      }
      return redirect()->route('admin.background.index')->with('error', $alias['message']);
    }
  }

  public function updateHeaderMode(Request $request)
  {
    try {
      $validated = $request->validate([
        'header_mode' => 'required|in:' . implode(',', [
          LoginBackgroundService::HEADER_MODE_DEFAULT,
          LoginBackgroundService::HEADER_MODE_HIDE,
          LoginBackgroundService::HEADER_MODE_CORNER,
        ]),
      ]);

      LoginBackgroundService::setHeaderMode($validated['header_mode']);

      return redirect()->route('admin.background.index')->with('success', 'Pengaturan header login berhasil disimpan.');
    } catch (\Throwable $e) {
      $alias = ErrorAlias::fromThrowable($e, 'BG');
      report($e);
      \Log::error('[' . ($alias['code'] ?? 'BG-ERR') . '] ' . $e->getMessage(), ['exception' => $e]);
      try {
        $logFile = storage_path('logs/background_errors.log');
        $entry = '[' . now()->toDateTimeString() . '] [' . ($alias['code'] ?? 'BG-ERR') . '] ' . $e->getMessage() . PHP_EOL . (string) $e . PHP_EOL . PHP_EOL;
        @file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
      } catch (\Throwable $inner) {
        \Log::warning('Failed to write background_errors.log: ' . $inner->getMessage());
      }
      return redirect()->route('admin.background.index')->with('error', $alias['message']);
    }
  }

  public function updateSettings(Request $request)
  {
    try {
      $validated = $request->validate([
        'header_mode' => 'required|in:' . implode(',', [
          LoginBackgroundService::HEADER_MODE_DEFAULT,
          LoginBackgroundService::HEADER_MODE_HIDE,
          LoginBackgroundService::HEADER_MODE_CORNER,
        ]),
        'selected' => 'required|string',
      ]);

      LoginBackgroundService::setHeaderMode($validated['header_mode']);

      $selected = trim((string) $validated['selected']);
      if ($selected === '' || $selected === 'default') {
        LoginBackgroundService::setSelectedFilename(null);
        return redirect()->route('admin.background.index')->with('success', 'Pengaturan berhasil disimpan.');
      }

      $path = $this->resolveBackgroundPath($selected);
      if ($path === null) {
        return redirect()->route('admin.background.index')->with('error', 'File background tidak ditemukan.');
      }

      LoginBackgroundService::setSelectedFilename($selected);

      return redirect()->route('admin.background.index')->with('success', 'Pengaturan berhasil disimpan.');
    } catch (\Throwable $e) {
      $alias = ErrorAlias::fromThrowable($e, 'BG');
      report($e);
      \Log::error('[' . ($alias['code'] ?? 'BG-ERR') . '] ' . $e->getMessage(), ['exception' => $e]);
      return redirect()->route('admin.background.index')->with('error', $alias['message']);
    }
  }
}
