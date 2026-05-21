<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class APts extends Authenticatable
{
  protected $table = 'a_pts';
  protected $primaryKey = 'id';
  public $incrementing = false;
  public $timestamps = false;

  protected $fillable = ['kode_pts', 'nama_pts', 'password', 'wilayah', 'aktif'];

  protected $hidden = ['password'];

  // Gunakan kolom 'kode_pts' sebagai username
  public function getAuthIdentifierName()
  {
    return 'kode_pts';
  }
}
