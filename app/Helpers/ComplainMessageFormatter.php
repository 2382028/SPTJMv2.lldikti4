<?php

namespace App\Helpers;

use App\Models\Transaksi;
use Illuminate\Support\Facades\DB;

class ComplainMessageFormatter
{
    private static function esc($v): string
    {
        $s = trim((string) $v);
        if ($s === '') return '-';
        return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private static function norm($v): string
    {
        return trim((string) $v);
    }

    private static function normDate($v): string
    {
        $s = trim((string) $v);
        if ($s === '') return '';
        try {
            if (strpos($s, '/') !== false) {
                return \Carbon\Carbon::createFromFormat('d/m/Y', $s)->format('Y-m-d');
            }
            return \Carbon\Carbon::parse($s)->format('Y-m-d');
        } catch (\Throwable $e) {
            return $s;
        }
    }

    /**
     * Formats i_complain.pesan into readable HTML for perubahan_data_dosen.
     * Returns null when not applicable.
     */
    public static function formatPerubahanDataDosenHtml(object $complainRow): ?string
    {
        $raw = (string) ($complainRow->pesan ?? '');
        $payload = json_decode($raw, true);
        if (!is_array($payload)) return null;
        if (($payload['jenis_pengajuan'] ?? null) !== 'perubahan_data_dosen') return null;

        $identifier = trim((string) ($payload['nidn'] ?? ($complainRow->nidn ?? '')));
        if ($identifier === '') {
            $identifier = trim((string) ($payload['nuptk'] ?? ($complainRow->nuptk ?? '')));
        }

        $tahunVersi = (int) ($payload['tahun_versi'] ?? 0);
        $bulanVersi = (int) ($payload['bulan_versi'] ?? 12);
        if ($bulanVersi < 1 || $bulanVersi > 12) $bulanVersi = 12;

        $current = null;
        if ($identifier !== '' && $tahunVersi > 0) {
            $current = Transaksi::where(function ($q) use ($identifier) {
                    $q->where('NIDN', $identifier)->orWhere('NUPTK', $identifier);
                })
                ->where('Tahun_Versi', $tahunVersi)
                ->first();
        }

        $changes = [];

        $addChange = function (string $label, $from, $to, bool $isDate = false) use (&$changes) {
            $fromNorm = $isDate ? self::normDate($from) : self::norm($from);
            $toNorm = $isDate ? self::normDate($to) : self::norm($to);
            if ($fromNorm === $toNorm) return;
            $changes[] = [
                'label' => $label,
                'from' => $from,
                'to' => $to,
                'isDate' => $isDate,
            ];
        };

        if ($current) {
            // Basic (card) fields
            if (array_key_exists('nama', $payload)) $addChange('Nama', $current->Nama ?? null, $payload['nama'], false);
            if (array_key_exists('jenis', $payload)) $addChange('Jenis', $current->Jenis ?? null, $payload['jenis'], false);
            if (array_key_exists('sertifikat_dosen', $payload)) $addChange('Sertifikat Dosen', $current->Sertifikat_Dosen ?? null, $payload['sertifikat_dosen'], false);
            if (array_key_exists('tahun_lulus', $payload)) $addChange('Tahun Lulus', $current->Tahun_Lulus ?? null, $payload['tahun_lulus'], false);
            if (array_key_exists('kode_pt', $payload)) $addChange('Kode PTS', $current->Kode_PT ?? null, $payload['kode_pt'], false);
            if (array_key_exists('pts', $payload)) $addChange('PTS', $current->PTS ?? null, $payload['pts'], false);
            if (array_key_exists('aktif', $payload)) $addChange('Status', $current->Aktif ?? null, $payload['aktif'], false);

            if (array_key_exists('tmt_jad_pertama', $payload)) $addChange('TMT JAD Pertama', $current->TMT_JAD_Pertama ?? null, $payload['tmt_jad_pertama'], true);
            if (array_key_exists('tmt_jad_akhir', $payload)) $addChange('TMT JAD Akhir', $current->TMT_JAD_Akhir ?? null, $payload['tmt_jad_akhir'], true);
            if (array_key_exists('tmt_inpassing_akhir', $payload)) $addChange('TMT Inpassing Akhir', $current->TMT_Inpassing_Akhir ?? null, $payload['tmt_inpassing_akhir'], true);
            if (array_key_exists('inpassing', $payload)) $addChange('Inpassing', $current->Inpassing ?? null, $payload['inpassing'], false);

            // Monthly fields (compare against selected month)
            $jabCol = 'Jabatan' . $bulanVersi;
            $golCol = 'Gol' . $bulanVersi;
            $mkCol = 'Tahun' . $bulanVersi;
            $gajiCol = 'Gaji' . $bulanVersi;
            if (array_key_exists('jabatan', $payload)) $addChange('Jabatan', $current->{$jabCol} ?? null, $payload['jabatan'], false);
            if (array_key_exists('gol', $payload)) $addChange('Golongan', $current->{$golCol} ?? null, $payload['gol'], false);
            if (array_key_exists('tahun', $payload)) $addChange('Masa Kerja', $current->{$mkCol} ?? null, $payload['tahun'], false);
            if (array_key_exists('gaji', $payload)) $addChange('Gaji', $current->{$gajiCol} ?? null, $payload['gaji'], false);

            // Bank & pajak fields
            if (array_key_exists('no_rekening', $payload)) $addChange('Rekening', $current->No_Rekening ?? null, $payload['no_rekening'], false);
            if (array_key_exists('bank', $payload)) $addChange('Bank', $current->Bank ?? null, $payload['bank'], false);
            if (array_key_exists('nama_rekening', $payload)) $addChange('Nama Rekening', $current->Nama_Rekening ?? null, $payload['nama_rekening'], false);
            if (array_key_exists('nama_penerima', $payload)) $addChange('Nama Supplier', $current->Nama_Penerima ?? null, $payload['nama_penerima'], false);
            if (array_key_exists('npwp', $payload)) $addChange('NPWP', $current->NPWP ?? null, $payload['npwp'], false);
            if (array_key_exists('eligible_span', $payload)) $addChange('Eligible Span', $current->Eligible_span ?? null, $payload['eligible_span'], false);
            if (array_key_exists('pemegang_wilayah', $payload)) $addChange('Pemegang Wilayah', $current->Pemegang_Wilayah ?? null, $payload['pemegang_wilayah'], false);
        }

        // Build HTML
        $info = [];
        $info[] = '<div><strong>Jenis Pengajuan:</strong> Perubahan Data Dosen</div>';
        if ($identifier !== '') $info[] = '<div><strong>Identifier:</strong> ' . self::esc($identifier) . '</div>';
        if (!empty($payload['tahun_versi'])) $info[] = '<div><strong>Tahun Versi:</strong> ' . self::esc($payload['tahun_versi']) . '</div>';
        if (!empty($payload['bulan_versi'])) $info[] = '<div><strong>Bulan Versi:</strong> ' . self::esc($payload['bulan_versi']) . '</div>';
        if (!empty($payload['no_dokumen_ubah'])) $info[] = '<div><strong>No Dokumen:</strong> ' . self::esc($payload['no_dokumen_ubah']) . '</div>';
        if (!empty($payload['tgl_dokumen_ubah'])) $info[] = '<div><strong>Tgl Dokumen:</strong> ' . self::esc($payload['tgl_dokumen_ubah']) . '</div>';
        if (!empty($payload['alasan_perubahan'])) $info[] = '<div><strong>Alasan:</strong> ' . self::esc($payload['alasan_perubahan']) . '</div>';

        // Context for monthly propagation rules
        $applyGol = (string) ($payload['apply_gol_by_tmt_inpassing'] ?? '');
        if ($applyGol !== '') {
            $info[] = '<div><strong>Update Gol (berdasarkan TMT Inpassing):</strong> ' . self::esc($applyGol === '1' ? 'YA' : 'TIDAK') . '</div>';
        }
        if (!empty($payload['tmt_inpassing_akhir'])) {
            $info[] = '<div><strong>TMT Inpassing Akhir:</strong> ' . self::esc($payload['tmt_inpassing_akhir']) . '</div>';
        }
        if (!empty($payload['tmt_jabatan'])) {
            $info[] = '<div><strong>TMT Jabatan:</strong> ' . self::esc($payload['tmt_jabatan']) . '</div>';
        }

        $html = '<div class="p-2">' . implode('', $info) . '</div>';

        $html .= '<hr />';
        $html .= '<div class="p-2"><strong>Perubahan Data (hanya yang berubah):</strong></div>';

        if (!$current) {
            $html .= '<div class="p-2 text-muted">Tidak bisa menghitung perubahan karena data dosen di s_transaksi_2 tidak ditemukan untuk tahun versi pengajuan.</div>';
            return $html;
        }

        if (empty($changes)) {
            $html .= '<div class="p-2 text-muted">Tidak ada perbedaan nilai yang terdeteksi.</div>';
            return $html;
        }

        $html .= '<div class="table-responsive">';
        $html .= '<table class="table table-sm table-bordered">';
        $html .= '<thead style="background-color:#f1f1f1;"><tr><th style="width:30%">Field</th><th>Dari</th><th>Menjadi</th></tr></thead><tbody>';
        foreach ($changes as $c) {
            $from = $c['from'];
            $to = $c['to'];
            if ($c['isDate']) {
                $from = self::normDate($from);
                $to = self::normDate($to);
            }
            $html .= '<tr>'
                . '<td>' . self::esc($c['label']) . '</td>'
                . '<td>' . self::esc($from) . '</td>'
                . '<td>' . self::esc($to) . '</td>'
                . '</tr>';
        }
        $html .= '</tbody></table></div>';

        return $html;
    }
}
