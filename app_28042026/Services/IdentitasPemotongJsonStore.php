<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class IdentitasPemotongJsonStore
{
    private const JSON_PATH = 'identitas_pemotong.json';
    private const SIGNATURE_DIR = 'identitas_pemotong_signatures';
    private const CAP_DIR = 'identitas_pemotong_caps';

    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        $data = $this->read();

        // Normalize to list
        $list = array_values($data);

        usort($list, function ($a, $b) {
            $ta = $a['tanggal'] ?? '';
            $tb = $b['tanggal'] ?? '';
            return strcmp($tb, $ta);
        });

        return $list;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(string $id): ?array
    {
        $data = $this->read();
        return $data[$id] ?? null;
    }

    /**
     * @param array{npwp:string,nama:string,tanggal?:string|null,tanda_tangan_path?:string|null,cap_path?:string|null} $payload
     * @return array<string, mixed>
     */
    public function create(array $payload): array
    {
        $data = $this->read();

        $id = (string) Str::uuid();
        $now = now()->toIso8601String();

        $record = [
            'id' => $id,
            'npwp' => $payload['npwp'],
            'nama' => $payload['nama'],
            'tanggal' => $payload['tanggal'] ?? null,
            'tanda_tangan_path' => $payload['tanda_tangan_path'] ?? null,
            'cap_path' => $payload['cap_path'] ?? null,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        $data[$id] = $record;
        $this->write($data);

        return $record;
    }

    /**
     * @param array{npwp?:string,nama?:string,tanggal?:string,tanda_tangan_path?:string|null,cap_path?:string|null} $payload
     * @return array<string, mixed>
     */
    public function update(string $id, array $payload): array
    {
        $data = $this->read();
        if (!isset($data[$id])) {
            throw new \RuntimeException('Identitas pemotong tidak ditemukan.');
        }

        $record = $data[$id];
        foreach (['npwp', 'nama', 'tanggal', 'tanda_tangan_path', 'cap_path'] as $field) {
            if (array_key_exists($field, $payload)) {
                $record[$field] = $payload[$field];
            }
        }
        $record['updated_at'] = now()->toIso8601String();

        $data[$id] = $record;
        $this->write($data);

        return $record;
    }

    public function delete(string $id): void
    {
        $data = $this->read();
        if (!isset($data[$id])) {
            return;
        }

        unset($data[$id]);
        $this->write($data);
    }

    /**
     * Stores PNG signature in public disk and returns relative path under public disk.
     */
    public function storeSignaturePng(\Illuminate\Http\UploadedFile $file): string
    {
        $filename = Str::uuid()->toString() . '.png';
        $path = $file->storeAs(self::SIGNATURE_DIR, $filename, 'public');
        return $path;
    }

    public function deleteSignatureIfExists(?string $publicDiskPath): void
    {
        if (!$publicDiskPath) {
            return;
        }
        Storage::disk('public')->delete($publicDiskPath);
    }

    /**
     * Stores PNG cap in public disk and returns relative path under public disk.
     */
    public function storeCapPng(\Illuminate\Http\UploadedFile $file): string
    {
        $filename = Str::uuid()->toString() . '.png';
        $path = $file->storeAs(self::CAP_DIR, $filename, 'public');
        return $path;
    }

    public function deleteCapIfExists(?string $publicDiskPath): void
    {
        if (!$publicDiskPath) {
            return;
        }
        Storage::disk('public')->delete($publicDiskPath);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function read(): array
    {
        if (!Storage::disk('local')->exists(self::JSON_PATH)) {
            return [];
        }

        $raw = Storage::disk('local')->get(self::JSON_PATH);
        if (!is_string($raw) || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return [];
        }

        // Ensure keys are ids
        $normalized = [];
        foreach ($decoded as $key => $value) {
            if (is_array($value)) {
                $id = (string)($value['id'] ?? $key);
                $value['id'] = $id;
                $normalized[$id] = $value;
            }
        }

        return $normalized;
    }

    /**
     * @param array<string, array<string, mixed>> $data
     */
    private function write(array $data): void
    {
        Storage::disk('local')->put(
            self::JSON_PATH,
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }
}
