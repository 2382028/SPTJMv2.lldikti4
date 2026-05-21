<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class DataGradePtsController extends Controller
{
  public function index(Request $request)
  {
    if ($request->ajax()) {
      $c_grade = Grade::where('kode', '!=', 0)
        ->orderByRaw('CAST(kode AS UNSIGNED) ASC');

      return DataTables::of($c_grade)->toJson();
    }

    return view('pts.data-grade');
  }
}
