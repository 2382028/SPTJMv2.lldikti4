<?php

namespace App\Http\Controllers;

use App\Helpers\ErrorAlias;
use App\Models\HistoriDosen;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class PerubahanDataDosenPtsController extends Controller
{
    private function norm($v): string
    {
        return trim((string) $v);
    }

    private function dateDmyOrYmdRule(): \Closure
    {
        return function ($attribute, $value, $fail) {
            $s = trim((string) $value);
            if ($s === '') return;
            try {
                if (strpos($s, '/') !== false) {
                    Carbon::createFromFormat('d/m/Y', $s);
                } else {
                    Carbon::createFromFormat('Y-m-d', $s);
                }
            } catch (\Throwable $e) {
                $alias = ErrorAlias::fromThrowable($e, 'PTS-DATE-RULE');
                Log::error(__METHOD__ . ' date rule validation failed', [
                    'alias' => $alias['code'],
                    'attribute' => $attribute,
                    'value' => $value,
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                $fail("Format $attribute tidak valid.");
            }
        };
    }

    private function normDateToYmd(?string $value): ?string
    {
        $s = trim((string) $value);
        if ($s === '') return null;
        try {
            if (strpos($s, '/') !== false) {
                return Carbon::createFromFormat('d/m/Y', $s)->format('Y-m-d');
            }
            return Carbon::parse($s)->format('Y-m-d');
        } catch (\Throwable $e) {
            $alias = ErrorAlias::fromThrowable($e, 'PTS-NORM-DATE');
            Log::error(__METHOD__ . ' normDateToYmd failed', [
                'alias' => $alias['code'],
                'value' => $value,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return $s;
        }
    }

    private function currentPts()
    {
        return Auth::guard('pts')->user();
    }

    public function show(Request $request, $nidn)
    {
        $pts = $this->currentPts();
        if (!$pts) abort(403);

        $mode = (string) $request->query('mode', 'edit');

        $bulanSession = (int) session('bulan') ?: 12;
        $bulanSession = max(1, min(12, $bulanSession));

        $masa_kerja = "NULLIF(Tahun{$bulanSession}, '')";
        $golongan   = "NULLIF(Gol{$bulanSession}, '')";
        $jabatan    = "NULLIF(Jabatan{$bulanSession}, '')";
        $gaji       = "NULLIF(Gaji{$bulanSession}, 0)";

        $data_dosen = Transaksi::where(function ($q) use ($nidn) {
                $q->where('NIDN', $nidn)
                    ->orWhere('NUPTK', $nidn);
            })
            ->where('Tahun_Versi', session('tahun'))
            ->where('Kode_PT', $pts->kode_pts)
            ->select(
                '*',
                DB::raw("$masa_kerja AS masa_kerja"),
                DB::raw("$golongan AS gol"),
                DB::raw("$jabatan AS jabatan"),
                DB::raw("$gaji AS gaji")
            )
            ->first();

        if (!$data_dosen) {
            return redirect()->back()->with('error', 'Data dosen tidak ditemukan!');
        }

        // List data untuk dropdown di UI (pakai sumber yang sama seperti admin agar layout & fungsi tetap sama)
        $pics = collect();

        $bankList = DB::table('b_bank')
            ->select('nama_bank')
            ->whereNotNull('nama_bank')
            ->where('nama_bank', '!=', '')
            ->distinct()
            ->orderBy('nama_bank')
            ->pluck('nama_bank');

        $golonganList = DB::table('c_grade')
            ->select('gol')
            ->whereNotNull('gol')
            ->where('gol', '!=', '')
            ->distinct()
            ->orderBy('gol')
            ->pluck('gol');

        $jenisList = DB::table('g_pegawai')
            ->select('jenis')
            ->whereNotNull('jenis')
            ->where('jenis', '!=', '')
            ->distinct()
            ->orderBy('jenis')
            ->pluck('jenis');

        $ptsList = DB::table('a_pts')
            ->select('kode_pts', 'nama_pts')
            ->whereNotNull('kode_pts')
            ->where('kode_pts', '!=', '')
            ->whereNotNull('nama_pts')
            ->where('nama_pts', '!=', '')
            ->orderBy('nama_pts')
            ->get();

        $statusPerubahan = DB::table('h_perubahan')
            ->orderBy('kode')
            ->pluck('status_perubahan')
            ->filter()
            ->values();

        $jabatanList = DB::table('e_jabatan')
            ->select('jabatan')
            ->whereNotNull('jabatan')
            ->where('jabatan', '!=', '')
            ->orderBy('jabatan')
            ->pluck('jabatan');

        // Use a dedicated PTS view (separated from admin) to keep permissions and UX isolated.
        return view('pts.perubahan-data-dosen', [
            'dosen' => $data_dosen,
            'mode' => $mode,
            'pics' => $pics,
            'bankList' => $bankList,
            'golonganList' => $golonganList,
            'jenisList' => $jenisList,
            'ptsList' => $ptsList,
            'statusPerubahan' => $statusPerubahan,
            'jabatanList' => $jabatanList,
        ]);
    }

    public function storePengaktifan(Request $request, $nidn)
    {
        return $this->storeToComplain($request, $nidn, 'pengaktifan');
    }

    public function storePerubahan(Request $request, $nidn)
    {
        return $this->storeToComplain($request, $nidn, 'perubahan');
    }

    private function storeToComplain(Request $request, $identifier, string $tab)
    {
        $pts = $this->currentPts();
        if (!$pts) abort(403);

        // PTS: these identity/institution fields must never be updated from this form.
        // Even if a malicious client submits them, ignore them completely.
        foreach (['kode_pt', 'pts', 'ttl', 'nik', 'NIK'] as $key) {
            try {
                if ($request->has($key)) {
                    $request->request->remove($key);
                }
            } catch (\Throwable $ignored) {
                // ignore
            }
        }

        $dateRule = $this->dateDmyOrYmdRule();

        // Validasi minimal: meniru field kunci pada halaman perubahan-data-dosen.
        $request->validate([
            'no_dokumen_ubah' => 'required|string',
            'tgl_dokumen_ubah' => ['required', $dateRule],
            'alasan_perubahan' => 'required|string',
            'dokumen' => 'required|file|mimes:pdf|max:10240',
            'keterangan' => 'required|string|max:100',
            'Aktif' => 'nullable|in:0,1',
            'tanggal_update_terakhir' => ['sometimes', 'nullable', $dateRule],
        ]);

        $tahun = (int) session('tahun');
        $dosen = Transaksi::where(function ($q) use ($identifier) {
                $q->where('NIDN', $identifier)
                    ->orWhere('NUPTK', $identifier);
            })
            ->where('Tahun_Versi', $tahun)
            ->where('Kode_PT', $pts->kode_pts)
            ->first();

        if (!$dosen) {
            return redirect()->back()->with('error', 'Data dosen tidak ditemukan!');
        }

        $bulanSession = (int) (session('bulan') ?: 12);
        $bulanSession = max(1, min(12, $bulanSession));

        // Conditional validation: if Jabatan is changed, TMT Jabatan is mandatory
        $currentJabatan = (string) ($dosen->{"Jabatan{$bulanSession}"} ?? '');
        $incomingJabatan = $request->has('jabatan') ? (string) $request->input('jabatan') : '';
        $jabatanChanged = ($incomingJabatan !== '' && $this->norm($incomingJabatan) !== $this->norm($currentJabatan));
        if ($jabatanChanged && !$request->filled('tmt_jabatan')) {
            throw ValidationException::withMessages([
                'tmt_jabatan' => 'TMT Jabatan wajib diisi jika Jabatan diubah.',
            ]);
        }

        // If checkbox Update Gol is checked, TMT Inpassing Akhir must be filled
        $applyGolChecked = $request->input('apply_gol_by_tmt_inpassing') === '1';
        if ($applyGolChecked && !$request->filled('tmt_inpassing_akhir')) {
            throw ValidationException::withMessages([
                'tmt_inpassing_akhir' => 'TMT Inpassing Akhir wajib diisi jika Update Gol dicentang.',
            ]);
        }

        // Block saving when there are no meaningful changes in Detail Data Dosen
        $currentGol = (string) ($dosen->{"Gol{$bulanSession}"} ?? '');
        $currentMk = (string) ($dosen->{"Tahun{$bulanSession}"} ?? '');
        $currentGaji = (string) ($dosen->{"Gaji{$bulanSession}"} ?? '');

        $hasAnyChange = false;

        // Base (non-monthly) columns
        $baseMap = [
            'nama' => 'Nama',
            'jenis' => 'Jenis',
            'sertifikat_dosen' => 'Sertifikat_Dosen',
            'tahun_lulus' => 'Tahun_Lulus',
            'tmt_jad_pertama' => 'TMT_JAD_Pertama',
            'tmt_jad_akhir' => 'TMT_JAD_Akhir',
            'inpassing' => 'Inpassing',
            'tmt_inpassing_akhir' => 'TMT_Inpassing_Akhir',
            'no_rekening' => 'No_Rekening',
            'bank' => 'Bank',
            'nama_rekening' => 'Nama_Rekening',
            'nama_penerima' => 'Nama_Penerima',
            'npwp' => 'NPWP',
            'eligible_span' => 'Eligible_span',
            'pemegang_wilayah' => 'Pemegang_Wilayah',
            'aktif' => 'Aktif',
        ];
        $dateKeys = ['tmt_jad_pertama', 'tmt_jad_akhir', 'tmt_inpassing_akhir'];
        foreach ($baseMap as $reqKey => $dbCol) {
            if (!$request->has($reqKey) && $reqKey !== 'aktif') continue;
            $incoming = $request->has($reqKey) ? $request->input($reqKey) : null;
            if ($reqKey === 'aktif') {
                // input name is 'Aktif' in the form
                $incoming = $request->input('Aktif', null);
            }
            $currentVal = $dosen->{$dbCol} ?? null;
            $incomingNorm = in_array($reqKey, $dateKeys, true) ? $this->normDateToYmd((string) $incoming) : $this->norm($incoming);
            $currentNorm = in_array($reqKey, $dateKeys, true) ? $this->normDateToYmd((string) $currentVal) : $this->norm($currentVal);
            if ($incomingNorm !== $currentNorm) {
                $hasAnyChange = true;
                break;
            }
        }

        // Monthly values (compare against session month)
        if (!$hasAnyChange) {
            $incomingGol = $request->has('gol') ? (string) $request->input('gol') : '';
            $incomingMk = $request->has('tahun') ? (string) $request->input('tahun') : '';
            $incomingGaji = $request->has('gaji') ? (string) $request->input('gaji') : '';
            if ($this->norm($incomingJabatan) !== $this->norm($currentJabatan)) $hasAnyChange = true;
            elseif ($incomingGol !== '' && $this->norm($incomingGol) !== $this->norm($currentGol)) $hasAnyChange = true;
            elseif ($incomingMk !== '' && $this->norm($incomingMk) !== $this->norm($currentMk)) $hasAnyChange = true;
            elseif ($incomingGaji !== '' && $this->norm($incomingGaji) !== $this->norm($currentGaji)) $hasAnyChange = true;
        }

        // Update Gol propagation is a meaningful change request by itself
        if (!$hasAnyChange && $applyGolChecked) {
            $hasAnyChange = true;
        }

        // Jabatan propagation requires TMT Jabatan, which is validated above when Jabatan changes
        if (!$hasAnyChange) {
            throw ValidationException::withMessages([
                'perubahan' => 'Detail Data Dosen tidak ada perubahan. Tidak dapat melakukan simpan.',
            ]);
        }

        // Upload dokumen (ikut pola HistoriDosen)
        $dokumenPath = null;
        $dokumenFilename = null;
        if ($request->hasFile('dokumen')) {
            $dokumen = $request->file('dokumen');
            $tanggalSekarang = date('Ymd');
            $originalName = pathinfo($dokumen->getClientOriginalName(), PATHINFO_FILENAME);
            $slugName = Str::slug($originalName, '_');
            $dokumenFilename = $tanggalSekarang . '_' . $slugName . '.' . $dokumen->getClientOriginalExtension();
            Storage::putFileAs('public/Dokumen_Histori_Dosen2', $dokumen, $dokumenFilename);
            $dokumenPath = 'Dokumen_Histori_Dosen2/' . $dokumenFilename;
        }

        $pengguna = 'PTS:' . ($pts->kode_pts ?? '-');

        $payload = [
            'jenis_pengajuan' => 'perubahan_data_dosen',
            'tab' => $tab,
            'tahun_versi' => $tahun,
            'bulan_versi' => (int) (session('bulan') ?: 12),
            'nidn' => $dosen->NIDN ?? null,
            'nuptk' => $dosen->NUPTK ?? null,
            'pengguna' => $pengguna,
            'tanggal_update_terakhir' => $this->normDateToYmd($request->input('tanggal_update_terakhir')),
            'no_dokumen_ubah' => (string) $request->input('no_dokumen_ubah'),
            'tgl_dokumen_ubah' => $this->normDateToYmd($request->input('tgl_dokumen_ubah')),
            'alasan_perubahan' => (string) $request->input('alasan_perubahan'),
            'keterangan' => (string) $request->input('keterangan'),
            'dokumen' => $dokumenFilename,
            // Always record checkbox state so it is visible in pesan
            'apply_gol_by_tmt_inpassing' => $applyGolChecked ? '1' : '0',
        ];

        // Editable inputs (only include when actually submitted)
        if ($request->has('tmt_jad_pertama')) $payload['tmt_jad_pertama'] = $this->normDateToYmd($request->input('tmt_jad_pertama'));
        if ($request->has('tmt_jad_akhir')) $payload['tmt_jad_akhir'] = $this->normDateToYmd($request->input('tmt_jad_akhir'));
        if ($request->has('inpassing')) $payload['inpassing'] = (string) $request->input('inpassing');
        if ($request->has('tmt_inpassing_akhir')) $payload['tmt_inpassing_akhir'] = $this->normDateToYmd($request->input('tmt_inpassing_akhir'));
        if ($request->has('jabatan')) $payload['jabatan'] = $this->norm((string) $request->input('jabatan'));
        if ($request->has('gol')) $payload['gol'] = $this->norm((string) $request->input('gol'));
        if ($request->has('tmt_jabatan')) $payload['tmt_jabatan'] = $this->normDateToYmd($request->input('tmt_jabatan'));

        // pengaktifan-only context (not shown for PTS, but harmless if present)
        if ($request->has('tmt_keaktifan')) $payload['tmt_keaktifan'] = $this->normDateToYmd($request->input('tmt_keaktifan'));

        // rekening & pajak
        if ($request->has('no_rekening')) $payload['no_rekening'] = (string) $request->input('no_rekening');
        if ($request->has('bank')) $payload['bank'] = (string) $request->input('bank');
        if ($request->has('nama_rekening')) $payload['nama_rekening'] = (string) $request->input('nama_rekening');
        if ($request->has('nama_penerima')) $payload['nama_penerima'] = (string) $request->input('nama_penerima');
        if ($request->has('npwp')) $payload['npwp'] = (string) $request->input('npwp');

        try {
            DB::table('i_complain')->insert([
                'pelapor_tipe' => 'pts',
                'pts_id' => $pts->id,
                'dosen_id' => null,
                'kode_pts' => $pts->kode_pts ?? null,
                'nidn' => $payload['nidn'],
                'nuptk' => $payload['nuptk'],
                'judul' => 'Pengajuan Perubahan Data Dosen',
                'pesan' => json_encode($payload, JSON_UNESCAPED_UNICODE),
                'lampiran' => $dokumenPath,
                'jenis_pengajuan' => 'perubahan_data_dosen',
                'status' => 'open',
                'admin_balasan' => null,
                'handled_by' => null,
                'handled_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // rollback file if insert fails
            if (!empty($dokumenFilename)) {
                try {
                    Storage::delete('public/Dokumen_Histori_Dosen2/' . $dokumenFilename);
                } catch (\Throwable $ignored) {
                    $alias = ErrorAlias::fromThrowable($ignored, 'PTS-PENGAJUAN-STORAGE');
                    Log::error(__METHOD__ . ' failed to delete uploaded file after insert error', [
                        'alias' => $alias['code'],
                        'file' => $dokumenFilename,
                        'message' => $ignored->getMessage(),
                        'trace' => $ignored->getTraceAsString(),
                    ]);
                }
            }

            $alias = ErrorAlias::fromThrowable($e, 'PTS-PENGAJUAN');
            Log::error('PerubahanDataDosenPtsController@storeToComplain failed', [
                'alias' => $alias['code'],
                'nidn' => $identifier,
                'tab' => $tab,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()->with('error', $alias['message']);
        }

        return redirect()->back()->with('success', 'Pengajuan perubahan berhasil dikirim dan menunggu persetujuan admin.');
    }
}

