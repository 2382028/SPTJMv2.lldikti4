<?php

namespace App\Http\Controllers;
use App\Helpers\ErrorAlias;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use App\Helpers\ActiveYears;

class TambahVersiController extends Controller
{
    public function index(Request $request)
    {
        $tahunAcuan = session('tahun');
        $limit = (int) $request->query('limit', 10);
        if ($limit < 1 || $limit > 200) { $limit = 10; }
        $search = trim((string) $request->query('search', ''));

        $query = DB::table('s_transaksi_2')
            ->select('Tahun_Versi', DB::raw('COUNT(*) as jumlah'));
        if ($search !== '') {
            $query->where('Tahun_Versi', 'like', "%$search%");
        }
        $versis = $query->groupBy('Tahun_Versi')
            ->orderByDesc('Tahun_Versi')
            ->paginate($limit)
            ->appends($request->query());

    $activeYears = ActiveYears::load();
        return view('admin.tambah-versi', compact('tahunAcuan', 'versis', 'search', 'limit', 'activeYears'));
    }

    public function store(Request $request)
    {
        set_time_limit(0);
        ini_set('max_execution_time', '0');
        $request->validate([
            'tahun_acuan' => 'required|digits:4',
            'tahun_target' => 'required|digits:4|different:tahun_acuan',
        ], [
            'tahun_target.different' => 'Tahun target tidak boleh sama dengan tahun acuan.',
        ]);

        $tahunAcuan = $request->input('tahun_acuan');
        $tahunTarget = $request->input('tahun_target');
        $table = 's_transaksi_2';

        try {
            DB::beginTransaction();

            $exists = DB::table($table)->where('Tahun_Versi', $tahunTarget)->exists();
            if ($exists) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Data untuk Tahun ' . $tahunTarget . ' sudah ada. Proses dibatalkan.');
            }

            $columns = Schema::getColumnListing($table);
            $hasId = in_array('id', $columns, true);

            $pkRows = DB::select("SHOW KEYS FROM {$table} WHERE Key_name = 'PRIMARY'");
            $primaryColumns = [];
            foreach ($pkRows as $pk) {
                if (!empty($pk->Column_name)) {
                    $primaryColumns[] = $pk->Column_name;
                } elseif (!empty($pk->ColumnName)) { 
                    $primaryColumns[] = $pk->ColumnName;
                }
            }

            $copyColumns = array_values(array_diff($columns, array_filter(array_merge([
                $hasId ? 'id' : null,
                'Tahun_Versi',
            ], $primaryColumns))));

            $nullifyColumns = [
                // Flag bulan
                'Jan','Feb','Mar','Apr','May','Jun','Jul','Ags','Sep','Okt','Nov','Des',
                // Kode cair / referensi TPD & TKGB per bulan
                'TPD1','TKGB1','TPD2','TKGB2','TPD3','TKGB3','TPD4','TKGB4','TPD5','TKGB5','TPD6','TKGB6',
                'TPD7','TKGB7','TPD8','TKGB8','TPD9','TKGB9','TPD10','TKGB10','TPD11','TKGB11','TPD12','TKGB12',
                // Pajak persentase
                'pajakTPD1','pajakTKGB1','pajakTPD2','pajakTKGB2','pajakTPD3','pajakTKGB3','pajakTPD4','pajakTKGB4',
                'pajakTPD5','pajakTKGB5','pajakTPD6','pajakTKGB6','pajakTPD7','pajakTKGB7','pajakTPD8','pajakTKGB8',
                'pajakTPD9','pajakTKGB9','pajakTPD10','pajakTKGB10','pajakTPD11','pajakTKGB11','pajakTPD12','pajakTKGB12',
                // Nilai pajak
                'nilaiPajakTPD1','nilaiPajakTKGB1','nilaiPajakTPD2','nilaiPajakTKGB2','nilaiPajakTPD3','nilaiPajakTKGB3',
                'nilaiPajakTPD4','nilaiPajakTKGB4','nilaiPajakTPD5','nilaiPajakTKGB5','nilaiPajakTPD6','nilaiPajakTKGB6',
                'nilaiPajakTPD7','nilaiPajakTKGB7','nilaiPajakTPD8','nilaiPajakTKGB8','nilaiPajakTPD9','nilaiPajakTKGB9',
                'nilaiPajakTPD10','nilaiPajakTKGB10','nilaiPajakTPD11','nilaiPajakTKGB11','nilaiPajakTPD12','nilaiPajakTKGB12',
                // Nilai bersih
                'bersihTPD1','bersihTKGB1','bersihTPD2','bersihTKGB2','bersihTPD3','bersihTKGB3','bersihTPD4','bersihTKGB4',
                'bersihTPD5','bersihTKGB5','bersihTPD6','bersihTKGB6','bersihTPD7','bersihTKGB7','bersihTPD8','bersihTKGB8',
                'bersihTPD9','bersihTKGB9','bersihTPD10','bersihTKGB10','bersihTPD11','bersihTKGB11','bersihTPD12','bersihTKGB12',
                // SP2D nomor & tanggal
                'No_sp2d_1','No_sp2d_2','No_sp2d_3','No_sp2d_4','No_sp2d_5','No_sp2d_6','No_sp2d_7','No_sp2d_8','No_sp2d_9','No_sp2d_10','No_sp2d_11','No_sp2d_12',
                'Tgl_sp2d_1','Tgl_sp2d_2','Tgl_sp2d_3','Tgl_sp2d_4','Tgl_sp2d_5','Tgl_sp2d_6','Tgl_sp2d_7','Tgl_sp2d_8','Tgl_sp2d_9','Tgl_sp2d_10','Tgl_sp2d_11','Tgl_sp2d_12',
                // Selisih agregat
                'JmlTPD_Selisih','JmlTKGB_Selisih','Pajak_TPD_Selisih','Pajak_TKGB_Selisih','Bersih_TPD_Selisih','Bersih_TKGB_Selisih',
                // Dokumen SPM & tanggal cair per jenis
                'No_SPM_TPD','No_SPM_TKGB','TglTPD','TglTKGB',
                // Pengguna
                'Pengguna',
            ];

            $toNull = array_values(array_intersect($nullifyColumns, $columns));

            $allowedStatuses = DB::table('h_perubahan')->pluck('status_perubahan')->filter()->values()->all();
            $allowedStatusSet = [];
            foreach ($allowedStatuses as $st) { $allowedStatusSet[$st] = true; }
            $kodeUsulanColumns = [];
            for ($i = 1; $i <= 12; $i++) {
                $col = 'KodeUsulan' . $i;
                if (in_array($col, $columns, true)) { $kodeUsulanColumns[] = $col; }
            }

            $totalInserted = 0;

            DB::table($table)
                ->where('Tahun_Versi', $tahunAcuan)
                ->orderBy($copyColumns[0] ?? 'Tahun_Versi')
                ->chunk(1000, function ($rows) use ($table, $copyColumns, $tahunTarget, $toNull, $kodeUsulanColumns, $allowedStatusSet, $columns, &$totalInserted) {
                    $columnsPerRow = max(1, count($copyColumns) + count($toNull) + count($kodeUsulanColumns) + 1); // +1 for Tahun_Versi
                    $maxPlaceholders = 65000; // aman di bawah batas 65535 MySQL
                    $maxRowsPerInsert = max(1, intdiv($maxPlaceholders, $columnsPerRow));

                    $pendingBatch = [];

                        foreach ($rows as $row) {
                        $data = [];
                        foreach ($copyColumns as $col) {
                            $data[$col] = $row->{$col} ?? null;
                        }
                        foreach ($toNull as $ncol) {
                            $data[$ncol] = null;
                        }
                            // Override Gol1..Gol12 with the source Gol12 value (if present)
                            $gol12val = null;
                            if (isset($row->Gol12)) { $gol12val = $row->Gol12; }
                            elseif (isset($row->gol12)) { $gol12val = $row->gol12; }
                            // Override Tahun1..Tahun12 with the source Tahun12 value (if present)
                            $tahun12val = null;
                            if (isset($row->Tahun12)) { $tahun12val = $row->Tahun12; }
                            elseif (isset($row->tahun12)) { $tahun12val = $row->tahun12; }
                        $lastVal = null;
                        for ($i = 12; $i >= 1; $i--) {
                            $col = 'KodeUsulan' . $i;
                            if (!in_array($col, $kodeUsulanColumns, true)) { continue; }
                            $v = $row->{$col} ?? null;
                            if ($v !== null && $v !== '') { $lastVal = $v; break; }
                        }
                        $fillVal = ($lastVal !== null && isset($allowedStatusSet[$lastVal])) ? $lastVal : null;
                        foreach ($kodeUsulanColumns as $kuCol) { $data[$kuCol] = $fillVal; }
                        // apply Gol12 -> Gol1..Gol12 and Tahun12 -> Tahun1..Tahun12 if columns exist
                        for ($g = 1; $g <= 12; $g++) {
                            $gcol = 'Gol' . $g;
                            if (in_array($gcol, $columns, true)) { $data[$gcol] = $gol12val; }
                            $tcol = 'Tahun' . $g;
                            if (in_array($tcol, $columns, true)) { $data[$tcol] = $tahun12val; }
                        }

                        // increment 'Usia' by 1 if column exists
                        if (in_array('Usia', $columns, true)) {
                            $rawUsia = $row->Usia ?? $row->usia ?? null;
                            $num = is_numeric($rawUsia) ? (int)$rawUsia : 0;
                            $data['Usia'] = $num + 1;
                        }

                        $data['Tahun_Versi'] = $tahunTarget;

                        $pendingBatch[] = $data;

                        if (count($pendingBatch) >= $maxRowsPerInsert) {
                            DB::table($table)->insert($pendingBatch);
                            $totalInserted += count($pendingBatch);
                            $pendingBatch = [];
                        }
                    }

                    if (!empty($pendingBatch)) {
                        DB::table($table)->insert($pendingBatch);
                        $totalInserted += count($pendingBatch);
                    }
                });

            DB::commit();

            $msg = 'Proses berhasil! Tahun versi ' . $tahunTarget . ' dibuat.';
            if ($totalInserted > 0) {
                $msg .= ' ' . $totalInserted . ' baris disalin dari tahun ' . $tahunAcuan . '.';
            } else {
                $msg .= ' Tidak ada data pada tahun acuan ' . $tahunAcuan . ' untuk disalin.';
            }

            return redirect()->route('admin.tambah-versi')->with('success', $msg);
        } catch (\Throwable $e) {
            DB::rollBack();
            $alias = ErrorAlias::fromThrowable($e, 'ADM-VERSI');
            Log::error('TambahVersiController: gagal tambah versi (copy s_transaksi_2)', [
                'alias' => $alias['code'],
                'tahunTarget' => $tahunTarget ?? null,
                'tahunAcuan' => $tahunAcuan ?? null,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()->with('error', 'Proses gagal. (Kode: ' . $alias['code'] . ')');
        }
    }

    public function toggle(Request $request, int $id)
    {
        return response()->json(['success' => false, 'message' => 'Fitur toggle status tidak tersedia.']);
    }

    public function toggleStatus(Request $request)
    {
        $request->validate([
            'tahun' => 'required|digits:4',
        ]);
        $year = (int)$request->input('tahun');
        $years = ActiveYears::toggle($year);
        $isActive = in_array($year, $years, true);
        return response()->json([
            'success' => true,
            'tahun' => $year,
            'active' => $isActive,
            'activeYears' => $years,
        ]);
    }
}
 
