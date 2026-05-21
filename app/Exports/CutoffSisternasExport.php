<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CutoffSisternasExport implements FromCollection, WithHeadings
{
    protected $rows;

    public function __construct($rows)
    {
        $this->rows = $rows;
    }

    public function collection()
    {
        $out = [];
        foreach ($this->rows as $r) {
            $arr = (array) $r;
            // ensure consistent column order
            $out[] = [
                $arr['nidn'] ?? null,
                $arr['nuptk'] ?? null,
                $arr['no_sertifikat'] ?? null,
                $arr['nama_dosen'] ?? null,
                $arr['kode_pt'] ?? null,
                $arr['pt'] ?? null,
                $arr['prodi'] ?? null,
                $arr['kesimpulan_bkd'] ?? null,
                $arr['kewajiban_khusus'] ?? null,
                $arr['kesimpulan'] ?? null,
                $arr['kd'] ?? null,
                $arr['kp'] ?? null,
                $arr['potongan_periodik'] ?? null,
            ];
        }

        return collect($out);
    }

    public function headings(): array
    {
        return [
            'nidn','nuptk','no_sertifikat','nama_dosen','kode_pt','pt','prodi',
            'kesimpulan_bkd','kewajiban_khusus','kesimpulan','kd','kp','potongan_periodik'
        ];
    }
}
