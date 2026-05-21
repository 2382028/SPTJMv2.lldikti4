<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dosen extends Model
{
  use HasFactory;
  // Canonical source for dosen data is s_transaksi_2
  protected $table = 's_transaksi_2';

  // Match actual primary key in s_transaksi_2 migration
  protected $primaryKey = 'No';
  public $incrementing = true;
  protected $keyType = 'int';

  // Keep it permissive since this model is primarily used for reads.
  protected $guarded = ['No'];
}
