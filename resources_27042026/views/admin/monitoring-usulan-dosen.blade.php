@extends('layouts/contentNavbarLayout')

@section('title', 'SPTJM Online')

@section('content')
<div class="card">
    <h5 class="card-header">Data Dosen Belum Ada Usulan</h5>

    {{-- Form Filter --}}
    <div class="container mb-4">
        <hr />
        <form class="row gx-2 gy-2 align-items-end" method="GET" action="{{ route('admin.monitoring-usulan-dosen') }}">
            <div class="col-md-3">
                <label for="searchInput" class="form-label fw-semibold">NIDN / NUPTK</label>
                <div class="input-group">
                    <input type="text" class="form-control form-control" id="searchInput" name="search"
                        placeholder="Masukan NIDN / NUPTK..." value="{{ request('search') }}">
                </div>
            </div>

            <div class="col-md-2">
                <label for="awalPeriode" class="form-label fw-semibold">Periode Awal</label>
                <select id="awalPeriode" name="awalPeriode" class="form-select form-select">
                    @foreach ($bulanIndonesia as $key => $bulan)
                    <option value="{{ $key }}" {{ request('awalPeriode') == $key ? 'selected' : '' }}>
                        {{ $bulan }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-auto d-flex align-items-end">
                <label class="fw-semibold mb-0">s.d</label>
            </div>

            <div class="col-md-2">
                <label for="akhirPeriode" class="form-label fw-semibold">Periode Akhir</label>
                <select id="akhirPeriode" name="akhirPeriode" class="form-select form-select">
                    @foreach ($bulanIndonesia as $key => $bulan)
                    <option value="{{ $key }}" {{ request('akhirPeriode', now()->month) == $key ? 'selected' : '' }}>
                        {{ $bulan }}
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- perPage moved to toolbar above table --}}

            <div class="col-auto d-flex align-items-end">
                <button type="submit" class="btn btn-primary btn">Tampilkan</button>
            </div>
        </form>
    </div>

    {{-- Export Toolbar --}}
    <div class="card-body">
        <nav class="navbar bg-light mb-3">
            <div class="container-fluid justify-content-end">
                <div class="d-flex align-items-center me-auto">
                    <label class="me-2 mb-0 fw-semibold">Per Halaman</label>
                    <select id="perPageSelect" class="form-select form-select-sm w-auto">
                        @foreach ([15,25,50,100] as $pp)
                        <option value="{{ $pp }}" {{ request('perPage', 15) == $pp ? 'selected' : '' }}>{{ $pp }}</option>
                        @endforeach
                    </select>
                </div>

                <a href="{{ route('admin.export-monitoring-usulan-dosen', request()->query()) }}" target="_blank"
                    class="btn btn-sm btn-success">
                    <i class="bx bxs-file-blank"></i> Export XLS
                </a>
            </div>
        </nav>

        {{-- Tabel Data --}}
        <div class="table-responsive">
            <table class="table table-bordered align-middle text-nowrap w-100 table-hover" id="monitoringTable">
                <thead class="table-light text-center">
                    <tr>
                        <th>No</th>
                        <th>NIDN</th>
                        <th>NUPTK</th>
                        <th>Nama</th>
                        <th>Jenis</th>
                        <th>Kode PT</th>
                        <th>PTS</th>
                        <th>Bulan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($dosenList as $data)
                    <tr>
                        <td class="text-center">{{ $dosenList->firstItem() ? $dosenList->firstItem() + $loop->index : $loop->iteration }}</td>
                        <td class="text-center">{{ $data->NIDN }}</td>
                        <td class="text-center">{{ $data->NUPTK ?: '-' }}</td>
                        <td class="text-wrap">{{ $data->Nama }}</td>
                        <td class="text-center">{{ $data->Jenis }}</td>
                        <td class="text-center">{{ $data->Kode_PT }}</td>
                        <td class="text-wrap">{{ $data->PTS }}</td>
                        <td class="text-center">
                            <a href="javascript:void(0);" class="text-decoration-underline text-primary"
                                onclick="showDetailModal('{{ $data->Nama }}', '{{ $data->kode_belum_usulan }}')">
                                {{ $data->bulan_belum_usulan }} Bulan
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">Tidak ada dosen aktif tanpa usulan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    {{-- PAGINATION --}}
    <div class="d-flex justify-content-end mt-3 me-3">
        {{ $dosenList->links('pagination::bootstrap-5') }}
    </div>

</div>

{{-- Modal Detail Bulan --}}
<div class="modal fade" id="modalDetail" tabindex="-1" aria-labelledby="modalDetailLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Bulan Belum Diusulkan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <p><strong>Nama :</strong> <span id="modalNamaDosen"></span></p>
                <div id="modalListBulan" style="font-family: monospace;"></div>
            </div>
        </div>
    </div>
</div>



{{-- JavaScript --}}
<script>
// Tampilkan Detail Modal
function showDetailModal(nama, kodeBelum) {
    document.getElementById('modalNamaDosen').textContent = nama;
    const kodeList = kodeBelum.split(',').map(k => k.trim()).filter(k => k !== '');
    const nmBulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober',
        'November', 'Desember'
    ]
    const formatted = kodeList.map((bulan) => {
        const index = nmBulan.indexOf(bulan)
        const kodeUsulan = index != -1 ? `KodeUsulan${index+1}` : `????`
        const padded = `${kodeUsulan} (${bulan})`.padEnd(25, ' ');
        return `${padded}: Belum Diusulkan`;
    }).join('\n');
    document.getElementById('modalListBulan').innerHTML = `<pre>${formatted}</pre>`;
    const modal = new bootstrap.Modal(document.getElementById('modalDetail'));
    modal.show();
}
</script>
<script>
// Update perPage via URL (keeps other query params)
document.addEventListener('DOMContentLoaded', function () {
    const sel = document.getElementById('perPageSelect');
    if (!sel) return;
    sel.addEventListener('change', function () {
        const params = new URLSearchParams(window.location.search);
        params.set('perPage', this.value);
        params.delete('page'); // reset page to first
        const qs = params.toString();
        window.location.search = qs ? `?${qs}` : '';
    });
});
</script>
@endsection
