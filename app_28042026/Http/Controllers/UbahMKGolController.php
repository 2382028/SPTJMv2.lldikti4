<?php

namespace App\Http\Controllers;

use App\Helpers\ErrorAlias;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Transaksi;
use App\Models\Grade;
use App\Models\HistoriDosen;
use App\Models\Dosen;
use App\Models\Pts;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class UbahMKGolController extends Controller
{
  public function editMKGol($nidn)
  {
    Log::info('UbahMKGolController@editMKGol called', ['nidn' => $nidn, 'session_tahun' => session('tahun'), 'session_bulan' => session('bulan')]);
    // Gunakan bulan aktif dari session agar form menampilkan nilai sesuai periode yang dipilih.
    // Fallback ke 12 (Desember) bila session('bulan') kosong.
    $bulanSession = (int) session('bulan') ?: 12;
    $bulanSession = max(1, min(12, $bulanSession));

    $masa_kerja = "NULLIF(Tahun{$bulanSession}, '')";
    $golongan   = "NULLIF(Gol{$bulanSession}, '')";
    $jabatan    = "NULLIF(Jabatan{$bulanSession}, '')";
    $gaji       = "NULLIF(Gaji{$bulanSession}, 0)";
    $data_dosen = Transaksi::where(function($q) use ($nidn) {
        $q->where('NIDN', $nidn)->orWhere('NUPTK', $nidn);
      })
      ->where('tahun_versi', session('tahun'))
      ->select(
        "*",
        DB::raw("$masa_kerja AS masa_kerja"),
        DB::raw("$golongan AS gol"),
        DB::raw("$jabatan AS jabatan"),
        DB::raw("$gaji AS gaji")
      )
      ->first();

    // Tentukan mode halaman berdasarkan sumber navigasi:
    // - mode=new (dari Tambah Dosen) => hanya opsi "Penambahan Data Baru"
    // - selain itu (dari View Data Dosen / edit biasa) => hanya opsi "Perubahan Golongan dan Masa Kerja"
    $mode = (string) request()->query('mode', '');
    $isDraftAddFlow = session()->has('draft_add_dosen.' . $nidn) || $mode === 'new';

    // Jika belum ada data di s_transaksi_2 (tambah dosen belum final), ambil draft dari session.
    if (!$data_dosen) {
      // Try to find draft by nidn key first, then by matching nuptk inside drafts
      $draft = session()->get('draft_add_dosen.' . $nidn);
      if (!$draft) {
        $allDrafts = session()->get('draft_add_dosen', []);
        foreach ($allDrafts as $d) {
          if (!empty($d['nuptk']) && (string)$d['nuptk'] === (string)$nidn) {
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
          // Auto-fill from Tambah Dosen draft
          'TMT_JAD_Pertama' => $draft['tmt_jad_pertama'] ?? null,
          'TMT_JAD_Akhir' => $draft['tmt_jad_akhir'] ?? null,
          'TMT_Inpassing_Akhir' => $draft['tmt_inpassing_akhir'] ?? null,
          'Inpassing' => $draft['inpassing'] ?? null,
          'No_Rekening' => $draft['no_rekening'] ?? '',
          'Bank' => $draft['bank'] ?? '',
          'Nama_Rekening' => $draft['nama_rekening'] ?? '',
          'Nama_Penerima' => $draft['nama_penerima'] ?? '',
          'NPWP' => $draft['npwp'] ?? '',
          'Pemegang_Wilayah' => $draft['pemegang_wilayah'] ?? null,
          'Eligible_span' => $draft['eligible_span'] ?? null,
          'Aktif' => $draft['aktif'] ?? '1',
          'Keterangan' => null,
          // Field hasil COALESCE (untuk select default)
          'masa_kerja' => null,
          'gol' => null,
          'jabatan' => null,
          'gaji' => 0,
        ];

        // Pastikan properti kolom bulanan ada agar blade tidak error (biarkan kosong/null).
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
          ->where(function($q) use ($nidn) {
            $q->where('NIDN', $nidn)->orWhere('NUPTK', $nidn);
          })
          ->where(function($q) {
            $q->whereNotNull('TMT_JAD_Pertama')
              ->orWhereNotNull('TMT_JAD_Akhir')
              ->orWhereNotNull('TMT_Inpassing_Akhir');
          })
          ->orderBy('Tahun_Versi', 'desc')
          ->first();

        if ($fallback) {
          Log::debug('UbahMKGolController@editMKGol - fallback values found', ['nidn' => $nidn, 'fallback' => (array) $fallback]);
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
      $alias = ErrorAlias::fromThrowable($e, 'ADM-MKGOL-FILL-TMT');
      Log::error('UbahMKGolController@editMKGol exception while filling TMT fields', [
        'alias' => $alias['code'],
        'nidn' => $nidn,
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
      ]);
      // ignore fallback errors
    }

    // Normalize TMT fields to Y-m-d for HTML date inputs (accept various stored formats)
    try {
      $formatDateForInput = function($v) {
        if (empty($v)) return '';
        try {
          if (strpos($v, '/') !== false) {
            $d = \Carbon\Carbon::createFromFormat('d/m/Y', $v);
          } else {
            $d = \Carbon\Carbon::parse($v);
          }
          return $d->format('Y-m-d');
        } catch (\Exception $ex) {
          return '';
        }
      };

      $data_dosen->TMT_JAD_Pertama = $formatDateForInput($data_dosen->TMT_JAD_Pertama ?? null);
      $data_dosen->TMT_JAD_Akhir = $formatDateForInput($data_dosen->TMT_JAD_Akhir ?? null);
      $data_dosen->TMT_Inpassing_Akhir = $formatDateForInput($data_dosen->TMT_Inpassing_Akhir ?? null);
    } catch (\Throwable $e) {
      // ignore normalization errors
    }
    $grades = Grade::all();
    Log::debug('UbahMKGolController@editMKGol preparing view', ['nidn' => $nidn, 'hasData' => !empty((array)$data_dosen)]);
    return view('admin.ubah-mk-gol', compact('data_dosen', 'grades', 'isDraftAddFlow'));
  }

  public function ubahMkGol(Request $request, $nidn)
  {
    // Always log submit attempt (even if validation fails)
    try {
      Log::info('UbahMKGolController@ubahMkGol submit attempt', [
        'nidn' => $nidn,
        'method' => $request->method(),
        'has_dokumen' => $request->hasFile('dokumen'),
        'input' => $request->except(['dokumen']),
      ]);
    } catch (\Throwable $e) {
      Log::warning('UbahMKGolController@ubahMkGol failed to log submit attempt', ['nidn' => $nidn, 'err' => $e->getMessage()]);
    }

    try {
      $request->validate([
        'no_dokumen_ubah' => 'required|string',
        'tgl_dokumen_ubah' => 'required|date',
        'alasan_perubahan' => 'required|in:Penambahan Data Baru,Perubahan Golongan dan Masa Kerja',
        'dokumen' => 'required|file|mimes:pdf,doc,docx',
        'tanggal_update_terakhir' => 'required|date',
        'keterangan' => 'required|string',
        'Aktif' => 'required|in:0,1',
        'jenis' => 'required|string',
        'tmt_jad_pertama' => 'nullable|string|max:100',
        'tmt_jad_akhir' => 'nullable|string|max:50',
        'tmt_inpassing_akhir' => 'nullable|string|max:50',
        'inpassing' => 'required|in:Berdasarkan Inpassing,Sesuai TMT Awal dan Akhir',
        'jabatan' => 'required|string',
        'gol' => 'nullable|string',
        'tahun' => 'nullable|numeric',
        'gaji' => 'nullable',
        'eligible_span' => 'required|string',
      ]);
    } catch (ValidationException $e) {
      Log::warning('UbahMKGolController@ubahMkGol validation failed', ['nidn' => $nidn, 'errors' => $e->errors()]);
      throw $e;
    }

    try {
    $tmt = Carbon::parse($request->tanggal_update_terakhir);
    $tmtBulan = (int) $tmt->format('n');
    $tmtTahun = (int) $tmt->format('Y');

    $alasan = trim((string) $request->alasan_perubahan);

    // Tahun_Versi maksimum yang "tersedia" di sistem (dipakai sebagai batas pembuatan data).
    // Contoh: jika data sampai 2027, maka saat simpan akan dibuatkan record dosen sampai 2027.
    $maxYearAvailable = (int) (DB::table('s_transaksi_2')->max('Tahun_Versi') ?? 0);
    if ($maxYearAvailable <= 0) {
      $maxYearAvailable = (int) session('tahun');
    }
    if ($maxYearAvailable < $tmtTahun) {
      $maxYearAvailable = $tmtTahun;
    }

    // Auto-create tahun (TMT..maxYearAvailable) hanya untuk alur Tambah Dosen / Penambahan Data Baru.
    // Untuk "Perubahan Golongan dan Masa Kerja" dari View Data Dosen, jangan membuat row Tahun_Versi baru.
    $allowAutoCreateYears = (strcasecmp($alasan, 'Penambahan Data Baru') === 0);

    // Jika data belum ada di DB (alur tambah dosen), buat record dulu dari draft session.
    $hasAnyRow = DB::table('s_transaksi_2')
      ->where(function($q) use ($nidn) {
        $q->where('NIDN', $nidn)->orWhere('NUPTK', $nidn);
      })
      ->exists();
    if ($allowAutoCreateYears && !$hasAnyRow) {
      $draft = session()->get('draft_add_dosen.' . $nidn);
      if (!$draft) {
        // Fallback: jika parameter route berisi NUPTK, draft mungkin disimpan dengan key NIDN.
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
        // Catatan:
        // Untuk mode=new (Tambah Dosen / Penambahan Data Baru), data baru harus masuk ke s_transaksi_2.
        // Jangan insert ke s_transaksi_2 dari sini.

        // Insert transaksi s_transaksi_2 untuk rentang tahun dari tahun TMT sampai tahun maksimum yang tersedia.
        $maxYearAvailableLocal = (int) (DB::table('s_transaksi_2')->max('Tahun_Versi') ?? 0);
        if ($maxYearAvailableLocal <= 0) {
          $maxYearAvailableLocal = (int) session('tahun');
        }
        if ($maxYearAvailableLocal < (int) $tmtTahun) {
          $maxYearAvailableLocal = (int) $tmtTahun;
        }

        // Buat tahun pertama dari draft (sebagai template), lalu tahun berikutnya clone dari template.
        $firstYear = (int) $tmtTahun;
        $existsFirst = DB::table('s_transaksi_2')
          ->where(function($q) use ($nidn) {
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
          ->where(function($q) use ($nidn) {
            $q->where('NIDN', $nidn)->orWhere('NUPTK', $nidn);
          })
          ->where('Tahun_Versi', $firstYear)
          ->first();
        if ($template) {
          $templateArr = (array) $template;

          // Hindari duplikasi primary key saat clone row.
          // Kolom PK di tabel ini biasanya `no`, tapi beberapa DB bisa punya variasi case (No/NO) atau nama lain.
          foreach (array_keys($templateArr) as $key) {
            $lower = strtolower((string) $key);
            if (in_array($lower, ['no', 'id', 'pk'], true)) {
              unset($templateArr[$key]);
            }
          }

          for ($y = $firstYear + 1; $y <= $maxYearAvailableLocal; $y++) {
            $existsY = DB::table('s_transaksi_2')
              ->where(function($q) use ($nidn) {
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
      // Jika dosen sudah ada, pastikan tidak ada "bolong" tahun dari tahun TMT sampai tahun maksimum.
      // Tahun yang tidak ada akan dibuat dengan menyalin row yang sudah ada (agar kolom wajib tetap terisi).
      $templateExisting = DB::table('s_transaksi_2')
        ->where(function($q) use ($nidn) {
          $q->where('NIDN', $nidn)->orWhere('NUPTK', $nidn);
        })
        ->orderBy('Tahun_Versi', 'desc')
        ->first();
      if ($templateExisting) {
        $templateArr = (array) $templateExisting;

        // Hindari duplikasi primary key saat clone row.
        foreach (array_keys($templateArr) as $key) {
          $lower = strtolower((string) $key);
          if (in_array($lower, ['no', 'id', 'pk'], true)) {
            unset($templateArr[$key]);
          }
        }
        for ($y = (int) $tmtTahun; $y <= $maxYearAvailable; $y++) {
          $existsY = DB::table('s_transaksi_2')
            ->where(function($q) use ($nidn) {
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

    // Bulan aktif dari session untuk penyesuaian kolom bulanan.
    $bulanSession = (int) session('bulan') ?: 12;
    $bulanSession = max(1, min(12, $bulanSession));

    // Update berdasarkan "Terhitung Mulai Tanggal" (TMT):
    // - Tahun_Versi == tahun TMT: update bulan TMT..12
    // - Tahun_Versi > tahun TMT: update bulan 1..12
    // Catatan:
    // - Untuk Perubahan Golongan dan Masa Kerja, TIDAK membuat Tahun_Versi baru; hanya update yang sudah tersedia.
    // - Untuk Penambahan Data Baru, Tahun_Versi bisa dibuat lebih dulu (lihat block allowAutoCreateYears).
    $sessionYear = (int) session('tahun');

    $yearsToUpdate = DB::table('s_transaksi_2')
      ->where(function($q) use ($nidn) {
        $q->where('NIDN', $nidn)->orWhere('NUPTK', $nidn);
      })
      ->where('Tahun_Versi', '>=', $tmtTahun)
      ->orderBy('Tahun_Versi', 'asc')
      ->pluck('Tahun_Versi')
      ->unique()
      ->values();

    if ($yearsToUpdate->isEmpty()) {
      return redirect()
        ->back()
        ->with('error', 'Data dosen tidak ditemukan untuk Tahun Versi >= ' . $tmtTahun . '!');
    }

    // Base row untuk histori: prioritas tahun session agar konsisten dengan halaman.
    $baseRow = DB::table('s_transaksi_2')
      ->where(function($q) use ($nidn) {
        $q->where('NIDN', $nidn)->orWhere('NUPTK', $nidn);
      })
      ->where('Tahun_Versi', $sessionYear)
      ->first();
    if (!$baseRow) {
      $baseRow = DB::table('s_transaksi_2')
        ->where(function($q) use ($nidn) {
          $q->where('NIDN', $nidn)->orWhere('NUPTK', $nidn);
        })
        ->where('Tahun_Versi', (int) $yearsToUpdate->first())
        ->first();
    }

    // Optional update: masa kerja (Tahun*) dan gaji (Gaji*) hanya diubah jika input tersedia.
    $shouldUpdateMasaKerja = $request->filled('tahun');
    $shouldUpdateGaji = $request->filled('gaji') || $shouldUpdateMasaKerja;

    // Determine gaji value to apply only when needed: prefer explicit submitted `gaji` (Biaya),
    // otherwise lookup from `c_grade` using masa kerja capped to 32.
    $gajiToApply = null;
    if ($shouldUpdateGaji) {
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
    }

    // Jenis/Eligible/Keterangan: update sesuai tahun session (bukan berdasarkan TMT)
    $jenisToApply = $request->jenis;
    $eligibleToApply = $request->eligible_span;
    $keteranganToApply = $request->keterangan;

    $tmtJadPertamaToApply = trim((string) $request->input('tmt_jad_pertama', ''));
    $tmtJadPertamaToApply = ($tmtJadPertamaToApply !== '') ? $tmtJadPertamaToApply : null;
    $tmtJadAkhirToApply = trim((string) $request->input('tmt_jad_akhir', ''));
    $tmtJadAkhirToApply = ($tmtJadAkhirToApply !== '') ? $tmtJadAkhirToApply : null;
    $tmtInpassingAkhirToApply = trim((string) $request->input('tmt_inpassing_akhir', ''));
    $tmtInpassingAkhirToApply = ($tmtInpassingAkhirToApply !== '') ? $tmtInpassingAkhirToApply : null;

    $inpassingToApply = trim((string) $request->input('inpassing', ''));

    foreach ($yearsToUpdate as $tahunVersi) {
      $tahunVersi = (int) $tahunVersi;
      $bulanMulai = ($tahunVersi === $tmtTahun) ? $tmtBulan : 1;

      // Catatan aturan:
      // - Jabatan/Gol/Tahun/Gaji: tetap di-update untuk semua Tahun_Versi >= TMT yang tersedia
      // - Keterangan: tetap mengikuti TMT (di-update untuk semua Tahun_Versi >= TMT)
      // - Aktif/Jenis/Eligible_span: hanya di-update untuk Tahun_Versi sesuai session('tahun')
      $dataUpdate = [];

      if (strcasecmp($alasan, 'Penambahan Data Baru') === 0) {
        // Untuk Data Baru:
        // - Bulan sebelum TMT (hanya di tahun TMT): Gaji=0, KodeUsulan="Data Baru", Gol/Tahun/Jabatan = NULL
        // - Bulan mulai TMT sampai Desember: isi nilai input (jabatan/gol/tahun/gaji) dan KodeUsulan NULL
        if ($tahunVersi === $tmtTahun) {
          for ($bulan = 1; $bulan < $tmtBulan; $bulan++) {
            $dataUpdate['Jabatan' . $bulan] = null;
            $dataUpdate['Gol' . $bulan] = null;
            $dataUpdate['Tahun' . $bulan] = null;
            $dataUpdate['Gaji' . $bulan] = 0;
            $dataUpdate['KodeUsulan' . $bulan] = 'Data Baru';
          }
        }

        for ($bulan = $bulanMulai; $bulan <= 12; $bulan++) {
          $dataUpdate['Jabatan' . $bulan] = $request->jabatan;
          $dataUpdate['Gol' . $bulan] = $request->gol;
          if ($shouldUpdateMasaKerja) {
            $dataUpdate['Tahun' . $bulan] = $request->tahun;
          }
          if ($shouldUpdateGaji && !is_null($gajiToApply)) {
            $dataUpdate['Gaji' . $bulan] = $gajiToApply;
          }
          // Setelah TMT tidak perlu tanda "Data Baru"
          $dataUpdate['KodeUsulan' . $bulan] = null;
        }
      } else {
        // Perubahan Golongan/MK (berdasarkan TMT):
        // - Tahun TMT: update bulan TMT..12
        // - Tahun setelahnya: update bulan 1..12
        for ($bulan = $bulanMulai; $bulan <= 12; $bulan++) {
          $dataUpdate['Jabatan' . $bulan] = $request->jabatan;
          $dataUpdate['Gol' . $bulan] = $request->gol;
          if ($shouldUpdateMasaKerja) {
            $dataUpdate['Tahun' . $bulan] = $request->tahun;
          }
          if ($shouldUpdateGaji && !is_null($gajiToApply)) {
            $dataUpdate['Gaji' . $bulan] = $gajiToApply;
          }
        }
      }
      // Keterangan tetap berdasarkan TMT (berlaku untuk semua Tahun_Versi yang di-update)
      $dataUpdate['Keterangan'] = $keteranganToApply;
      $dataUpdate['TMT_JAD_Pertama'] = $tmtJadPertamaToApply;
      $dataUpdate['TMT_JAD_Akhir'] = $tmtJadAkhirToApply;
      $dataUpdate['TMT_Inpassing_Akhir'] = $tmtInpassingAkhirToApply;
      $dataUpdate['Inpassing'] = $inpassingToApply;

      DB::table('s_transaksi_2')
        ->where(function($q) use ($nidn) {
          $q->where('NIDN', $nidn)->orWhere('NUPTK', $nidn);
        })
        ->where('Tahun_Versi', $tahunVersi)
        ->update($dataUpdate);
    }

    try {
      // Aktif/Jenis/Eligible_span hanya untuk Tahun_Versi = session('tahun')
      $sessionOnlyUpdate = [
        'Aktif' => $request->Aktif,
        'Jenis' => $jenisToApply,
        'Eligible_span' => $eligibleToApply,
        'TMT_JAD_Pertama' => $tmtJadPertamaToApply,
        'TMT_JAD_Akhir' => $tmtJadAkhirToApply,
        'TMT_Inpassing_Akhir' => $tmtInpassingAkhirToApply,
        'Inpassing' => $inpassingToApply,
      ];

      // Keterangan hanya ikut di session-year jika session-year termasuk rentang TMT (>= tahun TMT)
      if ($sessionYear >= $tmtTahun) {
        $sessionOnlyUpdate['Keterangan'] = $keteranganToApply;
      }

      DB::table('s_transaksi_2')
        ->where(function($q) use ($nidn) {
          $q->where('NIDN', $nidn)->orWhere('NUPTK', $nidn);
        })
        ->where('Tahun_Versi', $sessionYear)
        ->update($sessionOnlyUpdate);
    } catch (\Exception $e) {
      // ignore: if session-year record missing, do not block main update; history still recorded
    }

    $dokumenPath = null;
    if ($request->hasFile('dokumen')) {
      $dokumen = $request->file('dokumen');
      $tanggalSekarang = date('Ymd');
      $originalName = pathinfo($dokumen->getClientOriginalName(), PATHINFO_FILENAME);
      $slugName = Str::slug($originalName, '_');
      $namaDokumenBaru = $tanggalSekarang . '_' . $slugName . '.' . $dokumen->getClientOriginalExtension();
      // Simpan file ke storage/app/public/Dokumen_Histori_Dosen2 dan layani via /storage/Dokumen_Histori_Dosen2/
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

      // Jika berasal dari alur tambah dosen, bersihkan draft setelah berhasil tersimpan.
      session()->forget('draft_add_dosen.' . $nidn);

      // Redirect flow based on Inpassing selection:
      // Tampilkan modal sukses dulu di halaman edit, lalu navigasi ke tujuan.
      Log::info('UbahMKGolController@ubahMkGol saved successfully', ['nidn' => $nidn, 'inpassing' => $inpassingToApply]);

      if (strcasecmp($inpassingToApply, 'Sesuai TMT Awal dan Akhir') === 0) {
        $nextUrl = route('admin.sinkronisasi', [
          'tab' => 'golmasa',
          'nidn' => $nidn,
          'allowGolmasa' => 1,
          'autofill' => 1,
        ]);
      } else {
        $nextUrl = route('data-dosen.show', ['nidn' => $nidn]);
      }

      return redirect()
        ->route('admin.edit-mk-gol', ['nidn' => $nidn])
        ->with('success', 'Data Berhasil Disimpan')
        ->with('next_url', $nextUrl);
    } catch (\Throwable $e) {
      $alias = ErrorAlias::fromThrowable($e, 'ADM-MKGOL');
      Log::error('UbahMKGolController@ubahMkGol exception', [
        'alias' => $alias['code'],
        'nidn' => $nidn,
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
      ]);

      return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data. (Kode: ' . $alias['code'] . ')');
    }
  }
}
