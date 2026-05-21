<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Perubahan extends Model
{
  use HasFactory;
  protected $table = 'h_perubahan';
  protected $primaryKey = 'kode';
  public $incrementing = false;
  protected $keyType = 'string';
  protected $fillable = ['kode', 'status_perubahan'];
}
