<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PTS extends Model
{
  use HasFactory;

  protected $table = 'a_pts';
  protected $primaryKey = 'id';
  public $timestamps = false;

  protected $fillable = [
    'kode_pts',
    'nama_pts',
    'nama_pimpinan',
    'jabatan_pimpinan',
    'alamat_pt',
    'password',
    'aktif',
    'wilayah',
    'dokumen',
    'tanggal_update',
  ];

  protected $casts = [
    'tanggal_update' => 'datetime',
  ];
}
