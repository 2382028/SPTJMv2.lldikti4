<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengaturanUsulan extends Model
{
  use HasFactory;

  protected $table = 'm_pengaturan_usulan';

  protected $fillable = ['jenis_usulan','tahun', 'bulan', 'pencairan_ke', 'tanggal_mulai', 'tanggal_selesai', 'status'];
}
