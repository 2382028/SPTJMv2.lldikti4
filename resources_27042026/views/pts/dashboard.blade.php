@extends('layouts/contentNavbarLayoutPts')

@section('title', 'SPTJM Online')

@php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\PengaturanUsulan;

//tahun
$tahun = session('tahun') ?? date('Y');
// Cari pengaturan aktif per jenis (SPTJM & TUKIN) pada hari ini
$aktifSptjm = PengaturanUsulan::where('tahun', $tahun)
->where('jenis_usulan', 'SPTJM')
->where('status', 'Aktifkan')
->whereDate('tanggal_mulai', '<=', now())
->whereDate('tanggal_selesai', '>=', now())
->get();

$aktifTukin = PengaturanUsulan::where('tahun', $tahun)
->where('jenis_usulan', 'TUKIN')
->where('status', 'Aktifkan')
->whereDate('tanggal_mulai', '<=', now())
->whereDate('tanggal_selesai', '>=', now())
->get();
if (Auth::guard('pts')->check()) {
// Login sebagai PTS
$kodePtsLogin = Auth::guard('pts')->user()->kode_pts;
$baseQuery = DB::table('s_transaksi_2')->where('kode_pt', $kodePtsLogin)
->where('Tahun_versi',$tahun);
} elseif (Auth::guard('web')->check()) {
// Login sebagai user biasa (admin/pic)
$pemegangWilayah = Auth::user()->email;
$baseQuery = DB::table('s_transaksi_2')->where('pemegang_wilayah', $pemegangWilayah)
->where('Tahun_versi',$tahun);
} else {
$baseQuery = DB::table('s_transaksi_2')->whereRaw('1 = 0'); // hasil kosong kalau belum login
}

// Jumlah total dosen
$jumlahDosen = (clone $baseQuery)->count();
// Hitung semua kategori
$jumlahPnsAktif = (clone $baseQuery)->where('jenis', 'PNS')->where('aktif', '1')->count();
$jumlahPnsTidakAktif = (clone $baseQuery)->where('jenis', 'PNS')->where('aktif',
'0')->count();
$jumlahNonPnsAktif = (clone $baseQuery)->where('jenis', 'NON PNS')->where('aktif',
'1')->count();
$jumlahNonPnsTidakAktif = (clone $baseQuery)->where('jenis', 'NON PNS')->where('aktif',
'0')->count();
@endphp


@section('content')

<div class="container-xxl d-flex justify-content-center">
    <div class="mb-3">
        <div class="card">
            <div class="card-body text-center">
                
                    @php
                        $sptjmOpen = $aktifSptjm->isNotEmpty();
                        $tukinOpen = $aktifTukin->isNotEmpty();
                    @endphp

                    {{-- Info SPTJM --}}
                    @if ($sptjmOpen)
                        @php
                            $sptjmMulai = \Carbon\Carbon::parse($aktifSptjm->min('tanggal_mulai'))->format('d-m-Y');
                            $sptjmSelesai = \Carbon\Carbon::parse($aktifSptjm->max('tanggal_selesai'))->format('d-m-Y');
                        @endphp
                        <h5 class="fw-bold d-block mb-1 text-danger card-text-custom">
                            Usulan SPTJM dibuka dari tanggal {{ $sptjmMulai }} sampai {{ $sptjmSelesai }}
                        </h5>
                    @else
                        <h5 class="fw-bold d-block mb-1 text-danger card-text-custom">Usulan SPTJM belum dibuka.</h5>
                    @endif

                    {{-- Info TUKIN --}}
                    @if ($tukinOpen)
                        @php
                            $tukinMulai = \Carbon\Carbon::parse($aktifTukin->min('tanggal_mulai'))->format('d-m-Y');
                            $tukinSelesai = \Carbon\Carbon::parse($aktifTukin->max('tanggal_selesai'))->format('d-m-Y');
                        @endphp
                        <h5 class="fw-bold d-block mb-1 text-danger card-text-custom">
                            Usulan TUKIN dibuka dari tanggal {{ $tukinMulai }} sampai {{ $tukinSelesai }}
                        </h5>
                    @else
                        <h5 class="fw-bold d-block mb-1 text-danger card-text-custom">Usulan TUKIN belum dibuka.</h5>
                    @endif
            </div>
        </div>
    </div>
</div>


<hr class="my-3">

<div class="row g-3 mb-3">
    <div class="col-12 col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between">
                    <div class="content-left">
                        <span class="text-heading">Dosen PNS Aktif</span>
                        <div class="d-flex align-items-center my-1">
                            <h4 class="mb-0 me-2">{{ $jumlahPnsAktif }}</h4>
                        </div>
                    </div>
                    <div class="avatar">
                        <span class="avatar-initial rounded bg-label-success p-4">
                            <i class="bx bx-user-check bx-lg"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between">
                    <div class="content-left">
                        <span class="text-heading">Dosen PNS Tidak Aktif</span>
                        <div class="d-flex align-items-center my-1">
                            <h4 class="mb-0 me-2">{{ $jumlahPnsTidakAktif }}</h4>
                        </div>
                    </div>
                    <div class="avatar">
                        <span class="avatar-initial rounded bg-label-danger p-4">
                            <i class="bx bx-user-x bx-lg"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between">
                    <div class="content-left">
                        <span class="text-heading">Seluruh Dosen {{ session('tahun') }}</span>
                        <div class="d-flex align-items-center my-1">
                            <h4 class="mb-0 me-2">{{ $jumlahDosen }}</h4>
                        </div>
                    </div>
                    <div class="avatar">
                        <span class="avatar-initial rounded bg-label-primary p-4">
                            <i class="bx bx-group bx-lg"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-12 col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between">
                    <div class="content-left">
                        <span class="text-heading">Dosen Non-PNS Aktif</span>
                        <div class="d-flex align-items-center my-1">
                            <h4 class="mb-0 me-2">{{ $jumlahNonPnsAktif }}</h4>
                        </div>
                    </div>
                    <div class="avatar">
                        <span class="avatar-initial rounded bg-label-success p-4">
                            <i class="bx bx-user-check bx-lg"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between">
                    <div class="content-left">
                        <span class="text-heading">Dosen Non-PNS Tidak Aktif</span>
                        <div class="d-flex align-items-center my-1">
                            <h4 class="mb-0 me-2">{{ $jumlahNonPnsTidakAktif }}</h4>
                        </div>
                    </div>
                    <div class="avatar">
                        <span class="avatar-initial rounded bg-label-danger p-4">
                            <i class="bx bx-user-x bx-lg"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<hr class="my-4">

@endsection
