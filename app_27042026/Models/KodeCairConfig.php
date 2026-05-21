<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KodeCairConfig extends Model
{
    use HasFactory;

    protected $table = 'm_kode_cair_aktif';

    protected $fillable = ['config'];

    protected $casts = [
        'config' => 'array',
    ];
}
