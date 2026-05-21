<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
  use HasFactory;
  protected $table = "s_transaksi_2";
  protected $guarded = ['no'];
  public $timestamps = false;
}
