<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class ADosen extends Authenticatable
{
  protected $table = 'a_dosen';

  protected $fillable = [
    'nidn',
    'nuptk',
    'kode_pts',
    'nama_pts',
    'nama_dosen',
    'alamat_pt',
    'password',
    'aktif',
    'wilayah',
    'dokumen',
    'tanggal_update',
  ];

  public $timestamps = false;

  protected $hidden = [
    'password',
  ];
}
