<?php

namespace App\Http\Controllers\Pic;

use App\Helpers\ErrorAlias;
use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\HistoriDosen;
use App\Models\Pts;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UbahMKGolController extends Controller
{
  public function editMKGolPic($nidn)
  {
    // gunakan kolom sesuai bulan sesi (1-12) untuk menampilkan nilai spesifik bulan
    $bulanSession = (int) session('bulan') ?: 12;
    $masaCol = 'Tahun' . $bulanSession;
    $golCol = 'Gol' . $bulanSession;
    $jabCol = 'Jabatan' . $bulanSession;
    $gajiCol = 'Gaji' . $bulanSession;
    $data_dosen = Transaksi::where(function ($q) use ($nidn) {
        $q->where('NIDN', $nidn)->orWhere('NUPTK', $nidn);
      })
      ->where('tahun_versi', session('tahun'))
      ->select(
        '*',
        DB::raw("NULLIF({$masaCol}, '') AS masa_kerja"),
        DB::raw("NULLIF({$golCol}, '') AS gol"),
        DB::raw("NULLIF({$jabCol}, '') AS jabatan"),
        DB::raw("COALESCE({$gajiCol}, 0) AS gaji")
      )
      ->first();

    $mode = (string) request()->query('mode', '');
    $isDraftAddFlow = session()->has('draft_add_dosen.' . $nidn) || $mode === 'new';

    if (!$data_dosen) {
      $draft = session()->get('draft_add_dosen.' . $nidn);
      if (!$draft) {
        $allDrafts = session()->get('draft_add_dosen', []);
        foreach ($allDrafts as $d) {
          if (!empty($d['nuptk']) && (string) $d['nuptk'] === (string) $nidn) {
            $draft = $d;
            break;
          }
        }
      }
      if ($draft) {
        $data_dosen = (object) [
          'NIDN' => $draft['nidn'] ?? $nidn,
          'NIK' => $draft['nik'] ?? null,
          'NUPTK' => $draft['nuptk'] ?? null,
          'Nama' => $draft['nama'] ?? null,
          'TTL' => $draft['ttl'] ?? null,
          'Tanggal_Lahir' => $draft['tanggal_lahir'] ?? null,
          'Usia' => $draft['usia'] ?? null,
          'Kode_PT' => $draft['kode_pt'] ?? null,
          'PTS' => $draft['pts'] ?? null,
          'Jenis' => $draft['jenis'] ?? null,
          'No_Rekening' => $draft['no_rekening'] ?? '',
          'Bank' => $draft['bank'] ?? '',
          'Nama_Rekening' => $draft['nama_rekening'] ?? '',
          'Nama_Penerima' => $draft['nama_penerima'] ?? '',
          'NPWP' => $draft['npwp'] ?? '',
          'Pemegang_Wilayah' => $draft['pemegang_wilayah'] ?? null,
          'Eligible_span' => $draft['eligible_span'] ?? null,
          'Aktif' => $draft['aktif'] ?? '1',
          'Keterangan' => null,
          'masa_kerja' => null,
          'gol' => null,
          'jabatan' => null,
          'gaji' => 0,
        ];

        for ($i = 1; $i <= 12; $i++) {
          $data_dosen->{"Jabatan{$i}"} = null;
          $data_dosen->{"Gol{$i}"} = null;
          $data_dosen->{"Tahun{$i}"} = null;
          $data_dosen->{"Gaji{$i}"} = null;
          $data_dosen->{"KodeUsulan{$i}"} = null;
        }
      }
    }

    // Ensure $data_dosen is an object so property access is safe
    $data_dosen = $data_dosen ?? (object) [];

    // If some TMT fields are empty, try to fill them from the most recent existing row
    try {
      $needsFill = empty($data_dosen->TMT_JAD_Pertama) || empty($data_dosen->TMT_JAD_Akhir) || empty($data_dosen->TMT_Inpassing_Akhir);
      if ($needsFill) {
        $fallback = DB::table('s_transaksi_2')
          ->where(function ($q) use ($nidn) {
            $q->where('NIDN', $nidn)->orWhere('NUPTK', $nidn);
          })
          ->where(function ($q) {
            $q->whereNotNull('TMT_JAD_Pertama')
              ->orWhereNotNull('TMT_JAD_Akhir')
              ->orWhereNotNull('TMT_Inpassing_Akhir');
          })
          ->orderBy('Tahun_Versi', 'desc')
          ->first();

        if ($fallback) {
          if (empty($data_dosen->TMT_JAD_Pertama) && !empty($fallback->TMT_JAD_Pertama)) {
            $data_dosen->TMT_JAD_Pertama = $fallback->TMT_JAD_Pertama;
          }
          if (empty($data_dosen->TMT_JAD_Akhir) && !empty($fallback->TMT_JAD_Akhir)) {
            $data_dosen->TMT_JAD_Akhir = $fallback->TMT_JAD_Akhir;
          }
          if (empty($data_dosen->TMT_Inpassing_Akhir) && !empty($fallback->TMT_Inpassing_Akhir)) {
            $data_dosen->TMT_Inpassing_Akhir = $fallback->TMT_Inpassing_Akhir;
          }
        }
      }
    } catch (\Throwable $e) {
      $alias = ErrorAlias::fromThrowable($e, 'PIC-MKGOL-FILL-TMT');
      Log::error('PIC gagal mengisi fallback TMT.', [
        'alias' => $alias['code'],
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
        'nidn' => $nidn,
      ]);
    }

    $grades = Grade::all();
    return view('pic.ubah-mk-gol', compact('data_dosen', 'grades', 'isDraftAddFlow'));
  }

  public function ubahMkGolPic(Request $request, $nidn)
  {
    $request->validate([
      'no_dokumen_ubah' => 'required|string',
      'tgl_dokumen_ubah' => 'required|date',
      'alasan_perubahan' => 'required|string',
      'dokumen' => 'required|file|mimes:pdf,doc,docx',
      'tanggal_update_terakhir' => 'required|date',
      'keterangan' => 'required|string',
      'Aktif' => 'required|in:0,1',
      'jenis' => 'required|string',
      'tmt_jad_pertama' => 'nullable|string|max:100',
      'tmt_jad_akhir' => 'nullable|string|max:50',
      'tmt_inpassing_akhir' => 'nullable|string|max:50',
      'jabatan' => 'required|string',
      'gol' => 'required|string',
      'tahun' => 'required|numeric',
      'gaji' => 'nullable',
      'eligible_span' => 'required|string',
    ]);

    $tmt = Carbon::parse($request->tanggal_update_terakhir);
    $tmtBulan = (int) $tmt->format('n');
    $tmtTahun = (int) $tmt->format('Y');

    $alasan = trim((string) $request->alasan_perubahan);

    $maxYearAvailable = (int) (DB::table('s_transaksi_2')->max('Tahun_Versi') ?? 0);
    if ($maxYearAvailable <= 0) {
      $maxYearAvailable = (int) session('tahun');
    }
    if ($maxYearAvailable < $tmtTahun) {
      $maxYearAvailable = $tmtTahun;
    }

    $allowAutoCreateYears = (strcasecmp($alasan, 'Penambahan Data Baru') === 0);

    $hasAnyRow = DB::table('s_transaksi_2')
      ->where(function ($q) use ($nidn) {
        $q->where('NIDN', $nidn)->orWhere('NUPTK', $nidn);
      })
      ->exists();

    if ($allowAutoCreateYears && !$hasAnyRow) {
      $draft = session()->get('draft_add_dosen.' . $nidn);
      if (!$draft) {
        $allDrafts = session()->get('draft_add_dosen', []);
        foreach ($allDrafts as $d) {
          if (!empty($d['nuptk']) && (string) $d['nuptk'] === (string) $nidn) {
            $draft = $d;
            break;
          }
        }
      }

      if (!$draft) {
        return redirect()->back()->with('error', 'Draft data dosen tidak ditemukan. Silakan ulangi proses Tambah Dosen.');
      }

      $pts = Pts::where('kode_pts', $draft['kode_pt'] ?? null)->first();
      if (!$pts) {
        return redirect()->back()->with('error', 'Kode PTS pada draft tidak ditemukan.');
      }

      DB::transaction(function () use ($draft, $pts, $nidn, $tmtTahun) {
        $maxYearAvailableLocal = (int) (DB::table('s_transaksi_2')->max('Tahun_Versi') ?? 0);
        if ($maxYearAvailableLocal <= 0) {
          $maxYearAvailableLocal = (int) session('tahun');
        }
        if ($maxYearAvailableLocal < (int) $tmtTahun) {
          $maxYearAvailableLocal = (int) $tmtTahun;
        }

        $firstYear = (int) $tmtTahun;
        $existsFirst = DB::table('s_transaksi_2')
          ->where(function ($q) use ($nidn) {
            $q->where('NIDN', $nidn)->orWhere('NUPTK', $nidn);
          })
          ->where('Tahun_Versi', $firstYear)
          ->exists();

        if (!$existsFirst) {
          DB::table('s_transaksi_2')->insert([
            'Tahun_Versi' => $firstYear,
            'NIDN' => $draft['nidn'] ?? $nidn,
            'NIK' => $draft['nik'] ?? null,
            'NUPTK' => $draft['nuptk'] ?? null,
            'Nama' => $draft['nama'] ?? null,
            'TTL' => $draft['ttl'] ?? null,
            'Tanggal_Lahir' => $draft['tanggal_lahir'] ?? null,
            'Usia' => $draft['usia'] ?? null,
            'Kode_PT' => $draft['kode_pt'] ?? null,
            'PTS' => $pts->nama_pts,
            'Jenis' => $draft['jenis'] ?? null,
            'No_Rekening' => $draft['no_rekening'] ?? null,
            'Bank' => $draft['bank'] ?? null,
            'Nama_Rekening' => $draft['nama_rekening'] ?? null,
            'Nama_Penerima' => $draft['nama_penerima'] ?? null,
            'NPWP' => $draft['npwp'] ?? null,
            'Pemegang_Wilayah' => $draft['pemegang_wilayah'] ?? null,
            'Eligible_span' => $draft['eligible_span'] ?? null,
            'Aktif' => $draft['aktif'] ?? '1',
            'Keterangan' => null,
            'Tanggal_Update_Terakhir' => now(),
          ]);
        }

        $template = DB::table('s_transaksi_2')
          ->where(function ($q) use ($nidn) {
            $q->where('NIDN', $nidn)->orWhere('NUPTK', $nidn);
          })
          ->where('Tahun_Versi', $firstYear)
          ->first();

        if ($template) {
          $templateArr = (array) $template;
          foreach (array_keys($templateArr) as $key) {
            $lower = strtolower((string) $key);
            if (in_array($lower, ['no', 'id', 'pk'], true)) {
              unset($templateArr[$key]);
            }
          }

          for ($y = $firstYear + 1; $y <= $maxYearAvailableLocal; $y++) {
            $existsY = DB::table('s_transaksi_2')
              ->where(function ($q) use ($nidn) {
                $q->where('NIDN', $nidn)->orWhere('NUPTK', $nidn);
              })
              ->where('Tahun_Versi', $y)
              ->exists();
            if ($existsY) {
              continue;
            }
            $row = $templateArr;
            $row['Tahun_Versi'] = $y;
            $row['NIDN'] = $nidn;
            $row['Tanggal_Update_Terakhir'] = now();
            DB::table('s_transaksi_2')->insert($row);
          }
        }
      });
    }

    if ($allowAutoCreateYears) {
      $templateExisting = DB::table('s_transaksi_2')
        ->where(function ($q) use ($nidn) {
          $q->where('NIDN', $nidn)->orWhere('NUPTK', $nidn);
        })
        ->orderBy('Tahun_Versi', 'desc')
        ->first();
      if ($templateExisting) {
        $templateArr = (array) $templateExisting;
        foreach (array_keys($templateArr) as $key) {
          $lower = strtolower((string) $key);
          if (in_array($lower, ['no', 'id', 'pk'], true)) {
            unset($templateArr[$key]);
          }
        }
        for ($y = (int) $tmtTahun; $y <= $maxYearAvailable; $y++) {
          $existsY = DB::table('s_transaksi_2')
            ->where(function ($q) use ($nidn) {
              $q->where('NIDN', $nidn)->orWhere('NUPTK', $nidn);
            })
            ->where('Tahun_Versi', $y)
            ->exists();
          if ($existsY) {
            continue;
          }
          $row = $templateArr;
          $row['Tahun_Versi'] = $y;
          $row['NIDN'] = $nidn;
          $row['Tanggal_Update_Terakhir'] = now();
          DB::table('s_transaksi_2')->insert($row);
        }
      }
    }

    $sessionYear = (int) session('tahun');
    $yearsToUpdate = DB::table('s_transaksi_2')
      ->where(function ($q) use ($nidn) {
        $q->where('NIDN', $nidn)->orWhere('NUPTK', $nidn);
      })
      ->where('Tahun_Versi', '>=', $tmtTahun)
      ->orderBy('Tahun_Versi', 'asc')
      ->pluck('Tahun_Versi')
      ->unique()
      ->values();

    if ($yearsToUpdate->isEmpty()) {
      return redirect()->back()->with('error', 'Data dosen tidak ditemukan untuk Tahun Versi >= ' . $tmtTahun . '!');
    }

    $baseRow = DB::table('s_transaksi_2')
      ->where(function ($q) use ($nidn) {
        $q->where('NIDN', $nidn)->orWhere('NUPTK', $nidn);
      })
      ->where('Tahun_Versi', $sessionYear)
      ->first();
    if (!$baseRow) {
      $baseRow = DB::table('s_transaksi_2')
        ->where(function ($q) use ($nidn) {
          $q->where('NIDN', $nidn)->orWhere('NUPTK', $nidn);
        })
        ->where('Tahun_Versi', (int) $yearsToUpdate->first())
        ->first();
    }

    $gajiToApply = null;
    $rawGajiInput = (string) $request->gaji;
    $gajiDigits = preg_replace('/[^0-9]/', '', $rawGajiInput);
    if ($gajiDigits !== '') {
      $gajiToApply = (int) $gajiDigits;
    } else {
      $masaKerjaInput = $request->tahun;
      $masaKerjaInt = is_numeric($masaKerjaInput) ? (int) $masaKerjaInput : 0;
      if ($masaKerjaInt > 32) {
        $masaKerjaInt = 32;
      }
      $gajiLookup = DB::table('c_grade')
        ->where('gol', $request->gol)
        ->where('masa_kerja', $masaKerjaInt)
        ->value('nominal');
      if (!is_null($gajiLookup)) {
        $gajiToApply = (int) $gajiLookup;
      }
    }

    $jenisToApply = $request->jenis;
    $eligibleToApply = $request->eligible_span;
    $keteranganToApply = $request->keterangan;

    $tmtJadPertamaToApply = trim((string) $request->input('tmt_jad_pertama', ''));
    $tmtJadPertamaToApply = ($tmtJadPertamaToApply !== '') ? $tmtJadPertamaToApply : null;
    $tmtJadAkhirToApply = trim((string) $request->input('tmt_jad_akhir', ''));
    $tmtJadAkhirToApply = ($tmtJadAkhirToApply !== '') ? $tmtJadAkhirToApply : null;
    $tmtInpassingAkhirToApply = trim((string) $request->input('tmt_inpassing_akhir', ''));
    $tmtInpassingAkhirToApply = ($tmtInpassingAkhirToApply !== '') ? $tmtInpassingAkhirToApply : null;

    // Untuk update, tuliskan hanya ke kolom sesuai session('bulan') agar konsisten dengan permintaan
    $sessionMonth = (int) session('bulan') ?: $tmtBulan;

    foreach ($yearsToUpdate as $tahunVersi) {
      $tahunVersi = (int) $tahunVersi;

      $dataUpdate = [];
      // Jika Penambahan Data Baru dan tahun adalah tahun mulai, biarkan bulan sebelum tmt bernilai null/kosong
      if (strcasecmp($alasan, 'Penambahan Data Baru') === 0 && $tahunVersi === $tmtTahun) {
        for ($b = 1; $b < $tmtBulan; $b++) {
          $dataUpdate['Jabatan' . $b] = $dataUpdate['Jabatan' . $b] ?? null;
          $dataUpdate['Gol' . $b] = $dataUpdate['Gol' . $b] ?? null;
          $dataUpdate['Tahun' . $b] = $dataUpdate['Tahun' . $b] ?? null;
          $dataUpdate['Gaji' . $b] = $dataUpdate['Gaji' . $b] ?? 0;
          $dataUpdate['KodeUsulan' . $b] = $dataUpdate['KodeUsulan' . $b] ?? 'Data Baru';
        }
      }

      // Set hanya kolom bulan sesi
      $dataUpdate['Jabatan' . $sessionMonth] = $request->jabatan;
      $dataUpdate['Gol' . $sessionMonth] = $request->gol;
      $dataUpdate['Tahun' . $sessionMonth] = $request->tahun;
      if (!is_null($gajiToApply)) {
        $dataUpdate['Gaji' . $sessionMonth] = $gajiToApply;
      }
      // Jika ini adalah penambahan data baru, kosongkan kode usulan sebelum tmt jika tahun sama
      $dataUpdate['KodeUsulan' . $sessionMonth] = (strcasecmp($alasan, 'Penambahan Data Baru') === 0 && $tahunVersi === $tmtTahun) ? null : null;

      $dataUpdate['Keterangan'] = $keteranganToApply;
      $dataUpdate['TMT_JAD_Pertama'] = $tmtJadPertamaToApply;
      $dataUpdate['TMT_JAD_Akhir'] = $tmtJadAkhirToApply;
      $dataUpdate['TMT_Inpassing_Akhir'] = $tmtInpassingAkhirToApply;

      DB::table('s_transaksi_2')
        ->where(function ($q) use ($nidn) {
          $q->where('NIDN', $nidn)->orWhere('NUPTK', $nidn);
        })
        ->where('Tahun_Versi', $tahunVersi)
        ->update($dataUpdate);
    }

    try {
      $sessionOnlyUpdate = [
        'Aktif' => $request->Aktif,
        'Jenis' => $jenisToApply,
        'Eligible_span' => $eligibleToApply,
        'TMT_JAD_Pertama' => $tmtJadPertamaToApply,
        'TMT_JAD_Akhir' => $tmtJadAkhirToApply,
        'TMT_Inpassing_Akhir' => $tmtInpassingAkhirToApply,
      ];
      if ($sessionYear >= $tmtTahun) {
        $sessionOnlyUpdate['Keterangan'] = $keteranganToApply;
      }

      DB::table('s_transaksi_2')
        ->where(function ($q) use ($nidn) {
          $q->where('NIDN', $nidn)->orWhere('NUPTK', $nidn);
        })
        ->where('Tahun_Versi', $sessionYear)
        ->update($sessionOnlyUpdate);
    } catch (\Throwable $e) {
      $alias = ErrorAlias::fromThrowable($e, 'PIC-MKGOL-SESSION-UPDATE');
      Log::error('PIC gagal update data tahun sesi saat ubah MK/Gol.', [
        'alias' => $alias['code'],
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
        'nidn' => $nidn,
        'sessionYear' => $sessionYear ?? null,
        'tmtTahun' => $tmtTahun ?? null,
      ]);
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
      'nidn' => $baseRow->NIDN ?? null,
      'nuptk' => $baseRow->NUPTK ?? null,
      'nama' => $baseRow->Nama ?? null,
      'pts' => $baseRow->PTS ?? null,
      'kode_pt' => $baseRow->Kode_PT ?? null,
      'pemegang_wilayah' => $baseRow->Pemegang_Wilayah ?? null,
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

    session()->forget('draft_add_dosen.' . $nidn);

    return redirect()
      ->to(url('pic/lihat-data-dosen'))
      ->with('success', 'Data dosen berhasil diubah.');
  }
}
