@extends(
    \Illuminate\Support\Facades\Auth::guard('pts')->check()
        ? 'layouts/contentNavbarLayoutPts'
        : ((auth()->check() && method_exists(auth()->user(), 'isPIC') && auth()->user()->isPIC())
            ? 'layouts/contentNavbarLayoutPic'
            : 'layouts/contentNavbarLayout')
)

@section('title', 'SPTJM Online')

@section('content')

@php
    // For display-only fields: replace empty / '-' with a friendly label.
    $__displayOrNA = function ($v) {
        $s = trim((string) $v);
        return ($s === '' || $s === '-') ? 'Data Tidak Tersedia' : $s;
    };

    // Format date values for HTML input[type=date] (Y-m-d) accepting d/m/Y or other parseable strings
    $__formatForInput = function($v) {
        if (empty($v)) return '';
        try {
            if (strpos($v, '/') !== false) {
                $d = \Carbon\Carbon::createFromFormat('d/m/Y', $v);
            } else {
                $d = \Carbon\Carbon::parse($v);
            }
            return $d->format('Y-m-d');
        } catch (\Exception $e) {
            return '';
        }
    };
@endphp

<div class="card">
    <h5 class="card-header">Detail Data Dosen</h5>
    <div class="table-responsive text-nowrap">

        <div class="card-body">
            @php
                $__tglLahir = trim((string)($dosen->Tanggal_Lahir ?? ''));
                $__ttl = trim((string)($dosen->TTL ?? ''));
                $__ttlDisplay = ($__tglLahir === '' || $__tglLahir === '-' || $__ttl === '' || $__ttl === '-')
                    ? 'Data Tidak Tersedia'
                    : ($__tglLahir . ' - ' . $__ttl);

                $__kodePts = trim((string)($dosen->Kode_PT ?? ''));
                $__namaPts = trim((string)($dosen->PTS ?? ''));
                $__kodePtsDisplay = ($__kodePts === '' || $__kodePts === '-')
                    ? 'Data Tidak Tersedia'
                    : ($__kodePts . ' - ' . ($__namaPts !== '' && $__namaPts !== '-' ? $__namaPts : '-'));

                $__rawAktif = $dosen->aktif ?? ($dosen->Aktif ?? null);
                $__aktifStr = strtolower(trim((string)$__rawAktif));
                if ($__aktifStr === '') {
                    $__statusDisplay = 'Data Tidak Tersedia';
                } elseif ($__aktifStr === 'aktif') {
                    $__statusDisplay = 'AKTIF';
                } elseif ($__aktifStr === 'tidak aktif' || $__aktifStr === 'nonaktif' || $__aktifStr === 'tidak') {
                    $__statusDisplay = 'TIDAK AKTIF';
                } else {
                    $__statusDisplay = ((int)$__rawAktif) === 1 ? 'AKTIF' : 'TIDAK AKTIF';
                }

                $__eligibleRaw = strtoupper(trim((string)($dosen->Eligible_span ?? '')));
                if (in_array($__eligibleRaw, ['YA','Y','1','TRUE'], true)) {
                    $__eligibleDisplay = 'YA';
                } elseif (in_array($__eligibleRaw, ['TIDAK','TDK','N','0','FALSE','NO'], true)) {
                    $__eligibleDisplay = 'TIDAK';
                } else {
                    $__eligibleDisplay = ($__eligibleRaw === '' || $__eligibleRaw === '-') ? 'Data Tidak Tersedia' : $__eligibleRaw;
                }
            @endphp

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">NIDN</label>
                    <input type="text" class="form-control" value="{{ $__displayOrNA($dosen->NIDN ?? null) }}" readonly style="background-color: #eceef1;">
                </div>
                <div class="col-md-6">
                    <label class="form-label">NUPTK</label>
                    <input type="text" class="form-control" value="{{ $__displayOrNA($dosen->NUPTK ?? null) }}" readonly style="background-color: #eceef1;">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">NIK</label>
                    <input type="text" class="form-control" value="{{ $__displayOrNA($dosen->NIK ?? ($dosen->nik ?? null)) }}" readonly style="background-color: #eceef1;">
                </div>
                <div class="col-md-6"></div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Nama</label>
                    <input type="text" class="form-control" value="{{ $__displayOrNA($dosen->Nama ?? null) }}" readonly style="background-color: #eceef1;">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Jenis</label>
                    <input type="text" class="form-control" value="{{ $__displayOrNA($dosen->Jenis ?? null) }}" readonly style="background-color: #eceef1;">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Sertifikat Dosen</label>
                    <input type="text" class="form-control" value="{{ $__displayOrNA($dosen->Sertifikat_Dosen ?? null) }}" readonly style="background-color: #eceef1;">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tahun Lulus</label>
                    <input type="text" class="form-control" value="{{ $__displayOrNA($dosen->Tahun_Lulus ?? null) }}" readonly style="background-color: #eceef1;">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">TTL</label>
                    <input type="text" class="form-control" value="{{ $__ttlDisplay }}" readonly style="background-color: #eceef1;">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Usia</label>
                    <input type="text" class="form-control" value="{{ $__displayOrNA($dosen->Usia ?? null) }}" readonly style="background-color: #eceef1;">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Kode PTS / PTS</label>
                    <input type="text" class="form-control" value="{{ $__kodePtsDisplay }}" readonly style="background-color: #eceef1;">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Status</label>
                    <input type="text" class="form-control" value="{{ $__statusDisplay }}" readonly style="background-color: #eceef1;">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">TMT JAD Pertama</label>
                    <input type="date" class="form-control" value="{{ call_user_func($__formatForInput, $dosen->TMT_JAD_Pertama ?? null) }}" disabled style="background-color: #eceef1;">
                </div>
                <div class="col-md-6">
                    <label class="form-label">TMT JAD Akhir</label>
                    <input type="date" class="form-control" value="{{ call_user_func($__formatForInput, $dosen->TMT_JAD_Akhir ?? null) }}" disabled style="background-color: #eceef1;">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">TMT Inpassing Akhir</label>
                    <input type="date" class="form-control" value="{{ call_user_func($__formatForInput, $dosen->TMT_Inpassing_Akhir ?? null) }}" disabled style="background-color: #eceef1;">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Inpassing</label>
                    <input type="text" class="form-control" value="{{ $__displayOrNA($dosen->Inpassing ?? null) }}" readonly style="background-color: #eceef1;">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Jabatan</label>
                    <input type="text" class="form-control" value="{{ $__displayOrNA($dosen->jabatan ?? ($dosen->Jabatan ?? null)) }}" readonly style="background-color: #eceef1;">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Golongan</label>
                    <input type="text" class="form-control" value="{{ $__displayOrNA($dosen->gol ?? ($dosen->Gol ?? null)) }}" readonly style="background-color: #eceef1;">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Masa Kerja</label>
                    <input type="text" class="form-control" value="{{ $__displayOrNA($dosen->masa_kerja ?? null) }}" readonly style="background-color: #eceef1;">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Gaji</label>
                    <input type="text" class="form-control" value="{{ $__displayOrNA($dosen->gaji ?? null) }}" readonly style="background-color: #eceef1;">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Rekening</label>
                    <input type="text" class="form-control" value="{{ $__displayOrNA($dosen->No_Rekening ?? null) }}" readonly style="background-color: #eceef1;">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Bank</label>
                    <input type="text" class="form-control" value="{{ $__displayOrNA($dosen->Bank ?? null) }}" readonly style="background-color: #eceef1;">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Nama Rekening</label>
                    <input type="text" class="form-control" value="{{ $__displayOrNA($dosen->Nama_Rekening ?? null) }}" readonly style="background-color: #eceef1;">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nama Supplier</label>
                    <input type="text" class="form-control" value="{{ $__displayOrNA($dosen->Nama_Penerima ?? null) }}" readonly style="background-color: #eceef1;">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">NPWP</label>
                    <input type="text" class="form-control" value="{{ $__displayOrNA($dosen->NPWP ?? null) }}" readonly style="background-color: #eceef1;">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Eligible Span</label>
                    <input type="text" class="form-control" value="{{ $__eligibleDisplay }}" readonly style="background-color: #eceef1;">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Pemegang Wilayah</label>
                    <input type="text" class="form-control" value="{{ $__displayOrNA($dosen->Pemegang_Wilayah ?? null) }}" readonly style="background-color: #eceef1;">
                </div>
            </div>

            <div class="demo-inline-spacing">
                <div class="d-flex justify-content-center">
                    <a href="{{ url()->previous() }}" class="btn btn-secondary mx-2" onclick="event.preventDefault(); if (history.length > 1) { history.back(); } else { window.location.href = this.href; }">Batal</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection