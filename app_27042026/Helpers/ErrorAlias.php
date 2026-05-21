<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class ErrorAlias
{
    /**
     * Generate an alias code + friendly message for end users.
     *
     * @return array{code: string, message: string}
     */
    public static function fromThrowable(\Throwable $e, string $scope = 'ERR'): array
    {
        $scope = strtoupper(trim($scope)) ?: 'ERR';
        $uuid = str_replace('-', '', (string) Str::uuid());
        $code = $scope . '-' . strtoupper(substr($uuid, 0, 10));

        return [
            'code' => $code,
            'message' => 'Terjadi kesalahan sistem. Silakan coba lagi. (Kode: ' . $code . ')',
        ];
    }
}
