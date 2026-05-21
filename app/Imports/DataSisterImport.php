<?php

namespace App\Imports;

use DB;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class DataSisterImport implements ToCollection, WithHeadingRow, WithChunkReading
{
  /**
   * @param Collection $collection
   */

  private $table;
  public $successCount = 0;
  public $failedCount = 0;
  public $failures = [];

  public function __construct($table)
  {
    $this->table = $table;
  }

  public function collection(Collection $rows)
  {
    $data = [];
    foreach ($rows as $index => $row) {
      //normalisasi header
      $row = collect($row)->mapWithKeys(function ($value, $key) {
        $key = strtolower(str_replace(" ", "_", $key));
        return [$key => $value];
      })->toArray();
      // Masukkan ke array data
      $data[] = [
        'nidn' => $row['nidn'] ?? null,
        'nuptk' => $row['nuptk'] ?? null,
        'no_sertifikat' => $row['no_sertifikat'] ?? null,
        'nama_dosen' => $row['nama_dosen'] ?? null,
        'kode_pt' => $row['kode_pt'] ?? null,
        'pt' => $row['pt'] ?? null,
        'prodi' => $row['prodi'] ?? null,
        'kesimpulan_bkd' => $row['kesimpulan_bkd'] ?? null,
        'kewajiban_khusus' => $row['kewajiban_khusus'] ?? null,
        'kesimpulan' => $row['kesimpulan'] ?? null,
        'kd' =>  $this->parseDecimal($row['kd']),
        'kp' => $this->parseDecimal($row['kp']),
        'potongan_periodik' => $this->parseDecimal($row['potongan_periodik'])
      ];
    }

    // Filter out rows without primary identifier (nidn) to avoid empty inserts
    $data = array_values(array_filter($data, function ($r) {
      return !empty($r['nidn']);
    }));

    //simpan ke db
    try {
      // Use insertOrIgnore to avoid duplicate errors and get affected count
      $affected = DB::table($this->table)->insertOrIgnore($data);
      // insertOrIgnore returns number of rows inserted; if driver returns boolean, fallback to count
      if (is_int($affected)) {
        $this->successCount += $affected;
      } else {
        $this->successCount += count($data);
      }
    } catch (\Exception $e) {
      $this->failures = [
        'row' => 'chunk',
        'errors' => [$e->getMessage()],
        'data' => $data
      ];
    }
  }

  public function chunkSize(): int
  {
    return 500;
  }

  //fungsi parse
  private function parseDecimal($value)
  {
    //jika data kosong maka null
    if ($value == '' || $value == null) {
      return null;
    }

    //jika data bentuk persen (12%) ubah ke decimal
    if (is_string($value) && str_contains($value, '%')) {
      $result = floatval(str_replace('%', '', $value)) / 100;
      return $result;
    }
    //jiak data bentuk angka cast langsung
    return (float) $value;
  }
}