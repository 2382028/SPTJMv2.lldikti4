<?php

namespace App\Exports;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;

class MonitoringUsulanDosenExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
  protected $request;
  public function __construct(Request $request)
  {
    $this->request = $request;
  }

  public function collection()
  {
    $tahun = now()->year;
    $awal = (int) $this->request->input('awalPeriode', 1);
    $akhir = (int) $this->request->input('akhirPeriode', now()->month);
    if ($awal > $akhir) {
      [$awal, $akhir] = [$akhir, $awal];
    }
    $bulanIndonesia = [
      1 => 'Januari',
      2 => 'Februari',
      3 => 'Maret',
      4 => 'April',
      5 => 'Mei',
      6 => 'Juni',
      7 => 'Juli',
      8 => 'Agustus',
      9 => 'September',
      10 => 'Oktober',
      11 => 'November',
      12 => 'Desember',
    ];

    //kode lama
    // $jumlahBulanNull = [];
    // $kodeConcatParts = [];

    // for ($i = $awal; $i <= $akhir; $i++) {
    //   $jumlahBulanNull[] = "SUM(IF(t.kode_usulan$i IS NULL, 1, 0))";
    //   $kodeConcatParts[] = "CASE WHEN COUNT(t.kode_usulan$i) = 0 THEN 'kode_usulan$i' ELSE NULL END";
    // }

    // $nullCheckSQL = implode(' + ', $jumlahBulanNull);
    // $kodeConcatSQL = "CONCAT_WS(', ', " . implode(', ', $kodeConcatParts) . ')';

    // $query = DB::table('s_transaksi_2 as d')
    //   ->leftJoin('s_transaksi as t', function ($join) use ($tahun) {
    //     $join->on('d.nidn', '=', 't.nidn')->where('t.tahun', '=', $tahun);
    //   })
    //   ->select(
    //     'd.nidn',
    //     'd.nama',
    //     'd.jenis',
    //     'd.kode_pt',
    //     'd.pts',
    //     DB::raw("($nullCheckSQL) as bulan_belum_usulan"),
    //     DB::raw("($kodeConcatSQL) as kode_belum_usulan")
    //   )
    //   ->where('d.aktif', 1)
    //   ->groupBy('d.nidn', 'd.nama', 'd.jenis', 'd.kode_pt', 'd.pts');

    //kode baru
    $concatKodeBelumUsulan = [];
    $concatBulanBelumUsulan = [];
    for ($i = $awal; $i <= $akhir; $i++) {
      $namaBulan = $bulanIndonesia[$i];
      $concatKodeBelumUsulan[] = "MAX(CASE WHEN KodeUsulan$i IS NULL THEN '$namaBulan' END)";
      $concatBulanBelumUsulan[] = "SUM(IF(KodeUsulan$i IS NULL, 1, 0))";
    }
    $kodeBelumUsulan = implode(',', $concatKodeBelumUsulan);
    $bulanBelumUsulan = implode("+", $concatBulanBelumUsulan);
    $query = DB::Table('s_transaksi_2')
      ->join('a_pts', 's_transaksi_2.Kode_PT', '=', 'a_pts.kode_pts')
      ->select(
        'NIDN',
        'NUPTK',
        'Nama',
        'Jenis',
        'Kode_PT',
        'PTS',
        DB::raw("($bulanBelumUsulan)" . " as bulan_belum_usulan"),
        DB::raw("CONCAT_WS(', ',$kodeBelumUsulan)" . " as kode_belum_usulan")
      )
      ->where('s_transaksi_2.Aktif', '1')
      ->where('a_pts.aktif', '1')
      ->where('s_transaksi_2.tahun_versi', session('tahun'))
      ->groupBy("NIDN", "NUPTK", "Nama", "Jenis", "Kode_PT", "PTS")
      ->havingRaw("bulan_belum_usulan > 0");

    // validasi: jika login sebagai PTS, batasi ke kode PT yang bersangkutan
    if (Auth::guard('pts')->check()) {
      $ptsUser = Auth::guard('pts')->user();
      if (!empty($ptsUser->kode_pts)) {
        $query->where('s_transaksi_2.Kode_PT', $ptsUser->kode_pts);
      }
    } else {
      // jika login sebagai web user (admin/pic), batasi berdasarkan role
      if (Auth::check()) {
        $webUser = Auth::user();
        if (isset($webUser->role) && $webUser->role !== 'admin') {
          // untuk PIC/non-admin, batasi pemegang_wilayah
          if (!empty($webUser->email)) {
            $query->where('s_transaksi_2.pemegang_wilayah', $webUser->email);
          }
        }
      }
    }

    if ($this->request->filled('search')) {
      $search = trim($this->request->input('search'));
      $query->where(function ($q) use ($search) {
        $q->where('NIDN', 'like', '%' . $search . '%')
          ->orWhere('NUPTK', 'like', '%' . $search . '%')
          ->orWhere('Nama', 'like', '%' . $search . '%')
          ->orWhere('PTS', 'like', '%' . $search . '%')
          ->orWhere('Kode_PT', 'like', '%' . $search . '%');
      });
    }
    return $query->orderBy('bulan_belum_usulan', 'DESC')->get();
  }

  public function headings(): array
  {
    return ['NIDN', 'NUPTK', 'Nama', 'Jenis', 'Kode PT', 'PTS', 'Bulan Belum Usulan', 'Kode Belum Usulan'];
  }

  public function map($row): array
  {
    return [
      $row->NIDN,
      $row->NUPTK ?: '-',
      $row->Nama,
      $row->Jenis,
      $row->Kode_PT,
      $row->PTS,
      $row->bulan_belum_usulan,
      $row->kode_belum_usulan,
    ];
  }

  public function styles(Worksheet $sheet)
  {
    // Jumlah baris = hasil query + 1 baris header
    $rowCount = $this->collection()->count() + 1;

    // Terapkan border ke seluruh sel
    $sheet->getStyle('A1:H' . $rowCount)->applyFromArray([
      'borders' => [
        'allBorders' => [
          'borderStyle' => Border::BORDER_THIN,
          'color' => ['argb' => '000000'],
        ],
      ],
    ]);

    // Tebalkan header
    $sheet
      ->getStyle('A1:H1')
      ->getFont()
      ->setBold(true);

    return [];
  }
}