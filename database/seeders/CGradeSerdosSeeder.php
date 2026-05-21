<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CGradeSerdosSeeder extends Seeder
{
    public function run()
    {
        $rows = [
            // Asisten Ahli
            ['jabatan'=>'Asisten Ahli','masa_kerja_bawah'=>0,'masa_kerja_atas'=>99,'golongan'=>'III/b'],

            // Lektor
            ['jabatan'=>'Lektor','masa_kerja_bawah'=>0,'masa_kerja_atas'=>0,'golongan'=>'III/b'],
            ['jabatan'=>'Lektor','masa_kerja_bawah'=>1,'masa_kerja_atas'=>3,'golongan'=>'III/c'],
            ['jabatan'=>'Lektor','masa_kerja_bawah'=>4,'masa_kerja_atas'=>99,'golongan'=>'III/d'],

            // Lektor Kepala
            ['jabatan'=>'Lektor Kepala','masa_kerja_bawah'=>0,'masa_kerja_atas'=>0,'golongan'=>'III/d'],
            ['jabatan'=>'Lektor Kepala','masa_kerja_bawah'=>1,'masa_kerja_atas'=>99,'golongan'=>'IV/a'],

            // Guru Besar
            ['jabatan'=>'Guru Besar','masa_kerja_bawah'=>0,'masa_kerja_atas'=>0,'golongan'=>'IV/a'],
            ['jabatan'=>'Guru Besar','masa_kerja_bawah'=>1,'masa_kerja_atas'=>2,'golongan'=>'IV/b'],
            ['jabatan'=>'Guru Besar','masa_kerja_bawah'=>3,'masa_kerja_atas'=>5,'golongan'=>'IV/c'],
            ['jabatan'=>'Guru Besar','masa_kerja_bawah'=>6,'masa_kerja_atas'=>7,'golongan'=>'IV/d'],
            ['jabatan'=>'Guru Besar','masa_kerja_bawah'=>8,'masa_kerja_atas'=>99,'golongan'=>'IV/e'],
        ];

        foreach ($rows as $row) {
            DB::table('c_grade_serdos')->updateOrInsert(
                [
                    'jabatan'=>$row['jabatan'],
                    'masa_kerja_bawah'=>$row['masa_kerja_bawah'],
                    'masa_kerja_atas'=>$row['masa_kerja_atas'],
                ],
                [
                    'golongan'=>$row['golongan'],
                    'created_at'=>now(),
                    'updated_at'=>now(),
                ]
            );
        }
    }
}
