<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoriDosen extends Model
{
  use HasFactory;

  protected $table = 'j_histori_dosen';
  protected $primaryKey = 'no';
  public $incrementing = true;
  protected $fillable = [
    'nidn',
    'nuptk',
    'nama',
    'pts',
    'kode_pt',
    'pemegang_wilayah',
    'aktif',
    'keterangan',
    'pengguna',
    'tanggal_update_terakhir',
    'tanggal_update_terbaru',
    'no_dokumen_ubah',
    'tgl_dokumen_ubah',
    'alasan_perubahan',
    'dokumen',
  ];
}
