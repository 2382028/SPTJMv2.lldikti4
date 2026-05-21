<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Keaktifan extends Model
{
  use HasFactory;
  protected $table = 'f_keaktifan';
  protected $primaryKey = 'kode';
  public $incrementing = false;
  protected $keyType = 'string';
  protected $fillable = ['kode', 'aktif'];
}
