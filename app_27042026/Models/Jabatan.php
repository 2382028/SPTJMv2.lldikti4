<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jabatan extends Model
{
  use HasFactory;
  protected $table = 'e_jabatan';
  protected $primaryKey = 'kode';
  public $incrementing = false;
  protected $keyType = 'string';
  protected $fillable = ['kode', 'jabatan','nominal'];
}