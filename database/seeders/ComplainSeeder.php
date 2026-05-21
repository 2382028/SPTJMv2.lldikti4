<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ComplainSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('users')->where('role', 'admin')->orderBy('id')->value('id');

        $pts = DB::table('a_pts')->orderBy('id')->first();
        DB::table('i_complain')->insert([
            'pelapor_tipe' => 'pts',
            'pts_id' => $pts->id ?? null,
            'dosen_id' => null,
            'kode_pts' => $pts->kode_pts ?? 'PTS-DEMO',
            'nidn' => null,
            'nuptk' => null,
            'judul' => 'Contoh complain PTS',
            'pesan' => 'Ini contoh complain dari akun PTS (seed).',
            'lampiran' => null,
            'status' => 'open',
            'admin_balasan' => null,
            'handled_by' => $adminId,
            'handled_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $dosen = DB::table('a_dosen')->orderBy('id')->first();
        DB::table('i_complain')->insert([
            'pelapor_tipe' => 'dosen',
            'pts_id' => null,
            'dosen_id' => $dosen->id ?? null,
            'kode_pts' => $dosen->kode_pts ?? null,
            'nidn' => $dosen->nidn ?? 'NIDN-DEMO',
            'nuptk' => $dosen->nuptk ?? null,
            'judul' => 'Contoh complain Dosen',
            'pesan' => 'Ini contoh complain dari akun dosen (seed).',
            'lampiran' => null,
            'status' => 'open',
            'admin_balasan' => null,
            'handled_by' => $adminId,
            'handled_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
