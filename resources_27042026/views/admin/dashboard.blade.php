@extends('layouts/contentNavbarLayout')

@section('title', 'SPTJM Online')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-12 col-md-4">
        <div class="card ">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between">
                    <div class="content-left">
                        <span class="text-heading">Jumlah Seluruh Dosen</span>
                        <div class="d-flex align-items-center my-1">
                            <h4 class="mb-0 me-2">{{ $totalDosen }}
                            </h4>
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
    <div class="col-12 col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between">
                    <div class="content-left">
                        <span class="text-heading">Jumlah Dosen PNS Aktif</span>
                        <div class="d-flex align-items-center my-1">
                            <h4 class="mb-0 me-2">{{ $jumlahDosenPNSAktif }}</h4>
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
                        <span class="text-heading">Jumlah Dosen PNS Tidak Aktif</span>
                        <div class="d-flex align-items-center my-1">
                            <h4 class="mb-0 me-2">{{ $jumlahDosenPNSNon }}</h4>
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

<div class="row g-3 mb-3">
    <div class="col-12 col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between">
                    <div class="content-left">
                        <span class="text-heading">Jumlah Perguruan Tinggi Swasta</span>
                        <div class="d-flex align-items-center my-1">
                            <h4 class="mb-0 me-2">{{ $ptsCount }}</h4>
                        </div>
                    </div>
                    <div class="avatar">
                        <span class="avatar-initial rounded bg-label-primary p-4">
                            <i class='bx bxs-graduation bx-lg'></i>
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
                        <span class="text-heading">Jumlah Dosen Non-PNS Aktif</span>
                        <div class="d-flex align-items-center my-1">
                            <h4 class="mb-0 me-2">{{ $jumlahDosenNonPNSAktif }}</h4>
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
                        <span class="text-heading">Jumlah Dosen Non-PNS Tidak Aktif</span>
                        <div class="d-flex align-items-center my-1">
                            <h4 class="mb-0 me-2">{{ $jumlahDosenNonPNSNon }}</h4>
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

<div class="card" style="width: 100%; padding: 10px;">
    <h5 class="card-header">Data Dosen Pensiun Berjalan</h5>

    <div class="table-responsive text-nowrap">
        <table class="table table-sm table-hover" id="dosenPensiunTable">
            <thead style="background-color: #dbdee0;">
                <tr>
                    <th>Nidn</th>
                    <th>NUPTK</th>
                    <th>Nama Dosen</th>
                    <th>Nama PTS</th>
                    <th>TMT Pensiun</th>
                    <th>Usia</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            {{-- body akan diisi oleh DataTables via AJAX --}}
        </table>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    $('#dosenPensiunTable').DataTable({
        processing: true,
        serverSide: true,
        paging: true,
        pageLength: 100,
        lengthChange: true,
        scrollX: true,
        scrollCollapse: true,
        lengthMenu: [[25, 50, 100], [25, 50, 100]],
        ajax: {
            url: '{{ route('admin.dashboard.dosen-pensiun.data') }}'
        },
        columns: [
            { data: 'nidn', name: 'nidn' },
            { data: 'nuptk', name: 'nuptk' },
            { data: 'nama', name: 'nama' },
            { data: 'pts', name: 'pts' },
            { data: 'tmt_pensiun', name: 'tmt_pensiun' },
            { data: 'usia', name: 'usia' },
            { data: 'status', name: 'status', orderable: false, searchable: false },
            { data: 'aksi', name: 'aksi', orderable: false, searchable: false }
        ],
        order: [[3, 'asc']],
        pagingType: 'simple_numbers',
        language: {
            paginate: {
                first: "Awal",
                last: "Akhir",
                next: "→",
                previous: "←",
            },
            zeroRecords: "Data tidak ditemukan",
            infoEmpty: "Tidak ada data tersedia",
            searchPlaceholder: "Cari data...",
            search: "Cari Data:",
        },
    });
});
</script>
@endsection