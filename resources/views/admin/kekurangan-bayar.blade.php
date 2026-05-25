@extends('layouts/contentNavbarLayout')

@section('title', 'SPTJM Online')

@section('content')

@php
    $formatInt = function ($value) {
        $num = (float) ($value ?? 0);
        return number_format((int) round($num), 0, ',', '.');
    };

    $diffBgClass = function ($dbValue, $aktualValue) {
        $db = (int) round((float) ($dbValue ?? 0));
        $akt = (int) round((float) ($aktualValue ?? 0));
        $d = $db - $akt;
        if ($d === 0) return '';
        return $d > 0 ? 'table-success' : 'table-danger';
    };

    // Panggil variabel yang dikirim dari controller (Gak butuh dipisah manual lagi)
    $kurangRows = $detailKurang ?? []; 
    $lebihRows  = $detailLebih ?? []; 
    
    $rekapKurangRows = collect($rekapKurang ?? []);
    $rekapLebihRows = collect($rekapLebih ?? []);
    $allRekap = $rekapKurangRows->merge($rekapLebihRows)->sortByDesc('created_at');

    $flashInfo = $flashInfo ?? null;

    $rekapAssetUrl = function ($path) {
        $p = trim((string) ($path ?? ''));
        if ($p === '') return null;
        $p = ltrim(str_replace('\\', '/', $p), '/');
        if (strpos($p, 'storage/') === 0) return asset($p);
        return asset('storage/' . $p);
    };
@endphp

<div class="content-wrapper">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <h5 class="card-header">Monitoring Kurang/Lebih Bayar - Tahun {{ $versi }}</h5>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.kekurangan-bayar.proses') }}" id="formProsesKekurangan">
                        @csrf
                        <div class="row mb-0">
                            <div class="col-lg-2 col-md-4 mb-2">
                                <label class="form-label">Pilih Periode</label>
                                <select class="form-select" name="periode">
                                    <option value="1" selected>Januari - Desember</option>
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-4 mb-2">
                                <label class="form-label">Pilih Tipe</label>
                                <select class="form-select" name="tipe">
                                    <option value="Semua" selected>Semua</option>
                                    <option value="TPD">TPD</option>
                                    <option value="TKGB">TKGB</option>
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-4 mb-2">
                                <label class="form-label">Pilih Jenis</label>
                                <select class="form-select" name="jenis" id="selectJenis">
                                    <option value="Semua" selected>Semua</option>
                                    <option value="PNS">PNS</option>
                                    <option value="NON PNS">NON PNS</option>
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-4 mb-2">
                                <label class="form-label">Pilih Bank</label>
                                <select class="form-select" name="bank" id="selectBank">
                                    <option value="Semua" selected>Semua</option>
                                    @foreach(($bankList ?? []) as $bank)
                                    <option value="{{ $bank }}">{{ $bank }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-3 col-md-4 mb-2 d-flex align-items-end">
                                <button type="button" class="btn btn-outline-primary btn-sm me-2 px-2" id="btnCekDataKekurangan" style="min-width:130px;">
                                    <span class="tf-icons bx bx-search"></span>&nbsp; Cek Data
                                </button>
                                <button type="button" class="btn btn-warning btn-sm px-2" id="btnProsesKekurangan" style="min-width:130px;">
                                    <span class="tf-icons bx bx-loader"></span>&nbsp; Proses
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Navigasi Tabs --}}
            <ul class="nav nav-tabs mb-3" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-kurang-btn" data-bs-toggle="tab" data-bs-target="#tab-data-kurang" type="button" role="tab" aria-selected="true">
                        Data Kurang Bayar
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-lebih-btn" data-bs-toggle="tab" data-bs-target="#tab-data-lebih" type="button" role="tab" aria-selected="false">
                        Data Lebih Bayar
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-rekap-btn" data-bs-toggle="tab" data-bs-target="#tab-rekap" type="button" role="tab" aria-selected="false">
                        Rekap
                    </button>
                </li>
            </ul>

            <div class="tab-content px-0 py-0">
                
                {{-- TAB 1: DATA KURANG BAYAR --}}
                <div class="tab-pane fade show active" id="tab-data-kurang" role="tabpanel" tabindex="0">
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Data Kurang Bayar</h5>
                            <form action="{{ route('admin.kekurangan-bayar.destroy-kurang') }}" method="POST" id="formDestroyKurang" class="m-0">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <span class="tf-icons bx bx-trash"></span>&nbsp; Hapus Kurang Bayar
                                </button>
                            </form>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive text-nowrap">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th rowspan="3" class="text-center align-middle">No</th>
                                            <th rowspan="3" class="text-center align-middle">NIDN</th>
                                            <th rowspan="3" class="text-center align-middle">Nama</th>
                                            <th rowspan="3" class="text-center align-middle">Jenis</th>
                                            <th rowspan="3" class="text-center align-middle">Jabatan</th>
                                            <th rowspan="3" class="text-center align-middle">Status</th>
                                            <th colspan="48" class="text-center">Januari - Desember</th>
                                            <th colspan="11" class="text-center">Jumlah Kotor, Nilai Pajak, dan Bersih</th>
                                            <th rowspan="3" class="text-center align-middle">Aksi</th>
                                        </tr>
                                        <tr>
                                            <th colspan="2" class="text-center">Jan</th><th colspan="2" class="text-center">Jan (Aktual)</th>
                                            <th colspan="2" class="text-center">Feb</th><th colspan="2" class="text-center">Feb (Aktual)</th>
                                            <th colspan="2" class="text-center">Mar</th><th colspan="2" class="text-center">Mar (Aktual)</th>
                                            <th colspan="2" class="text-center">Apr</th><th colspan="2" class="text-center">Apr (Aktual)</th>
                                            <th colspan="2" class="text-center">Mei</th><th colspan="2" class="text-center">Mei (Aktual)</th>
                                            <th colspan="2" class="text-center">Jun</th><th colspan="2" class="text-center">Jun (Aktual)</th>
                                            <th colspan="2" class="text-center">Jul</th><th colspan="2" class="text-center">Jul (Aktual)</th>
                                            <th colspan="2" class="text-center">Ags</th><th colspan="2" class="text-center">Ags (Aktual)</th>
                                            <th colspan="2" class="text-center">Sep</th><th colspan="2" class="text-center">Sep (Aktual)</th>
                                            <th colspan="2" class="text-center">Okt</th><th colspan="2" class="text-center">Okt (Aktual)</th>
                                            <th colspan="2" class="text-center">Nov</th><th colspan="2" class="text-center">Nov (Aktual)</th>
                                            <th colspan="2" class="text-center">Des</th><th colspan="2" class="text-center">Des (Aktual)</th>
                                            <th colspan="2" class="text-center">Jumlah Kotor</th>
                                            <th colspan="2" class="text-center">Nilai Pajak</th>
                                            <th rowspan="2" class="text-center align-middle">Bersih</th>
                                            <th colspan="2" class="text-center">Jumlah Kotor (Aktual)</th>
                                            <th colspan="2" class="text-center">Nilai Pajak (Aktual)</th>
                                            <th rowspan="2" class="text-center align-middle">Bersih (Aktual)</th>
                                            <th rowspan="2" class="text-center align-middle">Kesimpulan</th>
                                        </tr>
                                        <tr>
                                            @for ($i = 1; $i <= 12; $i++)
                                            <th class="text-center">TPD</th><th class="text-center">TKGB</th>
                                            <th class="text-center">TPD</th><th class="text-center">TKGB</th>
                                            @endfor
                                            <th class="text-center">TPD</th><th class="text-center">TKGB</th>
                                            <th class="text-center">TPD</th><th class="text-center">TKGB</th>
                                            <th class="text-center">TPD</th><th class="text-center">TKGB</th>
                                            <th class="text-center">TPD</th><th class="text-center">TKGB</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($kurangRows as $row)
                                        @php
                                        $status = $row->Aktif == 1 ? 'Aktif' : 'Tidak Aktif';
                                        $kesimpulan = ((float) ($row->bersih ?? 0)) - ((float) ($row->bersih_akt ?? 0));
                                        $clsKesimpulan = $kesimpulan == 0.0 ? '' : ($kesimpulan > 0 ? 'table-success' : 'table-danger');
                                        @endphp
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $row->NIDN }}</td>
                                            <td>{{ $row->Nama }}</td>
                                            <td>{{ $row->Jenis }}</td>
                                            <td>{{ $row->Jabatan12 }}</td>
                                            <td>{{ $status }}</td>
                                                @for ($i = 1; $i <= 12; $i++)
                                                @php
                                                    $dbTpd = $row->{'db_tpd'.$i} ?? 0;
                                                    $dbTkgb = $row->{'db_tkgb'.$i} ?? 0;
                                                    $aktTpd = $row->{'exp_tpd'.$i} ?? 0;
                                                    $aktTkgb = $row->{'exp_tkgb'.$i} ?? 0;
                                                    $clsTpd = $diffBgClass($dbTpd, $aktTpd);
                                                    $clsTkgb = $diffBgClass($dbTkgb, $aktTkgb);
                                                @endphp
                                                <td class="{{ $clsTpd }}">{{ $formatInt($dbTpd) }}</td>
                                                <td class="{{ $clsTkgb }}">{{ $formatInt($dbTkgb) }}</td>
                                                <td>{{ $formatInt($aktTpd) }}</td>
                                                <td>{{ $formatInt($aktTkgb) }}</td>
                                                @endfor
                                                @php
                                                    $clsSumTpd = $diffBgClass($row->jml_tpd ?? 0, $row->jml_tpd_akt ?? 0);
                                                    $clsSumTkgb = $diffBgClass($row->jml_tkgb ?? 0, $row->jml_tkgb_akt ?? 0);
                                                    $clsPjkTpd = $diffBgClass($row->nilai_pjk_tpd ?? 0, $row->nilai_pjk_tpd_akt ?? 0);
                                                    $clsPjkTkgb = $diffBgClass($row->nilai_pjk_tkgb ?? 0, $row->nilai_pjk_tkgb_akt ?? 0);
                                                    $clsBersih = $diffBgClass($row->bersih ?? 0, $row->bersih_akt ?? 0);
                                                @endphp
                                                <td class="{{ $clsSumTpd }}">{{ $formatInt($row->jml_tpd ?? 0) }}</td>
                                                <td class="{{ $clsSumTkgb }}">{{ $formatInt($row->jml_tkgb ?? 0) }}</td>
                                                <td class="{{ $clsPjkTpd }}">{{ $formatInt($row->nilai_pjk_tpd ?? 0) }}</td>
                                                <td class="{{ $clsPjkTkgb }}">{{ $formatInt($row->nilai_pjk_tkgb ?? 0) }}</td>
                                                <td class="{{ $clsBersih }}">{{ $formatInt($row->bersih ?? 0) }}</td>
                                                <td>{{ $formatInt($row->jml_tpd_akt ?? 0) }}</td>
                                                <td>{{ $formatInt($row->jml_tkgb_akt ?? 0) }}</td>
                                                <td>{{ $formatInt($row->nilai_pjk_tpd_akt ?? 0) }}</td>
                                                <td>{{ $formatInt($row->nilai_pjk_tkgb_akt ?? 0) }}</td>
                                                <td>{{ $formatInt($row->bersih_akt ?? 0) }}</td>
                                                <td class="{{ $clsKesimpulan }}">{{ $formatInt($kesimpulan) }}</td>
                                                <td class="text-center align-middle">
                                                    @if($kesimpulan != 0)
                                                    <button type="button" class="btn btn-sm btn-primary btn-aksi-sp2d-individu py-0 px-2"
                                                        data-nidn="{{ $row->NIDN }}" data-nama="{{ $row->Nama }}" data-jenis="kurang" title="Input SP2D NIDN: {{ $row->NIDN }}" style="font-size:11px;">
                                                        <i class="bx bx-edit-alt"></i> SP2D
                                                    </button>
                                                    @else
                                                    -
                                                    @endif
                                                </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="65" class="text-center">Tidak ada data kurang bayar ditemukan</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            
                            @if($kurangRows instanceof \Illuminate\Pagination\LengthAwarePaginator)
                            <div class="mt-4 d-flex justify-content-end">
                                {{ $kurangRows->appends(request()->query())->links('pagination::bootstrap-5') }}
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- TAB 2: DATA LEBIH BAYAR --}}
                <div class="tab-pane fade" id="tab-data-lebih" role="tabpanel" tabindex="0">
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Data Lebih Bayar</h5>
                            <form action="{{ route('admin.kekurangan-bayar.destroy-lebih') }}" method="POST" id="formDestroyLebih" class="m-0">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <span class="tf-icons bx bx-trash"></span>&nbsp; Hapus Lebih Bayar
                                </button>
                            </form>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive text-nowrap">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th rowspan="3" class="text-center align-middle">No</th>
                                            <th rowspan="3" class="text-center align-middle">NIDN</th>
                                            <th rowspan="3" class="text-center align-middle">Nama</th>
                                            <th rowspan="3" class="text-center align-middle">Jenis</th>
                                            <th rowspan="3" class="text-center align-middle">Jabatan</th>
                                            <th rowspan="3" class="text-center align-middle">Status</th>
                                            <th colspan="48" class="text-center">Januari - Desember</th>
                                            <th colspan="11" class="text-center">Jumlah Kotor, Nilai Pajak, dan Bersih</th>
                                            <th rowspan="3" class="text-center align-middle">Aksi</th>
                                        </tr>
                                        <tr>
                                            <th colspan="2" class="text-center">Jan</th><th colspan="2" class="text-center">Jan (Aktual)</th>
                                            <th colspan="2" class="text-center">Feb</th><th colspan="2" class="text-center">Feb (Aktual)</th>
                                            <th colspan="2" class="text-center">Mar</th><th colspan="2" class="text-center">Mar (Aktual)</th>
                                            <th colspan="2" class="text-center">Apr</th><th colspan="2" class="text-center">Apr (Aktual)</th>
                                            <th colspan="2" class="text-center">Mei</th><th colspan="2" class="text-center">Mei (Aktual)</th>
                                            <th colspan="2" class="text-center">Jun</th><th colspan="2" class="text-center">Jun (Aktual)</th>
                                            <th colspan="2" class="text-center">Jul</th><th colspan="2" class="text-center">Jul (Aktual)</th>
                                            <th colspan="2" class="text-center">Ags</th><th colspan="2" class="text-center">Ags (Aktual)</th>
                                            <th colspan="2" class="text-center">Sep</th><th colspan="2" class="text-center">Sep (Aktual)</th>
                                            <th colspan="2" class="text-center">Okt</th><th colspan="2" class="text-center">Okt (Aktual)</th>
                                            <th colspan="2" class="text-center">Nov</th><th colspan="2" class="text-center">Nov (Aktual)</th>
                                            <th colspan="2" class="text-center">Des</th><th colspan="2" class="text-center">Des (Aktual)</th>
                                            <th colspan="2" class="text-center">Jumlah Kotor</th>
                                            <th colspan="2" class="text-center">Nilai Pajak</th>
                                            <th rowspan="2" class="text-center align-middle">Bersih</th>
                                            <th colspan="2" class="text-center">Jumlah Kotor (Aktual)</th>
                                            <th colspan="2" class="text-center">Nilai Pajak (Aktual)</th>
                                            <th rowspan="2" class="text-center align-middle">Bersih (Aktual)</th>
                                            <th rowspan="2" class="text-center align-middle">Kesimpulan</th>
                                        </tr>
                                        <tr>
                                            @for ($i = 1; $i <= 12; $i++)
                                            <th class="text-center">TPD</th><th class="text-center">TKGB</th>
                                            <th class="text-center">TPD</th><th class="text-center">TKGB</th>
                                            @endfor
                                            <th class="text-center">TPD</th><th class="text-center">TKGB</th>
                                            <th class="text-center">TPD</th><th class="text-center">TKGB</th>
                                            <th class="text-center">TPD</th><th class="text-center">TKGB</th>
                                            <th class="text-center">TPD</th><th class="text-center">TKGB</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($lebihRows as $row)
                                        @php
                                        $status = $row->Aktif == 1 ? 'Aktif' : 'Tidak Aktif';
                                        $kesimpulan = ((float) ($row->bersih ?? 0)) - ((float) ($row->bersih_akt ?? 0));
                                        $clsKesimpulan = $kesimpulan == 0.0 ? '' : ($kesimpulan > 0 ? 'table-success' : 'table-danger');
                                        @endphp
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $row->NIDN }}</td>
                                            <td>{{ $row->Nama }}</td>
                                            <td>{{ $row->Jenis }}</td>
                                            <td>{{ $row->Jabatan12 }}</td>
                                            <td>{{ $status }}</td>
                                                @for ($i = 1; $i <= 12; $i++)
                                                @php
                                                    $dbTpd = $row->{'db_tpd'.$i} ?? 0;
                                                    $dbTkgb = $row->{'db_tkgb'.$i} ?? 0;
                                                    $aktTpd = $row->{'exp_tpd'.$i} ?? 0;
                                                    $aktTkgb = $row->{'exp_tkgb'.$i} ?? 0;
                                                    $clsTpd = $diffBgClass($dbTpd, $aktTpd);
                                                    $clsTkgb = $diffBgClass($dbTkgb, $aktTkgb);
                                                @endphp
                                                <td class="{{ $clsTpd }}">{{ $formatInt($dbTpd) }}</td>
                                                <td class="{{ $clsTkgb }}">{{ $formatInt($dbTkgb) }}</td>
                                                <td>{{ $formatInt($aktTpd) }}</td>
                                                <td>{{ $formatInt($aktTkgb) }}</td>
                                                @endfor
                                                @php
                                                    $clsSumTpd = $diffBgClass($row->jml_tpd ?? 0, $row->jml_tpd_akt ?? 0);
                                                    $clsSumTkgb = $diffBgClass($row->jml_tkgb ?? 0, $row->jml_tkgb_akt ?? 0);
                                                    $clsPjkTpd = $diffBgClass($row->nilai_pjk_tpd ?? 0, $row->nilai_pjk_tpd_akt ?? 0);
                                                    $clsPjkTkgb = $diffBgClass($row->nilai_pjk_tkgb ?? 0, $row->nilai_pjk_tkgb_akt ?? 0);
                                                    $clsBersih = $diffBgClass($row->bersih ?? 0, $row->bersih_akt ?? 0);
                                                @endphp
                                                <td class="{{ $clsSumTpd }}">{{ $formatInt($row->jml_tpd ?? 0) }}</td>
                                                <td class="{{ $clsSumTkgb }}">{{ $formatInt($row->jml_tkgb ?? 0) }}</td>
                                                <td class="{{ $clsPjkTpd }}">{{ $formatInt($row->nilai_pjk_tpd ?? 0) }}</td>
                                                <td class="{{ $clsPjkTkgb }}">{{ $formatInt($row->nilai_pjk_tkgb ?? 0) }}</td>
                                                <td class="{{ $clsBersih }}">{{ $formatInt($row->bersih ?? 0) }}</td>
                                                <td>{{ $formatInt($row->jml_tpd_akt ?? 0) }}</td>
                                                <td>{{ $formatInt($row->jml_tkgb_akt ?? 0) }}</td>
                                                <td>{{ $formatInt($row->nilai_pjk_tpd_akt ?? 0) }}</td>
                                                <td>{{ $formatInt($row->nilai_pjk_tkgb_akt ?? 0) }}</td>
                                                <td>{{ $formatInt($row->bersih_akt ?? 0) }}</td>
                                                <td class="{{ $clsKesimpulan }}">{{ $formatInt($kesimpulan) }}</td>
                                                <td class="text-center align-middle">
                                                    @if($kesimpulan != 0)
                                                    <button type="button" class="btn btn-sm btn-primary btn-aksi-sp2d-individu py-0 px-2"
                                                        data-nidn="{{ $row->NIDN }}" data-nama="{{ $row->Nama }}" data-jenis="lebih" title="Input NTPN NIDN: {{ $row->NIDN }}" style="font-size:11px;">
                                                        <i class="bx bx-edit-alt"></i> NTPN
                                                    </button>
                                                    @else
                                                    -
                                                    @endif
                                                </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="65" class="text-center">Tidak ada data lebih bayar ditemukan</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            
                            @if($lebihRows instanceof \Illuminate\Pagination\LengthAwarePaginator)
        <div class="mt-4 d-flex justify-content-end">
            {{ $lebihRows->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>
    @endif
                        </div>
                    </div>
                </div>

                {{-- TAB 3: REKAP GABUNGAN --}}
                <div class="tab-pane fade" id="tab-rekap" role="tabpanel" tabindex="0">
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Rekap Kurang & Lebih Bayar</h5>
                            <a href="{{ route('admin.kekurangan-bayar.rekap') }}" class="btn btn-sm btn-dark" title="Hapus Rekap">
                                <span class="tf-icons bx bx-trash"></span>&nbsp; Kelola / Hapus Rekap
                            </a>
                        </div>
                        <div class="card-body pt-2">
                            <style>
                                .rekap-tbl { font-size: 12px; line-height: 1.35; }
                                .rekap-tbl th, .rekap-tbl td { padding: 4px 8px !important; vertical-align: middle; }
                                .rekap-tbl th { font-size: 11px; text-transform: uppercase; letter-spacing: 0.3px; }
                                .badge-kurang { background-color: #d74d4dff; color: #fff; font-size: 10.5px; padding: 3px 8px; border-radius: 4px; }
                                .badge-lebih { background-color: #34cc4bff; color: #fff; font-size: 10.5px; padding: 3px 8px; border-radius: 4px; }
                            </style>
                            <div class="table-responsive text-nowrap">
                                <table class="table table-bordered table-sm rekap-tbl mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-center">No</th>
                                            <th class="text-center">Jenis</th>
                                            <th class="text-center">Periode</th>
                                            <th class="text-center">Pegawai</th>
                                            <th class="text-center">Tipe</th>
                                            <th class="text-center">Bank</th>
                                            <th class="text-center">Total Pembayaran</th>
                                            <th class="text-center">Tanggal Rekap</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($allRekap as $rekap)
                                        @php
                                            $periodeText = strtolower(trim($rekap->periode ?? ''));
                                            $isKurangRekap = (strpos($periodeText, 'kurang') !== false);
                                            $isLebihRekap = (strpos($periodeText, 'lebih') !== false);
                                            $sudahSp2d = !empty($rekap->sp2d);
                                        @endphp
                                        <tr>
                                            <td class="text-center">{{ $loop->iteration }}</td>
                                            <td class="text-center">
                                                @if($isKurangRekap)
                                                    <span class="badge-kurang">Kurang</span>
                                                @elseif($isLebihRekap)
                                                    <span class="badge-lebih">Lebih</span>
                                                @else
                                                    <span class="badge bg-secondary" style="font-size:10.5px;">-</span>
                                                @endif
                                            </td>
                                            <td>{{ $rekap->periode }}</td>
                                            <td class="text-center">{{ $rekap->pegawai }}</td>
                                            <td class="text-center">{{ $rekap->tipe }}</td>
                                            <td>{{ $rekap->bank }}</td>
                                            <td class="text-end fw-semibold">Rp {{ number_format((float) ($rekap->total_nominal ?? 0), 0, ',', '.') }}</td>
                                            <td class="text-center">{{ \Carbon\Carbon::parse($rekap->created_at)->format('d-M-Y H:i') }}</td>
                                            <td class="text-center">
                                                <div class="d-flex gap-1 justify-content-center flex-nowrap">
                                                    @if(!empty($rekap->pdf) && ($u = $rekapAssetUrl($rekap->pdf)))
                                                    <a href="{{ $u }}" class="btn btn-sm btn-danger py-0 px-2" target="_blank" title="Download PDF" style="font-size:11px;">PDF</a>
                                                    @endif
                                                    @if(!empty($rekap->excel) && ($u = $rekapAssetUrl($rekap->excel)))
                                                    <a href="{{ $u }}" class="btn btn-sm btn-success py-0 px-2" target="_blank" title="Download XLSX" style="font-size:11px;">XLSX</a>
                                                    @endif

                                                    @if($sudahSp2d)
                                                        @php
                                                            $lbl = $isLebihRekap ? 'NTPN' : 'SP2D';
                                                        @endphp
                                                        <span class="badge bg-label-info d-inline-flex align-items-center" style="font-size:10px;padding:3px 8px;" title="{{ $lbl }}: {{ $rekap->sp2d }}">
                                                            <i class="bx bx-check-circle me-1"></i> {{ $lbl }}
                                                        </span>
                                                    @else
                                                        @php
                                                            $lbl = $isLebihRekap ? 'NTPN' : 'SP2D';
                                                        @endphp
                                                        <button type="button"
                                                            class="btn btn-sm btn-primary btn-aksi-sp2d py-0 px-2"
                                                            data-rekap-id="{{ $rekap->id }}"
                                                            data-rekap-periode="{{ $rekap->periode }}"
                                                            title="Input No {{ $lbl }}"
                                                            style="font-size:11px;">
                                                            <i class="bx bx-edit-alt"></i> Proses
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="9" class="text-center">Belum ada data rekap yang diproses.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

{{-- Modal Input SP2D --}}
<div class="modal fade" id="modalSp2d" tabindex="-1" aria-labelledby="modalSp2dLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalSp2dLabel">Input No SP2D</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2">
                    <span class="text-muted small">Rekap:</span>
                    <strong id="sp2dRekapPeriode" class="d-block"></strong>
                </div>
                <div class="alert alert-warning small py-2 mb-3">
                    <i class="bx bx-info-circle me-1"></i>
                    <span id="sp2dWarningText">Data SP2D hanya bisa diinput <b>satu kali</b>. Pastikan nomor dan tanggal sudah benar.</span>
                </div>
                <input type="hidden" id="sp2dRekapId">
                <div class="mb-3">
                    <label for="sp2dUraian" class="form-label">Uraian Pembayaran <span class="text-muted">(Opsional)</span></label>
                    <input type="text" class="form-control" id="sp2dUraian" placeholder="Cth: Pembayaran kekurangan bayar">
                </div>
                <div class="mb-3">
                    <label id="lblSp2dNomor" for="sp2dNomor" class="form-label">No SP2D <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="sp2dNomor" placeholder="Masukkan No SP2D" required>
                </div>
                <div class="mb-3">
                    <label id="lblSp2dTanggal" for="sp2dTanggal" class="form-label">Tanggal SP2D <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="sp2dTanggal" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btnSubmitSp2d">
                    <span class="tf-icons bx bx-send"></span>&nbsp; Proses SP2D
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Input SP2D Individu --}}
<div class="modal fade" id="modalSp2dIndividu" tabindex="-1" aria-labelledby="modalSp2dIndividuLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalSp2dIndividuLabel">Input No SP2D Individu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2">
                    <span class="text-muted small">NIDN - Nama:</span>
                    <strong id="sp2dIndividuNidnLabel" class="d-block"></strong>
                </div>
                <div class="alert alert-warning small py-2 mb-3">
                    <i class="bx bx-info-circle me-1"></i>
                    <span id="sp2dIndividuWarningText">Data SP2D ini akan disimpan untuk dosen dengan NIDN di atas secara spesifik.</span>
                </div>
                <input type="hidden" id="sp2dIndividuNidn">
                <input type="hidden" id="sp2dIndividuJenis">
                <div class="mb-3">
                    <label for="sp2dIndividuUraian" class="form-label">Uraian Pembayaran <span class="text-muted">(Opsional)</span></label>
                    <input type="text" class="form-control" id="sp2dIndividuUraian" placeholder="Cth: Pembayaran kekurangan bayar tahun 2026">
                </div>
                <div class="mb-3">
                    <label id="lblSp2dIndividuNomor" for="sp2dIndividuNomor" class="form-label">No SP2D <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="sp2dIndividuNomor" placeholder="Masukkan No SP2D" required>
                </div>
                <div class="mb-3">
                    <label id="lblSp2dIndividuTanggal" for="sp2dIndividuTanggal" class="form-label">Tanggal SP2D <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="sp2dIndividuTanggal" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btnSubmitSp2dIndividu">
                    <span class="tf-icons bx bx-send"></span>&nbsp; Proses SP2D
                </button>
            </div>
        </div>
    </div>
</div>

            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const btnProses = document.getElementById('btnProsesKekurangan');
        const btnCekData = document.getElementById('btnCekDataKekurangan');
        const formProses = document.getElementById('formProsesKekurangan');

        const formDestroyKurang = document.getElementById('formDestroyKurang');
        const formDestroyLebih = document.getElementById('formDestroyLebih');

        const alertApi = window.SptjmAlert;

        // TAMBAHAN: MENGINGAT POSISI TAB SAAT RELOAD
        const tabElements = document.querySelectorAll('button[data-bs-toggle="tab"]');
        tabElements.forEach(tab => {
            tab.addEventListener('shown.bs.tab', function (event) {
                localStorage.setItem('activeKekuranganTab', event.target.id);
            });
        });

        const urlParams = new URLSearchParams(window.location.search);
        let activeTabId = null;
        
        if (urlParams.has('lebih_page')) {
            activeTabId = 'tab-lebih-btn';
        } else if (urlParams.has('kurang_page')) {
            activeTabId = 'tab-kurang-btn';
        } else {
            activeTabId = localStorage.getItem('activeKekuranganTab');
        }

        if (activeTabId) {
            const tabToActivate = document.getElementById(activeTabId);
            if (tabToActivate) tabToActivate.click();
        }

        const getSelectedText = (sel) => {
            if (!sel) return '';
            const opt = sel.options[sel.selectedIndex];
            return opt ? opt.text : '';
        };

        const buildSummaryHtml = () => {
            const periode = document.querySelector('select[name="periode"]');
            const tipe = document.querySelector('select[name="tipe"]');
            const jenis = document.querySelector('select[name="jenis"]');
            const bank = document.querySelector('select[name="bank"]');

            if (!periode || !tipe || !jenis || !bank) return null;

            return `
                <div class="text-start">
                  <div>Periode: <b>${getSelectedText(periode)}</b></div>
                  <div>Tipe: <b>${getSelectedText(tipe)}</b></div>
                  <div>Jenis: <b>${getSelectedText(jenis)}</b></div>
                  <div>Bank: <b>${getSelectedText(bank)}</b></div>
                </div>
            `;
        };

        const showFlash = () => {
            const validationErrors = @json($errors->all());
            const flash = {
                success: @json(session('success')),
                error: @json(session('error')),
                warning: @json(session('warning')),
                info: @json(session('info') ?? ($flashInfo ?? null)),
            };

            if (!alertApi) {
                const msg = (validationErrors && validationErrors.length)
                    ? validationErrors.join('\n')
                    : (flash.error || flash.warning || flash.success || flash.info);
                if (msg) {
                    try { alert(String(msg)); } catch (e) {}
                }
                return;
            }

            if (validationErrors && validationErrors.length) {
                alertApi.warning('Validasi', validationErrors.join('<br>'));
                return;
            }

            if (flash.error) return alertApi.error('Gagal', String(flash.error));
            if (flash.warning) return alertApi.warning('Peringatan', String(flash.warning));
            if (flash.success) return alertApi.success('Berhasil', String(flash.success));
            if (flash.info) return alertApi.info('Info', String(flash.info));
        };

        showFlash();

        try {
            const _flashInfo = @json($flashInfo ?? null);
            if (_flashInfo && typeof _flashInfo === 'string' && _flashInfo.toLowerCase().includes('cek data')) {
                // Cegah pindah tab jika user sengaja di tab lain
                if (!urlParams.has('lebih_page') && !urlParams.has('kurang_page')) {
                     const tabBtn = document.getElementById('tab-kurang-btn');
                     if (tabBtn && localStorage.getItem('activeKekuranganTab') !== 'tab-lebih-btn') tabBtn.click();
                }
            }
        } catch (e) {}

        const confirmAndSubmit = async (actionUrl, title, html, confirmText) => {
            if (!formProses) return;

            if (!actionUrl) {
                if (alertApi) {
                    await alertApi.warning('Peringatan', 'Form tidak lengkap. Silakan refresh halaman.');
                }
                return;
            }

            if (alertApi) {
                const r = await alertApi.question(title, html, {
                    confirmButtonText: confirmText || 'Ya',
                });
                if (!r || !r.isConfirmed) return;
                formProses.setAttribute('action', actionUrl);
                formProses.submit();
                return;
            }

            if (confirm(title)) {
                formProses.setAttribute('action', actionUrl);
                formProses.submit();
            }
        };

        if (btnProses && formProses) {
            btnProses.addEventListener('click', async function () {
                const summary = buildSummaryHtml();
                if (!summary) {
                    if (alertApi) await alertApi.warning('Peringatan', 'Form tidak lengkap. Silakan refresh halaman.');
                    return;
                }

                const html = `
                    <div class="text-start">
                      <div class="mb-2">Proses akan menghitung dan menyimpan data kurang/lebih bayar untuk Tahun <b>{{ $versi }}</b>.</div>
                      ${summary}
                      <div class="mt-3 alert alert-warning mb-0">Bulan dianggap dibayar jika <b>No SP2D</b> dan <b>Tgl SP2D</b> terisi.</div>
                    </div>
                `;
                await confirmAndSubmit("{{ route('admin.kekurangan-bayar.proses') }}", 'Konfirmasi Proses', html, 'Ya, Proses');
            });
        }

        if (btnCekData && formProses) {
            btnCekData.addEventListener('click', async function () {
                const summary = buildSummaryHtml();
                if (!summary) {
                    if (alertApi) await alertApi.warning('Peringatan', 'Form tidak lengkap. Silakan refresh halaman.');
                    return;
                }

                const html = `
                    <div class="text-start">
                      <div class="mb-2">Cek Data akan menghitung data seperti Proses, namun <b>tanpa menyimpan</b> ke database.</div>
                      ${summary}
                      <div class="mt-3 alert alert-info mb-0">Jika hasil sudah benar, klik <b>Proses</b> untuk menyimpan.</div>
                    </div>
                `;
                await confirmAndSubmit("{{ route('admin.kekurangan-bayar.cek') }}", 'Konfirmasi Cek Data', html, 'Ya, Cek Data');
            });
        }

        const wireDeleteConfirm = (form, title, text) => {
            if (!form) return;
            form.addEventListener('submit', async function (e) {
                e.preventDefault();
                if (alertApi) {
                    const r = await alertApi.question(title, text, { confirmButtonText: 'Ya, Hapus' });
                    if (r && r.isConfirmed) form.submit();
                    return;
                }
                if (confirm(text)) form.submit();
            });
        };

        wireDeleteConfirm(formDestroyKurang, 'Konfirmasi', 'Yakin ingin menghapus data Kurang Bayar pada tahun ini?');
        wireDeleteConfirm(formDestroyLebih, 'Konfirmasi', 'Yakin ingin menghapus data Lebih Bayar pada tahun ini?');

        // Helper date
        const getTodayDate = () => {
            const today = new Date();
            const yyyy = today.getFullYear();
            const mm = String(today.getMonth() + 1).padStart(2, '0');
            const dd = String(today.getDate()).padStart(2, '0');
            return `${yyyy}-${mm}-${dd}`;
        };

        // SP2D MODAL HANDLERS
        const modalSp2dEl = document.getElementById('modalSp2d');
        const sp2dModal = modalSp2dEl ? new bootstrap.Modal(modalSp2dEl) : null;

        // Bind all Aksi buttons
        document.querySelectorAll('.btn-aksi-sp2d').forEach(btn => {
            btn.addEventListener('click', function () {
                const rekapId = this.dataset.rekapId;
                const rekapPeriode = this.dataset.rekapPeriode;
                const isLebih = (rekapPeriode || '').toLowerCase().includes('lebih');
                const labelText = isLebih ? 'NTPN' : 'SP2D';

                document.getElementById('sp2dRekapId').value = rekapId;
                document.getElementById('sp2dRekapPeriode').textContent = rekapPeriode;
                document.getElementById('sp2dUraian').value = '';
                document.getElementById('sp2dNomor').value = '';
                document.getElementById('sp2dTanggal').value = getTodayDate();
                
                // Set Labels dynamically
                document.getElementById('modalSp2dLabel').textContent = `Input No ${labelText}`;
                document.getElementById('lblSp2dNomor').innerHTML = `No ${labelText} <span class="text-danger">*</span>`;
                document.getElementById('sp2dNomor').placeholder = `Masukkan No ${labelText}`;
                document.getElementById('lblSp2dTanggal').innerHTML = `Tanggal ${labelText} <span class="text-danger">*</span>`;
                document.getElementById('sp2dWarningText').innerHTML = `Data ${labelText} hanya bisa diinput <b>satu kali</b>. Pastikan nomor dan tanggal sudah benar.`;
                document.getElementById('btnSubmitSp2d').innerHTML = `<span class="tf-icons bx bx-send"></span>&nbsp; Proses ${labelText}`;
                document.getElementById('btnSubmitSp2d').dataset.labelText = labelText;

                if (sp2dModal) sp2dModal.show();
            });
        });

        // Submit SP2D
        const btnSubmitSp2d = document.getElementById('btnSubmitSp2d');
        if (btnSubmitSp2d) {
            btnSubmitSp2d.addEventListener('click', async function () {
                const rekapId = document.getElementById('sp2dRekapId').value;
                const uraianPembayaran = document.getElementById('sp2dUraian').value.trim();
                const noSp2d = document.getElementById('sp2dNomor').value.trim();
                const tglSp2d = document.getElementById('sp2dTanggal').value;

                const labelText = btnSubmitSp2d.dataset.labelText || 'SP2D';

                if (!noSp2d || !tglSp2d) {
                    // Sembunyikan modal dulu agar peringatan tampil di depan
                    if (sp2dModal) sp2dModal.hide();
                    if (alertApi) {
                        await alertApi.warning('Peringatan', `No ${labelText} dan Tanggal wajib diisi.`);
                    } else {
                        alert(`No ${labelText} dan Tanggal wajib diisi.`);
                    }
                    // Tampilkan kembali modal
                    if (sp2dModal) sp2dModal.show();
                    return;
                }

                // Sembunyikan modal agar dialog konfirmasi tampil di depan
                if (sp2dModal) sp2dModal.hide();

                // Konfirmasi sebelum submit
                const konfHtml = `
                    <div class="text-start">
                        <div>No ${labelText}: <b>${noSp2d}</b></div>
                        <div>Tanggal: <b>${tglSp2d}</b></div>
                        <div class="mt-2 alert alert-warning mb-0 small">Data ${labelText} tidak bisa diubah setelah diproses.</div>
                    </div>
                `;

                let confirmed = false;
                if (alertApi) {
                    const r = await alertApi.question(`Konfirmasi Proses ${labelText}`, konfHtml, { confirmButtonText: 'Ya, Proses' });
                    confirmed = r && r.isConfirmed;
                } else {
                    confirmed = confirm(`Yakin ingin memproses ${labelText} ini? Data tidak bisa diubah setelahnya.`);
                }

                if (!confirmed) {
                    // Jika user batal, tampilkan kembali modal
                    if (sp2dModal) sp2dModal.show();
                    return;
                }

                // Disable button
                btnSubmitSp2d.disabled = true;
                btnSubmitSp2d.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Memproses...';

                try {
                    const token = document.querySelector('meta[name="csrf-token"]')?.content
                               || document.querySelector('input[name="_token"]')?.value;

                    const response = await fetch("{{ route('admin.kekurangan-bayar.aksi-sp2d') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            rekap_id: rekapId,
                            no_sp2d: noSp2d,
                            tanggal_sp2d: tglSp2d,
                            uraian_pembayaran: uraianPembayaran,
                        }),
                    });

                    const data = await response.json();

                    if (data.success) {
                        if (sp2dModal) sp2dModal.hide();
                        if (alertApi) {
                            await alertApi.success('Berhasil', data.message || `${labelText} berhasil diproses.`);
                        } else {
                            alert(data.message || `${labelText} berhasil diproses.`);
                        }
                        // Reload halaman untuk refresh data
                        window.location.reload();
                    } else {
                        if (alertApi) {
                            await alertApi.error('Gagal', data.message || 'Terjadi kesalahan.');
                        } else {
                            alert(data.message || 'Terjadi kesalahan.');
                        }
                    }
                } catch (err) {
                    console.error('SP2D submit error:', err);
                    if (alertApi) {
                        await alertApi.error('Error', 'Gagal menghubungi server. Silakan coba lagi.');
                    } else {
                        alert('Gagal menghubungi server.');
                    }
                } finally {
                    btnSubmitSp2d.disabled = false;
                    btnSubmitSp2d.innerHTML = `<span class="tf-icons bx bx-send"></span>&nbsp; Proses ${labelText}`;
                }
            });
        }
        // SP2D INDIVIDU MODAL HANDLERS
        const modalSp2dIndividuEl = document.getElementById('modalSp2dIndividu');
        const sp2dIndividuModal = modalSp2dIndividuEl ? new bootstrap.Modal(modalSp2dIndividuEl) : null;

        document.querySelectorAll('.btn-aksi-sp2d-individu').forEach(btn => {
            btn.addEventListener('click', function () {
                const nidn = this.dataset.nidn;
                const nama = this.dataset.nama;
                const jenis = this.dataset.jenis;
                const isLebih = (jenis === 'lebih');
                const labelText = isLebih ? 'NTPN' : 'SP2D';

                document.getElementById('sp2dIndividuNidn').value = nidn;
                document.getElementById('sp2dIndividuJenis').value = jenis;
                document.getElementById('sp2dIndividuNidnLabel').textContent = nidn + ' - ' + nama + ' (' + jenis.toUpperCase() + ')';
                document.getElementById('sp2dIndividuUraian').value = '';
                document.getElementById('sp2dIndividuNomor').value = '';
                document.getElementById('sp2dIndividuTanggal').value = getTodayDate();

                document.getElementById('modalSp2dIndividuLabel').textContent = `Input No ${labelText} Individu`;
                document.getElementById('lblSp2dIndividuNomor').innerHTML = `No ${labelText} <span class="text-danger">*</span>`;
                document.getElementById('sp2dIndividuNomor').placeholder = `Masukkan No ${labelText}`;
                document.getElementById('lblSp2dIndividuTanggal').innerHTML = `Tanggal ${labelText} <span class="text-danger">*</span>`;
                document.getElementById('sp2dIndividuWarningText').innerHTML = `Data ${labelText} ini akan disimpan untuk dosen dengan NIDN di atas secara spesifik.`;
                document.getElementById('btnSubmitSp2dIndividu').innerHTML = `<span class="tf-icons bx bx-send"></span>&nbsp; Proses ${labelText}`;
                document.getElementById('btnSubmitSp2dIndividu').dataset.labelText = labelText;

                if (sp2dIndividuModal) sp2dIndividuModal.show();
            });
        });

        const btnSubmitSp2dIndividu = document.getElementById('btnSubmitSp2dIndividu');
        if (btnSubmitSp2dIndividu) {
            btnSubmitSp2dIndividu.addEventListener('click', async function () {
                const nidn = document.getElementById('sp2dIndividuNidn').value;
                const jenis = document.getElementById('sp2dIndividuJenis').value;
                const uraianPembayaran = document.getElementById('sp2dIndividuUraian').value.trim();
                const noSp2d = document.getElementById('sp2dIndividuNomor').value.trim();
                const tglSp2d = document.getElementById('sp2dIndividuTanggal').value;

                const labelText = btnSubmitSp2dIndividu.dataset.labelText || 'SP2D';

                if (!noSp2d || !tglSp2d) {
                    if (sp2dIndividuModal) sp2dIndividuModal.hide();
                    if (alertApi) {
                        await alertApi.warning('Peringatan', `No ${labelText} dan Tanggal wajib diisi.`);
                    } else {
                        alert(`No ${labelText} dan Tanggal wajib diisi.`);
                    }
                    if (sp2dIndividuModal) sp2dIndividuModal.show();
                    return;
                }

                if (sp2dIndividuModal) sp2dIndividuModal.hide();

                const konfHtml = `
                    <div class="text-start">
                        <div>NIDN: <b>${nidn}</b></div>
                        <div>No ${labelText}: <b>${noSp2d}</b></div>
                        <div>Tanggal: <b>${tglSp2d}</b></div>
                        <div class="mt-2 alert alert-warning mb-0 small">Data ${labelText} tidak bisa diubah setelah diproses.</div>
                    </div>
                `;

                let confirmed = false;
                if (alertApi) {
                    const r = await alertApi.question(`Konfirmasi Proses ${labelText} Individu`, konfHtml, { confirmButtonText: 'Ya, Proses' });
                    confirmed = r && r.isConfirmed;
                } else {
                    confirmed = confirm(`Yakin ingin memproses ${labelText} ini? Data tidak bisa diubah setelahnya.`);
                }

                if (!confirmed) {
                    if (sp2dIndividuModal) sp2dIndividuModal.show();
                    return;
                }

                btnSubmitSp2dIndividu.disabled = true;
                btnSubmitSp2dIndividu.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Memproses...';

                try {
                    const token = document.querySelector('meta[name="csrf-token"]')?.content
                               || document.querySelector('input[name="_token"]')?.value;

                    const response = await fetch("{{ route('admin.kekurangan-bayar.aksi-sp2d') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            nidn: nidn,
                            jenis_sp2d: jenis,
                            no_sp2d: noSp2d,
                            tanggal_sp2d: tglSp2d,
                            uraian_pembayaran: uraianPembayaran,
                        }),
                    });

                    const data = await response.json();

                    if (data.success) {
                        if (sp2dIndividuModal) sp2dIndividuModal.hide();
                        if (alertApi) {
                            await alertApi.success('Berhasil', data.message || `${labelText} berhasil diproses.`);
                        } else {
                            alert(data.message || `${labelText} berhasil diproses.`);
                        }
                        window.location.reload();
                    } else {
                        if (alertApi) {
                            await alertApi.error('Gagal', data.message || 'Terjadi kesalahan.');
                        } else {
                            alert(data.message || 'Terjadi kesalahan.');
                        }
                    }
                } catch (err) {
                    console.error('SP2D submit error:', err);
                    if (alertApi) {
                        await alertApi.error('Error', 'Gagal menghubungi server. Silakan coba lagi.');
                    } else {
                        alert('Gagal menghubungi server.');
                    }
                } finally {
                    btnSubmitSp2dIndividu.disabled = false;
                    btnSubmitSp2dIndividu.innerHTML = `<span class="tf-icons bx bx-send"></span>&nbsp; Proses ${labelText}`;
                }
            });
        }
    });
</script>
@endpush