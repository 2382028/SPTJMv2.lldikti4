@extends('layouts/contentNavbarLayout')

@section('title', 'SPTJM Online')

@section('content')

@php
    $formatInt = function ($value) {
        $num = (float) ($value ?? 0);
        return number_format((int) round($num), 0, ',', '.');
    };

    // Background color for monthly cells when DB (nilai asli) != Aktual (hasil kalkulasi)
    // Rule:
    // - DB > Aktual => lebih bayar => green (+)
    // - DB < Aktual => kurang bayar => red (-)
    // Note: We color only the DB cell (kolom bulan tanpa '(Aktual)').
    $diffBgClass = function ($dbValue, $aktualValue) {
        $db = (int) round((float) ($dbValue ?? 0));
        $akt = (int) round((float) ($aktualValue ?? 0));
        $d = $db - $akt;
        if ($d === 0) return '';
        return $d > 0 ? 'table-success' : 'table-danger';
    };

    // Note: Totals section now shows ABSOLUTE totals (DB vs Aktual), not signed deltas.
@endphp

@php
    $detailRows = collect($detailKekurangan ?? []);
    $rekapKurangRows = collect($rekapKurang ?? []);
    $rekapLebihRows = collect($rekapLebih ?? []);

    $flashInfo = $flashInfo ?? null;

    $rekapAssetUrl = function ($path) {
        $p = trim((string) ($path ?? ''));
        if ($p === '') return null;
        $p = ltrim(str_replace('\\', '/', $p), '/');
        // If already prefixed with storage/, keep it.
        if (strpos($p, 'storage/') === 0) return asset($p);
        // Rekap files are stored under storage/app/public and served via /storage symlink.
        return asset('storage/' . $p);
    };
@endphp

<div class="content-wrapper">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <h5 class="card-header">Proses Kurang/Lebih Bayar - Tahun {{ $versi }}</h5>
                <div class="card-body">
                    {{-- Form Proses Kurang/Lebih Bayar (logika proses belum dimigrasi penuh) --}}
                    <form method="POST" action="{{ route('admin.kekurangan-bayar.proses') }}" id="formProsesKekurangan">
                        @csrf
                        <div class="row mb-4">
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
                            <div class="col-lg-2 col-md-4 mb-2 d-flex align-items-end">
                                <button type="button" class="btn btn-outline-primary btn-sm me-2 px-2" id="btnCekDataKekurangan" style="min-width:140px;">
                                    <span class="tf-icons bx bx-search"></span>&nbsp; Cek Data
                                </button>
                                <button type="button" class="btn btn-warning btn-sm px-2" id="btnProsesKekurangan" style="min-width:140px;">
                                    <span class="tf-icons bx bx-loader"></span>&nbsp; Proses
                                </button>
                            </div>
                        </div>
                    </form>
                    <div class="d-inline">
                        {{-- Hapus hanya Kurang Bayar --}}
                        <form action="{{ route('admin.kekurangan-bayar.destroy-kurang') }}" method="POST" class="d-inline me-1"
                            id="formDestroyKurang">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-warning">
                                Kurang Bayar
                            </button>
                        </form>

                        {{-- Hapus hanya Lebih Bayar --}}
                        <form action="{{ route('admin.kekurangan-bayar.destroy-lebih') }}" method="POST" class="d-inline me-1"
                            id="formDestroyLebih">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-primary">
                                Lebih Bayar
                            </button>
                        </form>

                        {{-- Hapus Rekap (ikon sampah) - hanya rekap kurang/lebih --}}
                        <a href="{{ route('admin.kekurangan-bayar.rekap') }}" class="btn btn-danger" title="Hapus Rekap">
                            <span class="tf-icons bx bx-trash"></span>
                        </a>
                    </div>
                </div>
            </div>


            <ul class="nav nav-tabs mb-3" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-data-btn" data-bs-toggle="tab" data-bs-target="#tab-data" type="button" role="tab" aria-controls="tab-data" aria-selected="true">
                        Data Kurang/Lebih Bayar
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-kurang-btn" data-bs-toggle="tab" data-bs-target="#tab-kurang" type="button" role="tab" aria-controls="tab-kurang" aria-selected="false">
                        Rekap Kurang Bayar
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-lebih-btn" data-bs-toggle="tab" data-bs-target="#tab-lebih" type="button" role="tab" aria-controls="tab-lebih" aria-selected="false">
                        Rekap Lebih Bayar
                    </button>
                </li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="tab-data" role="tabpanel" aria-labelledby="tab-data-btn" tabindex="0">
                    <div class="card mb-4">
                        <h5 class="card-header">Data Kurang/Lebih Bayar</h5>
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
                                        </tr>
                                        <tr>
                                            <th colspan="2" class="text-center">Jan</th>
                                            <th colspan="2" class="text-center">Jan (Aktual)</th>
                                            <th colspan="2" class="text-center">Feb</th>
                                            <th colspan="2" class="text-center">Feb (Aktual)</th>
                                            <th colspan="2" class="text-center">Mar</th>
                                            <th colspan="2" class="text-center">Mar (Aktual)</th>
                                            <th colspan="2" class="text-center">Apr</th>
                                            <th colspan="2" class="text-center">Apr (Aktual)</th>
                                            <th colspan="2" class="text-center">Mei</th>
                                            <th colspan="2" class="text-center">Mei (Aktual)</th>
                                            <th colspan="2" class="text-center">Jun</th>
                                            <th colspan="2" class="text-center">Jun (Aktual)</th>
                                            <th colspan="2" class="text-center">Jul</th>
                                            <th colspan="2" class="text-center">Jul (Aktual)</th>
                                            <th colspan="2" class="text-center">Ags</th>
                                            <th colspan="2" class="text-center">Ags (Aktual)</th>
                                            <th colspan="2" class="text-center">Sep</th>
                                            <th colspan="2" class="text-center">Sep (Aktual)</th>
                                            <th colspan="2" class="text-center">Okt</th>
                                            <th colspan="2" class="text-center">Okt (Aktual)</th>
                                            <th colspan="2" class="text-center">Nov</th>
                                            <th colspan="2" class="text-center">Nov (Aktual)</th>
                                            <th colspan="2" class="text-center">Des</th>
                                            <th colspan="2" class="text-center">Des (Aktual)</th>
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
                                            <th class="text-center">TPD</th>
                                            <th class="text-center">TKGB</th>
                                            <th class="text-center">TPD</th>
                                            <th class="text-center">TKGB</th>
                                            @endfor
                                            <th class="text-center">TPD</th>
                                            <th class="text-center">TKGB</th>
                                            <th class="text-center">TPD</th>
                                            <th class="text-center">TKGB</th>
                                            <th class="text-center">TPD</th>
                                            <th class="text-center">TKGB</th>
                                            <th class="text-center">TPD</th>
                                            <th class="text-center">TKGB</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($detailRows as $index => $row)
                                        @php
                                        $status = $row->Aktif == 1 ? 'Aktif' : 'Tidak Aktif';
                                        @endphp
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $row->NIDN }}</td>
                                            <td>{{ $row->Nama }}</td>
                                            <td>{{ $row->Jenis }}</td>
                                            <td>{{ $row->Jabatan12 }}</td>
                                            <td>{{ $status }}</td>
                                                @for ($i = 1; $i <= 12; $i++)
                                                @php
                                                    // Kolom bulan (Jan/Feb/...) = nilai asli dari DB
                                                    $dbTpd = $row->{'db_tpd'.$i} ?? 0;
                                                    $dbTkgb = $row->{'db_tkgb'.$i} ?? 0;

                                                    // Kolom bulan (Aktual) = hasil kalkulasi (seharusnya/bersih)
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
                                                    $kesimpulan = ((float) ($row->bersih ?? 0)) - ((float) ($row->bersih_akt ?? 0));
                                                    $clsKesimpulan = $kesimpulan == 0.0 ? '' : ($kesimpulan > 0 ? 'table-success' : 'table-danger');
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
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="65" class="text-center">Tidak ada data ditemukan</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-kurang" role="tabpanel" aria-labelledby="tab-kurang-btn" tabindex="0">
                    <div class="card mb-4">
                        <h5 class="card-header">Rekap Kurang Bayar</h5>
                        <div class="card-body">
                            <div class="table-responsive text-nowrap">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th class="text-center">No</th>
                                            <th class="text-center">Periode</th>
                                            <th class="text-center">Pegawai</th>
                                            <th class="text-center">Tipe</th>
                                            <th class="text-center">Bank</th>
                                            <th class="text-center">Tanggal Rekap</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($rekapKurangRows as $rekap)
                                        <tr>
                                            <td>{{ $rekap->id }}</td>
                                            <td>{{ $rekap->periode }}</td>
                                            <td>{{ $rekap->pegawai }}</td>
                                            <td>{{ $rekap->tipe }}</td>
                                            <td>{{ $rekap->bank }}</td>
                                            <td>{{ $rekap->created_at }}</td>
                                            <td>
                                                @if(!empty($rekap->pdf) && ($u = $rekapAssetUrl($rekap->pdf)))
                                                <a href="{{ $u }}" class="btn btn-sm btn-danger" target="_blank">PDF</a>
                                                @endif
                                                @if(!empty($rekap->excel) && ($u = $rekapAssetUrl($rekap->excel)))
                                                <a href="{{ $u }}" class="btn btn-sm btn-success" target="_blank">XLSX</a>
                                                @endif
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="7" class="text-center">Belum ada data rekap yang diproses.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-lebih" role="tabpanel" aria-labelledby="tab-lebih-btn" tabindex="0">
                    <div class="card mb-4">
                        <h5 class="card-header">Rekap Lebih Bayar</h5>
                        <div class="card-body">
                            <div class="table-responsive text-nowrap">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th class="text-center">No</th>
                                            <th class="text-center">Periode</th>
                                            <th class="text-center">Pegawai</th>
                                            <th class="text-center">Tipe</th>
                                            <th class="text-center">Bank</th>
                                            <th class="text-center">Tanggal Rekap</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($rekapLebihRows as $rekap)
                                        <tr>
                                            <td>{{ $rekap->id }}</td>
                                            <td>{{ $rekap->periode }}</td>
                                            <td>{{ $rekap->pegawai }}</td>
                                            <td>{{ $rekap->tipe }}</td>
                                            <td>{{ $rekap->bank }}</td>
                                            <td>{{ $rekap->created_at }}</td>
                                            <td>
                                                @if(!empty($rekap->pdf) && ($u = $rekapAssetUrl($rekap->pdf)))
                                                <a href="{{ $u }}" class="btn btn-sm btn-danger" target="_blank">PDF</a>
                                                @endif
                                                @if(!empty($rekap->excel) && ($u = $rekapAssetUrl($rekap->excel)))
                                                <a href="{{ $u }}" class="btn btn-sm btn-success" target="_blank">XLSX</a>
                                                @endif
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="7" class="text-center">Belum ada data rekap yang diproses.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
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
                // very small fallback if helper not available
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

            // show at most one flash (priority: error > warning > success > info)
            if (flash.error) return alertApi.error('Gagal', String(flash.error));
            if (flash.warning) return alertApi.warning('Peringatan', String(flash.warning));
            if (flash.success) return alertApi.success('Berhasil', String(flash.success));
            if (flash.info) return alertApi.info('Info', String(flash.info));
        };

        showFlash();

        // If cek action just ran (controller sets $flashInfo), ensure Data tab is active
        try {
            const _flashInfo = @json($flashInfo ?? null);
            if (_flashInfo && typeof _flashInfo === 'string' && _flashInfo.toLowerCase().includes('cek data')) {
                const tabBtn = document.getElementById('tab-data-btn');
                if (tabBtn) tabBtn.click();
            }
        } catch (e) {
            // ignore
        }

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

            // fallback native confirm
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
    });
</script>
@endpush
