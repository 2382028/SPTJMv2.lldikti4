<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Versi extends Model
{
    use HasFactory;

    protected $table = 'versis';

    protected $fillable = [
        'versi',
        'status',
    ];
}
