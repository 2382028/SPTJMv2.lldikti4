<?php

namespace App\Helpers;

class ActiveYears
{
    private static function filePath(): string
    {
        return storage_path('app/active_years.json');
    }

    public static function load(): array
    {
        $path = self::filePath();
        if (!file_exists($path)) {
            return [];
        }
        $json = file_get_contents($path);
        $data = json_decode($json, true);
        if (!is_array($data)) {
            return [];
        }
        // Normalize to unique integer years
        $years = array_values(array_unique(array_map(fn($y) => (int)$y, $data)));
        sort($years);
        return $years;
    }

    public static function save(array $years): void
    {
        $years = array_values(array_unique(array_map(fn($y) => (int)$y, $years)));
        sort($years);
        $path = self::filePath();
        @mkdir(dirname($path), 0777, true);
        file_put_contents($path, json_encode($years, JSON_PRETTY_PRINT));
    }

    public static function toggle(int $year): array
    {
        $years = self::load();
        $idx = array_search($year, $years, true);
        if ($idx !== false) {
            array_splice($years, $idx, 1);
        } else {
            $years[] = $year;
        }
        self::save($years);
        return $years;
    }
}
