<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property string $kode_pts
 * @property string|null $nama_pts
 * @property string|null $nama_pimpinan
 * @property string|null $jabatan_pimpinan
 * @property string|null $alamat_pt
 * @property string|null $password
 * @property int|null $aktif
 * @property string|null $wilayah
 * @property string|null $dokumen
 * @property string $tanggal_update
 * @method static \Illuminate\Database\Eloquent\Builder|APts newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|APts newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|APts query()
 * @method static \Illuminate\Database\Eloquent\Builder|APts whereAktif($value)
 * @method static \Illuminate\Database\Eloquent\Builder|APts whereAlamatPt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|APts whereDokumen($value)
 * @method static \Illuminate\Database\Eloquent\Builder|APts whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|APts whereJabatanPimpinan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|APts whereKodePts($value)
 * @method static \Illuminate\Database\Eloquent\Builder|APts whereNamaPimpinan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|APts whereNamaPts($value)
 * @method static \Illuminate\Database\Eloquent\Builder|APts wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|APts whereTanggalUpdate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|APts whereWilayah($value)
 */
	class APts extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $kode_bank
 * @property string|null $nama_bank
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Bank newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Bank newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Bank query()
 * @method static \Illuminate\Database\Eloquent\Builder|Bank whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bank whereKodeBank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bank whereNamaBank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bank whereUpdatedAt($value)
 */
	class Bank extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $nidn
 * @property string|null $nik
 * @property string|null $nama
 * @property string|null $ttl
 * @property string|null $tanggal_lahir
 * @property string|null $usia
 * @property string|null $sertifikat_dosen
 * @property string|null $tahun_lulus
 * @property string|null $pts
 * @property string|null $kode_pt
 * @property string|null $jenis
 * @property string|null $sk_inpassing
 * @property string|null $pangkat
 * @property string|null $tmt_pangkat_golongan
 * @property string|null $tmt_jabatan_fungsional
 * @property string|null $jabatan
 * @property string|null $jabatan2
 * @property string|null $jabatan3
 * @property string|null $jabatan4
 * @property string|null $jabatan5
 * @property string|null $jabatan6
 * @property string|null $jabatan7
 * @property string|null $jabatan8
 * @property string|null $jabatan9
 * @property string|null $jabatan10
 * @property string|null $jabatan11
 * @property string|null $jabatan12
 * @property string|null $gol
 * @property string|null $gol2
 * @property string|null $gol3
 * @property string|null $gol4
 * @property string|null $gol5
 * @property string|null $gol6
 * @property string|null $gol7
 * @property string|null $gol8
 * @property string|null $gol9
 * @property string|null $gol10
 * @property string|null $gol11
 * @property string|null $gol12
 * @property string|null $tahun
 * @property string|null $tahun2
 * @property string|null $tahun3
 * @property string|null $tahun4
 * @property string|null $tahun5
 * @property string|null $tahun6
 * @property string|null $tahun7
 * @property string|null $tahun8
 * @property string|null $tahun9
 * @property string|null $tahun10
 * @property string|null $tahun11
 * @property string|null $tahun12
 * @property string|null $npwp
 * @property string|null $no_rekening
 * @property string|null $nama_rekening
 * @property string|null $nama_penerima
 * @property string|null $bank
 * @property string|null $gaji
 * @property string|null $gaji2
 * @property string|null $gaji3
 * @property string|null $gaji4
 * @property string|null $gaji5
 * @property string|null $gaji6
 * @property string|null $gaji7
 * @property string|null $gaji8
 * @property string|null $gaji9
 * @property string|null $gaji10
 * @property string|null $gaji11
 * @property string|null $gaji12
 * @property string|null $aktif
 * @property string|null $keterangan
 * @property string|null $eligible_span
 * @property string|null $tanggal_update_terakhir
 * @property string|null $pemegang_wilayah
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen query()
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereAktif($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereBank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereEligibleSpan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereGaji($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereGaji10($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereGaji11($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereGaji12($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereGaji2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereGaji3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereGaji4($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereGaji5($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereGaji6($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereGaji7($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereGaji8($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereGaji9($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereGol($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereGol10($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereGol11($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereGol12($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereGol2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereGol3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereGol4($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereGol5($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereGol6($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereGol7($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereGol8($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereGol9($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereJabatan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereJabatan10($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereJabatan11($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereJabatan12($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereJabatan2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereJabatan3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereJabatan4($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereJabatan5($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereJabatan6($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereJabatan7($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereJabatan8($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereJabatan9($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereJenis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereKeterangan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereKodePt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereNamaPenerima($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereNamaRekening($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereNidn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereNik($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereNoRekening($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereNpwp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen wherePangkat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen wherePemegangWilayah($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen wherePts($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereSertifikatDosen($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereSkInpassing($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereTahun($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereTahun10($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereTahun11($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereTahun12($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereTahun2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereTahun3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereTahun4($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereTahun5($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereTahun6($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereTahun7($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereTahun8($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereTahun9($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereTahunLulus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereTanggalLahir($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereTanggalUpdateTerakhir($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereTmtJabatanFungsional($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereTmtPangkatGolongan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereTtl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dosen whereUsia($value)
 */
	class Dosen extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $kode
 * @property string|null $gol
 * @property int|null $masa_kerja
 * @property int|null $nominal
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Grade newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Grade newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Grade query()
 * @method static \Illuminate\Database\Eloquent\Builder|Grade whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Grade whereGol($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Grade whereKode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Grade whereMasaKerja($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Grade whereNominal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Grade whereUpdatedAt($value)
 */
	class Grade extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $no
 * @property string|null $nidn
 * @property string|null $nama
 * @property string|null $pts
 * @property string|null $kode_pt
 * @property string|null $pemegang_wilayah
 * @property string|null $aktif
 * @property string|null $keterangan
 * @property string|null $pengguna
 * @property string|null $tanggal_update_terakhir
 * @property string|null $no_dokumen_ubah
 * @property string|null $tgl_dokumen_ubah
 * @property string|null $alasan_perubahan
 * @property string|null $dokumen
 * @property string|null $tanggal_update_terbaru
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|HistoriDosen newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HistoriDosen newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HistoriDosen query()
 * @method static \Illuminate\Database\Eloquent\Builder|HistoriDosen whereAktif($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistoriDosen whereAlasanPerubahan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistoriDosen whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistoriDosen whereDokumen($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistoriDosen whereKeterangan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistoriDosen whereKodePt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistoriDosen whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistoriDosen whereNidn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistoriDosen whereNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistoriDosen whereNoDokumenUbah($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistoriDosen wherePemegangWilayah($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistoriDosen wherePengguna($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistoriDosen wherePts($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistoriDosen whereTanggalUpdateTerakhir($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistoriDosen whereTanggalUpdateTerbaru($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistoriDosen whereTglDokumenUbah($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistoriDosen whereUpdatedAt($value)
 */
	class HistoriDosen extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $kode
 * @property string|null $jabatan
 * @property string|null $nominal
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Jabatan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Jabatan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Jabatan query()
 * @method static \Illuminate\Database\Eloquent\Builder|Jabatan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Jabatan whereJabatan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Jabatan whereKode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Jabatan whereNominal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Jabatan whereUpdatedAt($value)
 */
	class Jabatan extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $kode
 * @property string|null $aktif
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Keaktifan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Keaktifan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Keaktifan query()
 * @method static \Illuminate\Database\Eloquent\Builder|Keaktifan whereAktif($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Keaktifan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Keaktifan whereKode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Keaktifan whereUpdatedAt($value)
 */
	class Keaktifan extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $kode_pts
 * @property string|null $nama_pts
 * @property string|null $nama_pimpinan
 * @property string|null $jabatan_pimpinan
 * @property string|null $alamat_pt
 * @property string|null $password
 * @property int|null $aktif
 * @property string|null $wilayah
 * @property string|null $dokumen
 * @property \Illuminate\Support\Carbon $tanggal_update
 * @method static \Illuminate\Database\Eloquent\Builder|PTS newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PTS newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PTS query()
 * @method static \Illuminate\Database\Eloquent\Builder|PTS whereAktif($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PTS whereAlamatPt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PTS whereDokumen($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PTS whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PTS whereJabatanPimpinan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PTS whereKodePts($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PTS whereNamaPimpinan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PTS whereNamaPts($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PTS wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PTS whereTanggalUpdate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PTS whereWilayah($value)
 */
	class PTS extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $no
 * @property string|null $status
 * @property string|null $akumulasi
 * @property float|null $tarif_pajak
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Pajak newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Pajak newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Pajak query()
 * @method static \Illuminate\Database\Eloquent\Builder|Pajak whereAkumulasi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pajak whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pajak whereNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pajak whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pajak whereTarifPajak($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pajak whereUpdatedAt($value)
 */
	class Pajak extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $kode
 * @property string|null $jenis
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Pegawai newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Pegawai newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Pegawai query()
 * @method static \Illuminate\Database\Eloquent\Builder|Pegawai whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pegawai whereJenis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pegawai whereKode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pegawai whereUpdatedAt($value)
 */
	class Pegawai extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string|null $tahun
 * @property string|null $bulan
 * @property int|null $pencairan_ke
 * @property string|null $tanggal_mulai
 * @property string|null $tanggal_selesai
 * @property string|null $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PengaturanUsulan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PengaturanUsulan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PengaturanUsulan query()
 * @method static \Illuminate\Database\Eloquent\Builder|PengaturanUsulan whereBulan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PengaturanUsulan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PengaturanUsulan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PengaturanUsulan wherePencairanKe($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PengaturanUsulan whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PengaturanUsulan whereTahun($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PengaturanUsulan whereTanggalMulai($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PengaturanUsulan whereTanggalSelesai($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PengaturanUsulan whereUpdatedAt($value)
 */
	class PengaturanUsulan extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $kode
 * @property string $status_perubahan
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Perubahan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Perubahan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Perubahan query()
 * @method static \Illuminate\Database\Eloquent\Builder|Perubahan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Perubahan whereKode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Perubahan whereStatusPerubahan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Perubahan whereUpdatedAt($value)
 */
	class Perubahan extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $tahun
 * @property string|null $periode
 * @property string|null $bulan
 * @property string|null $dokumen
 * @property string $tanggal
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Sisternas newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Sisternas newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Sisternas query()
 * @method static \Illuminate\Database\Eloquent\Builder|Sisternas whereBulan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sisternas whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sisternas whereDokumen($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sisternas whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sisternas wherePeriode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sisternas whereTahun($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sisternas whereTanggal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sisternas whereUpdatedAt($value)
 */
	class Sisternas extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $email
 * @property string $password
 * @property string $role
 * @property int $active
 * @property string|null $cp
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $status
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 */
	class User extends \Eloquent {}
}

