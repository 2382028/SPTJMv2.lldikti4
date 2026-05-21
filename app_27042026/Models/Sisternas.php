<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sisternas extends Model
{
  use HasFactory;

  protected $table = 'k_data_sister';

  protected $fillable = ['id', 'tahun', 'periode', 'bulan', 'dokumen', 'tanggal', 'created_at', 'updated_at'];
}
