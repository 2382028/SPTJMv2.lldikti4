<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class KeluhanPembayaranPicController extends Controller
{
  public function index()
  {
    return view('pic.keluhan-pembayaran');
  }
}
