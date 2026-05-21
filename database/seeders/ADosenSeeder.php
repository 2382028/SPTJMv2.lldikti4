<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ADosenSeeder extends Seeder
{
    public function run()
    {
        // Read from s_transaksi_2 in chunks to avoid memory spikes
        DB::table('s_transaksi_2')
            ->select('NIDN', 'NUPTK', 'Kode_PT', 'PTS', 'Nama', 'Aktif')
            ->where('Tahun_versi', 2026)
            ->orderBy('Nama')
            ->chunk(200, function ($rows) {
                foreach ($rows as $r) {
                    $nidn = isset($r->NIDN) ? trim((string) $r->NIDN) : '';
                    $nuptk = isset($r->NUPTK) ? trim((string) $r->NUPTK) : '';

                    // skip rows without any identifier
                    if ($nidn === '' && $nuptk === '') {
                        continue;
                    }

                    $kodePts = isset($r->Kode_PT) ? trim((string) $r->Kode_PT) : null;
                    $namaPts = isset($r->PTS) ? trim((string) $r->PTS) : null;
                    $namaDosen = isset($r->Nama) ? trim((string) $r->Nama) : null;

                    $aktifRaw = $r->Aktif ?? null;
                    $aktif = 0;
                    if ($aktifRaw !== null) {
                        $s = strtoupper(trim((string) $aktifRaw));
                        $aktif = in_array($s, ['1', 'Y', 'T', 'TRUE', 'YES'], true) ? 1 : 0;
                    }

                    // set password to nidn or nuptk (hashed)
                    $plainPw = $nidn !== '' ? $nidn : ($nuptk !== '' ? $nuptk : null);
                    $hashedPw = $plainPw ? Hash::make($plainPw) : null;

                    $payload = [
                        'nidn' => $nidn !== '' ? $nidn : null,
                        'nuptk' => $nuptk !== '' ? $nuptk : null,
                        'kode_pts' => $kodePts,
                        'nama_pts' => $namaPts,
                        'nama_dosen' => $namaDosen,
                        'password' => $plainPw,
                        'aktif' => $aktif,
                        'wilayah' => $kodePts,
                        'dokumen' => null,
                        'tanggal_update' => now(),
                    ];

                    if ($nidn !== '') {
                        DB::table('a_dosen')->updateOrInsert(['nidn' => $nidn], $payload);
                    } else {
                        DB::table('a_dosen')->updateOrInsert(['nuptk' => $nuptk], $payload);
                    }
                }
            });
    }
}
