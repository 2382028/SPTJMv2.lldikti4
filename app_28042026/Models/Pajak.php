<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pajak extends Model
{
  use HasFactory;
  protected $table = 'd_pajak';
  protected $primaryKey = 'no';
  public $incrementing = true;
  protected $fillable = ['status', 'akumulasi', 'tarif_pajak'];
  protected $casts = ['tarif_pajak' => 'float'];
}
