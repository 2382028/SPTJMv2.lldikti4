<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class LoginBackgroundService
{
  public const DEFAULT_FILENAME = 'background_login.png';

  public const HEADER_MODE_DEFAULT = 'default';
  public const HEADER_MODE_HIDE = 'hide';
  public const HEADER_MODE_CORNER = 'corner';

  private const SETTINGS_DIR = 'app/settings';
  private const SETTINGS_FILE = 'login_background.json';

  /**
   * Returns the filename to use for login background.
   * Falls back to DEFAULT_FILENAME when none is selected.
   */
  public static function getSelectedFilename(): string
  {
    $settings = self::readSettings();
    $selected = $settings['selected'] ?? null;

    if (is_string($selected) && $selected !== '') {
      $path = public_path('background/' . $selected);
      if (is_file($path)) {
        return $selected;
      }
    }

    // Default when nothing selected / invalid selection
    $defaultPath = public_path('background/' . self::DEFAULT_FILENAME);
    if (is_file($defaultPath)) {
      return self::DEFAULT_FILENAME;
    }

    // Safety fallback: pick first existing image if default is missing
    $files = glob(public_path('background/*.{png,jpg,jpeg,webp,gif}'), GLOB_BRACE) ?: [];
    if (!empty($files)) {
      return basename($files[0]);
    }

    return self::DEFAULT_FILENAME;
  }

  /**
   * Set selected filename. Use null/empty to revert to default.
   */
  public static function setSelectedFilename(?string $filename): void
  {
    $filename = is_string($filename) ? trim($filename) : null;

    $settings = self::readSettings();

    if ($filename === null || $filename === '') {
      $settings['selected'] = null;
      self::writeSettings($settings);
      return;
    }

    $settings['selected'] = $filename;
    self::writeSettings($settings);
  }

  public static function getHeaderMode(): string
  {
    $settings = self::readSettings();
    $mode = $settings['header_mode'] ?? self::HEADER_MODE_DEFAULT;

    if (!is_string($mode)) {
      return self::HEADER_MODE_DEFAULT;
    }

    $mode = trim($mode);
    if (!in_array($mode, [self::HEADER_MODE_DEFAULT, self::HEADER_MODE_HIDE, self::HEADER_MODE_CORNER], true)) {
      return self::HEADER_MODE_DEFAULT;
    }

    return $mode;
  }

  public static function setHeaderMode(?string $mode): void
  {
    $mode = is_string($mode) ? trim($mode) : self::HEADER_MODE_DEFAULT;
    if (!in_array($mode, [self::HEADER_MODE_DEFAULT, self::HEADER_MODE_HIDE, self::HEADER_MODE_CORNER], true)) {
      $mode = self::HEADER_MODE_DEFAULT;
    }

    $settings = self::readSettings();
    $settings['header_mode'] = $mode;
    self::writeSettings($settings);
  }

  public static function getAssetUrl(): string
  {
    return asset('background/' . self::getSelectedFilename());
  }

  private static function readSettings(): array
  {
    $settingsPath = storage_path(self::SETTINGS_DIR . '/' . self::SETTINGS_FILE);

    if (!is_file($settingsPath)) {
      return [];
    }

    $raw = @file_get_contents($settingsPath);
    if (!is_string($raw) || trim($raw) === '') {
      return [];
    }

    $json = json_decode($raw, true);
    if (!is_array($json)) {
      return [];
    }

    return $json;
  }

  private static function writeSettings(array $settings): void
  {
    $dir = storage_path(self::SETTINGS_DIR);
    File::ensureDirectoryExists($dir);

    $settingsPath = $dir . '/' . self::SETTINGS_FILE;
    file_put_contents($settingsPath, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
  }
}
