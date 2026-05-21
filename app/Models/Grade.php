<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
  use HasFactory;
  protected $table = 'c_grade';
  protected $primaryKey = 'kode';
  protected $fillable = ['kode', 'gol', 'masa_kerja', 'nominal'];
}
