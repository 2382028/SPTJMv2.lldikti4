<?php

namespace App\Http\Controllers\Pic;

use App\Http\Controllers\Controller;
use App\Helpers\ErrorAlias;
use App\Models\Bank;
use App\Models\HistoriDosen;
use App\Models\Jabatan;
use App\Models\Transaksi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UbahDataDosenController extends Controller
{
  public function editDataDosenPic(Request $request, $nidn)
  {
    // Ambil nilai khusus untuk bulan sesi sehingga form menampilkan kolom bulan yang relevan
    $bulanSession = (int) session('bulan') ?: 12;
    $masaCol = 'Tahun' . $bulanSession;
    $golCol = 'Gol' . $bulanSession;
    $jabCol = 'Jabatan' . $bulanSession;
    $gajiCol = 'Gaji' . $bulanSession;

    $data_dosen = Transaksi::where(function ($q) use ($nidn) {
        $q->where('NIDN', $nidn)
          ->orWhere('NUPTK', $nidn);
      })
      ->where('tahun_versi', session('tahun'))
      ->select(
        '*',
        DB::raw("NULLIF({$masaCol}, '') AS masa_kerja"),
        DB::raw("NULLIF({$golCol}, '') AS gol"),
        DB::raw("NULLIF({$jabCol}, '') AS jabatan"),
        DB::raw("COALESCE({$gajiCol}, 0) AS gaji" )
      )
      ->first();

    if (!$data_dosen) {
      return redirect()
        ->back()
        ->with('error', 'Data dosen tidak ditemukan!');
    }

    $banks = Bank::pluck('nama_bank');
    $jabatans = Jabatan::pluck('jabatan');
    $pics = User::where('role', 'pic')->pluck('email');

    $statusPerubahan = DB::table('h_perubahan')
      ->orderBy('kode')
      ->pluck('status_perubahan')
      ->filter()
      ->values();

    $mode = $request->query('mode', 'edit');

    return view('pic.ubah-data-dosen', compact('data_dosen', 'banks', 'jabatans', 'mode', 'pics', 'statusPerubahan'));
  }

  public function ubahDataDosenPic(Request $request, $nidn)
  {
    $request->validate([
      'no_dokumen_ubah' => 'required|string',
      'tgl_dokumen_ubah' => 'required|date',
      'alasan_perubahan' => 'required|string',
      'dokumen' => 'nullable|file|mimes:pdf,doc,docx',
      'tanggal_update_terakhir' => 'required|date',
      'keterangan' => 'required|string',
      'Aktif' => 'required|in:0,1',
      'kode_pt' => 'required|string',
      'pts' => 'required|string',
      'jenis' => 'required|string',
      'jabatan' => 'required|string',
      'gol' => 'required|string',
      'tahun' => 'required|numeric',
      'gaji' => 'required|numeric',
      'no_rekening' => 'required|string',
      'bank' => 'required|string',
      'nama_rekening' => 'required|string',
      'nama_penerima' => 'required|string',
      'npwp' => 'required|string',
      'pemegang_wilayah' => 'required|string',
      'eligible_span' => 'required|string',
    ]);

    try {
      $tmt = null;
      if (!empty($request->tanggal_update_terakhir)) {
        try {
          $tmt = Carbon::createFromFormat('d/m/Y', $request->tanggal_update_terakhir);
        } catch (\Exception $e) {
          $tmt = Carbon::parse($request->tanggal_update_terakhir);
        }
      } else {
        $tmt = Carbon::now();
      }

      $bulanAktif = (int) $tmt->format('n');
      $tahunTmt = (int) $tmt->format('Y');

      $year = (int) session('tahun');
      $mode = $request->input('mode', 'edit');

      $identifierParam = $nidn;
      $data_dosen = Transaksi::where(function ($q) use ($identifierParam) {
          $q->where('NIDN', $identifierParam)
            ->orWhere('NUPTK', $identifierParam);
        })
        ->where('Tahun_Versi', $year)
        ->first();

      if (!$data_dosen) {
        return response()->json(['success' => false, 'message' => 'Data dosen tidak ditemukan!']);
      }

      $oldAktif = (string) ($data_dosen->Aktif ?? '');
      $newAktif = (string) ($request->Aktif ?? '');

      $dataUpdateDasar = [
        'Kode_PT' => $request->kode_pt,
        'PTS' => $request->pts,
        'Jenis' => $request->jenis,
        'No_Rekening' => $request->no_rekening,
        'Bank' => $request->bank,
        'Nama_Rekening' => $request->nama_rekening,
        'Nama_Penerima' => $request->nama_penerima,
        'NPWP' => $request->npwp,
        'Pemegang_Wilayah' => $request->pemegang_wilayah,
        'Eligible_span' => $request->eligible_span,
        'Aktif' => $request->Aktif,
        'Keterangan' => $request->keterangan,
        'Tanggal_Update_Terakhir' => now(),
      ];

      $identifier = $data_dosen->NIDN ?? $data_dosen->NUPTK;
      Transaksi::where(function ($q) use ($identifier) {
          $q->where('NIDN', $identifier)
            ->orWhere('NUPTK', $identifier);
        })
        ->where('Tahun_Versi', $year)
        ->update($dataUpdateDasar);

      $alasan = (string) $request->alasan_perubahan;
      $statusChanged = ($oldAktif !== '' && $newAktif !== '' && $oldAktif !== $newAktif);

      $lookupGaji = function () use ($request) {
        $masaKerjaInput = $request->tahun;
        $masaKerjaInt = is_numeric($masaKerjaInput) ? (int) $masaKerjaInput : 0;
        if ($masaKerjaInt > 32) {
          $masaKerjaInt = 32;
        }
        $nominal = DB::table('c_grade')
          ->where('gol', $request->gol)
          ->where('masa_kerja', $masaKerjaInt)
          ->value('nominal');
        if (!is_null($nominal)) {
          return (int) $nominal;
        }
        return is_numeric($request->gaji) ? (int) $request->gaji : 0;
      };

      $kodeUsulanToApply = $alasan;
      if ($statusChanged && $oldAktif === '0' && $newAktif === '1') {
        $kodeUsulanToApply = null;
      } elseif (!$statusChanged) {
        if (strcasecmp(trim($alasan), 'Pengaktifan kembali') === 0 || strcasecmp(trim($alasan), 'Pengaktifan Kembali') === 0) {
          $kodeUsulanToApply = null;
        }
      }

      $gajiToApply = is_numeric($request->gaji) ? (int) $request->gaji : 0;
      if ($statusChanged && $oldAktif === '1' && $newAktif === '0') {
        $gajiToApply = 0;
        $kodeUsulanToApply = $alasan;
      } elseif ($statusChanged && $oldAktif === '0' && $newAktif === '1') {
        $gajiToApply = $lookupGaji();
        $kodeUsulanToApply = null;
      }

      // Update hanya untuk kolom bulan sesuai session('bulan')
      $sessionMonth = (int) session('bulan') ?: $bulanAktif;
      $updateSingle = [
        "Jabatan{$sessionMonth}" => $request->jabatan,
        "Gol{$sessionMonth}" => $request->gol,
        "Tahun{$sessionMonth}" => $request->tahun,
        "Gaji{$sessionMonth}" => $gajiToApply,
        "KodeUsulan{$sessionMonth}" => $kodeUsulanToApply,
      ];

      Transaksi::where(function ($q) use ($identifier) {
          $q->where('NIDN', $identifier)
            ->orWhere('NUPTK', $identifier);
        })
        ->where('Tahun_Versi', $year)
        ->update($updateSingle);

      $futureYears = Transaksi::where(function ($q) use ($identifier) {
          $q->where('NIDN', $identifier)
            ->orWhere('NUPTK', $identifier);
        })
        ->where('Tahun_Versi', '>', $year)
        ->orderBy('Tahun_Versi')
        ->pluck('Tahun_Versi')
        ->all();

      if (!empty($futureYears)) {
        foreach ($futureYears as $tahunVersiFuture) {
          $updateFuture = [];
          $updateFuture['Aktif'] = $request->Aktif;
          $updateFuture['Keterangan'] = $request->keterangan;
          // Untuk tahun mendatang, update hanya kolom bulan sesi agar konsisten dengan pembaruan bulan saat ini
          $updateFuture["Jabatan{$sessionMonth}"] = $request->jabatan;
          $updateFuture["Gol{$sessionMonth}"] = $request->gol;
          $updateFuture["Tahun{$sessionMonth}"] = $request->tahun;
          $updateFuture["Gaji{$sessionMonth}"] = $gajiToApply;
          $updateFuture["KodeUsulan{$sessionMonth}"] = $kodeUsulanToApply;

          Transaksi::where(function ($q) use ($identifier) {
              $q->where('NIDN', $identifier)
                ->orWhere('NUPTK', $identifier);
            })
            ->where('Tahun_Versi', $tahunVersiFuture)
            ->update($updateFuture);
        }
      }

      if ($mode === 'new' && $tahunTmt <= $year) {
        for ($tahunIterasi = $tahunTmt; $tahunIterasi <= $year; $tahunIterasi++) {
          $transaksiTahun = Transaksi::where(function ($q) use ($identifier) {
              $q->where('NIDN', $identifier)
                ->orWhere('NUPTK', $identifier);
            })
            ->where('Tahun_Versi', $tahunIterasi)
            ->first();

          if (!$transaksiTahun) {
            $dataBaru = $data_dosen->replicate()->toArray();
            unset($dataBaru['no']);
            $dataBaru['Tahun_Versi'] = $tahunIterasi;
            $dataBaru['Kode_PT'] = $request->kode_pt;
            $dataBaru['PTS'] = $request->pts;
            $dataBaru['Jenis'] = $request->jenis;
            $dataBaru['No_Rekening'] = $request->no_rekening;
            $dataBaru['Bank'] = $request->bank;
            $dataBaru['Nama_Rekening'] = $request->nama_rekening;
            $dataBaru['Nama_Penerima'] = $request->nama_penerima;
            $dataBaru['NPWP'] = $request->npwp;
            $dataBaru['Pemegang_Wilayah'] = $request->pemegang_wilayah;
            $dataBaru['Eligible_span'] = $request->eligible_span;

            if ($tahunIterasi < $year) {
              if ($tahunIterasi === $tahunTmt) {
                for ($i = 1; $i <= 12; $i++) {
                  if ($i < $bulanAktif) {
                    $dataBaru["KodeUsulan{$i}"] = null;
                  } else {
                    $dataBaru["KodeUsulan{$i}"] = $kodeUsulanToApply;
                  }
                }
              } else {
                for ($i = 1; $i <= 12; $i++) {
                  $dataBaru["KodeUsulan{$i}"] = $kodeUsulanToApply;
                }
              }
            }

            Transaksi::create($dataBaru);
          }
        }
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
        'nidn' => $data_dosen->NIDN ?? null,
        'nuptk' => $data_dosen->NUPTK ?? null,
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

      return response()->json(['success' => true, 'message' => 'Data dosen tersimpan!', 'nidn' => $data_dosen->NIDN]);
    } catch (\Exception $e) {
      $alias = ErrorAlias::fromThrowable($e, 'PIC-UBAH-DOSEN');
      Log::error('Pic\\UbahDataDosenController@ubahDataDosenPic failed', [
        'alias' => $alias['code'],
        'nidn' => $nidn,
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
      ]);

      return response()->json([
        'success' => false,
        'message' => 'Terjadi kesalahan saat menyimpan data. (Kode: ' . $alias['code'] . ')',
        'code' => $alias['code'],
      ], 500);
    }
  }
}
