<?php
namespace App\Helpers;

class FormatHelper{
  public function formatRibuan($angka){
    return number_format($angka, 0, ',', '.');
  }
}