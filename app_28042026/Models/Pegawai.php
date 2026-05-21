<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
  use HasFactory;
  protected $table = 'g_pegawai';
  protected $primaryKey = 'kode';
  public $incrementing = false;
  protected $keyType = 'string';
  protected $fillable = ['kode', 'jenis'];
}
