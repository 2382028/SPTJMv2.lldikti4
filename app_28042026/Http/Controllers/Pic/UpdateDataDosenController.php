<?php

namespace App\Http\Controllers\Pic;

use App\Http\Controllers\Controller;
use App\Models\HistoriDosen;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class UpdateDataDosenController extends Controller
{
  public function updateDataDosenPic($nidn)
  {
    $year = session('tahun');

    // Pilih kolom berdasarkan bulan session sehingga form menampilkan nilai untuk bulan yang aktif
    $bulanSession = (int) session('bulan') ?: 12;
    $masaCol = 'Tahun' . $bulanSession;
    $golCol = 'Gol' . $bulanSession;
    $jabCol = 'Jabatan' . $bulanSession;
    $gajiCol = 'Gaji' . $bulanSession;

    $data_dosen = Transaksi::where(function ($q) use ($nidn) {
        $q->where('NIDN', $nidn)
          ->orWhere('NUPTK', $nidn);
      })
      ->where('Tahun_Versi', $year)
      ->select(
        '*',
        \DB::raw("NULLIF({$masaCol}, '') AS masa_kerja"),
        \DB::raw("NULLIF({$golCol}, '') AS gol"),
        \DB::raw("NULLIF({$jabCol}, '') AS jabatan"),
        \DB::raw("COALESCE({$gajiCol}, 0) AS gaji")
      )
      ->first();

    if (!$data_dosen) {
      return redirect()
        ->back()
        ->with('error', 'Data dosen tidak ditemukan!');
    }

    $histori_dosen = HistoriDosen::where(function ($q) use ($nidn) {
      $q->where('nidn', $nidn)
        ->orWhere('nuptk', $nidn);
    })
      ->orderBy('no', 'desc')->first();

    $pics = User::where('role', 'pic')->pluck('email');
    return view('pic.update-data-dosen', compact('data_dosen', 'histori_dosen', 'pics'));
  }

  public function updateDataPic(Request $request, $nidn)
  {
    $request->validate([
      'nuptk' => 'nullable|string',
      'no_dokumen_ubah' => 'required|string',
      'tgl_dokumen_ubah' => 'required|date',
      'alasan_perubahan' => 'required|string',
      'dokumen' => 'required|file|mimes:pdf,doc,docx',
      'tanggal_update_terakhir' => 'required|date',
      'keterangan' => 'required|string',
      'Aktif' => 'required|in:0,1',
      // month-specific fields
      'jabatan' => 'nullable|string',
      'gol' => 'nullable|string',
      'tahun' => 'nullable|numeric',
      'gaji' => 'nullable',
      'nama' => 'required|string',
      'kode_pt' => 'required|string',
      'pts' => 'required|string',
      'no_rekening' => 'required|string',
      'bank' => 'required|string',
      'nama_rekening' => 'required|string',
      'nama_penerima' => 'required|string',
      'npwp' => 'required|string',
      'pemegang_wilayah' => 'required|string',
      'eligible_span' => 'required|string',
    ]);

    $year = session('tahun');

    $data_dosen = Transaksi::where(function ($q) use ($nidn) {
        $q->where('NIDN', $nidn)
          ->orWhere('NUPTK', $nidn);
      })
      ->where('Tahun_Versi', $year)->first();

    if (!$data_dosen) {
      return redirect()
        ->back()
        ->with('error', 'Data dosen tidak ditemukan!');
    }

    $dataUpdate = [
      'NUPTK' => $request->nuptk,
      'Nama' => $request->nama,
      'Kode_PT' => $request->kode_pt,
      'PTS' => $request->pts,
      'No_Rekening' => $request->no_rekening,
      'Bank' => $request->bank,
      'Nama_Rekening' => $request->nama_rekening,
      'Nama_Penerima' => $request->nama_penerima,
      'NPWP' => $request->npwp,
      'Pemegang_Wilayah' => $request->pemegang_wilayah,
      'Tanggal_Update_Terakhir' => now(),
      'Keterangan' => $request->keterangan,
      'Eligible_span' => $request->eligible_span,
    ];

    $identifier = !empty($data_dosen->NIDN)
      ? $data_dosen->NIDN
      : $data_dosen->NUPTK;

    Transaksi::where(function ($q) use ($identifier) {
        $q->where('NIDN', $identifier)
          ->orWhere('NUPTK', $identifier);
      })
      ->where('Tahun_Versi', $year)
      ->update($dataUpdate);

    // update bulan spesifik (session month) untuk Golongan/Masa Kerja/Jabatan/Gaji
    $sessionMonth = (int) session('bulan') ?: 12;
    $monthlyUpdate = [];
    if ($request->filled('jabatan')) {
      $monthlyUpdate["Jabatan{$sessionMonth}"] = $request->jabatan;
    }
    if ($request->filled('gol')) {
      $monthlyUpdate["Gol{$sessionMonth}"] = $request->gol;
    }
    if ($request->filled('tahun')) {
      $monthlyUpdate["Tahun{$sessionMonth}"] = $request->tahun;
    }
    if ($request->filled('gaji')) {
      // normalize numeric value
      $gajiDigits = preg_replace('/[^0-9]/', '', (string) $request->gaji);
      if ($gajiDigits !== '') {
        $monthlyUpdate["Gaji{$sessionMonth}"] = (int) $gajiDigits;
      }
    }

    if (!empty($monthlyUpdate)) {
      Transaksi::where(function ($q) use ($identifier) {
          $q->where('NIDN', $identifier)
            ->orWhere('NUPTK', $identifier);
        })
        ->where('Tahun_Versi', $year)
        ->update($monthlyUpdate);
    }

    $dokumenPath = null;
    if ($request->hasFile('dokumen')) {
      $dokumen = $request->file('dokumen');
      $tanggalSekarang = date('Ymd');
      $originalName = pathinfo($dokumen->getClientOriginalName(), PATHINFO_FILENAME);
      $slugName = Str::slug($originalName, '_');
      $namaDokumenBaru = $tanggalSekarang . '_' . $slugName . '.' . $dokumen->getClientOriginalExtension();
      Storage::putFileAs('public/Dokumen_Histori_Dosen2', $dokumen, $namaDokumenBaru);
      $dokumenPath = $namaDokumenBaru;
    }

    HistoriDosen::create([
      'nidn' => !empty($data_dosen->NIDN) ? $data_dosen->NIDN : null,
      'nuptk' => !empty($data_dosen->NUPTK) ? $data_dosen->NUPTK : null,
      'nama' => $data_dosen->Nama,
      'pts' => $data_dosen->PTS,
      'kode_pt' => $data_dosen->Kode_PT,
      'pemegang_wilayah' => $data_dosen->Pemegang_Wilayah,
      'aktif' => $request->Aktif,
      'keterangan' => $request->keterangan,
      'pengguna' => auth()->user()->email,
      'tanggal_update_terakhir' => $request->tanggal_update_terakhir,
      'no_dokumen_ubah' => $request->no_dokumen_ubah,
      'tgl_dokumen_ubah' => $request->tgl_dokumen_ubah,
      'alasan_perubahan' => $request->alasan_perubahan,
      'dokumen' => $dokumenPath,
      'tanggal_update_terbaru' => now(),
    ]);

    return redirect()
      ->to(url('pic/lihat-data-dosen'))
      ->with('success', 'Data dosen berhasil diubah.');
  }
}
