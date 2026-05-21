@extends('layouts/contentNavbarLayoutPts')

@section('title', 'SPTJM Online')

@section('content')
<div class="card mt-2">
  <h5 class="card-header">Data Dosen Belum Ada Usulan</h5>

  {{-- Form Filter --}}
  <div class="card-body mb-2">
    <form method="GET" action="{{ route('pts.monitoring-usulan-dosen') }}">
      <div class="row gx-3 gy-2 align-items-end">
        <div class="col-md-3">
          <label for="searchInput" class="form-label fw-semibold small">NIDN / NUPTK</label>
          <input type="text" class="form-control form-control-sm" id="searchInput" name="search" placeholder="Masukan NIDN atau NUPTK..." value="{{ request('search') }}">
        </div>

        <div class="col-md-2">
          <label for="awalPeriode" class="form-label fw-semibold small">Periode Awal</label>
          <select id="awalPeriode" name="awalPeriode" class="form-select form-select-sm">
            @foreach ($bulanIndonesia as $key => $bulan)
            <option value="{{ $key }}" {{ request('awalPeriode') == $key ? 'selected' : '' }}>
              {{ $bulan }}
            </option>
            @endforeach
          </select>
        </div>

        <div class="col-auto col-md-1 text-center">
          <label class="form-label fw-semibold small d-block">s.d</label>
        </div>

        <div class="col-md-2">
          <label for="akhirPeriode" class="form-label fw-semibold small">Periode Akhir</label>
          <select id="akhirPeriode" name="akhirPeriode" class="form-select form-select-sm">
            @foreach ($bulanIndonesia as $key => $bulan)
            <option value="{{ $key }}" {{ request('akhirPeriode', now()->month) == $key ? 'selected' : '' }}>
              {{ $bulan }}
            </option>
            @endforeach
          </select>
        </div>

        <div class="col-md-3 text-md-end">
          <div class="d-flex justify-content-end gap-2">
              <button type="submit" formaction="{{ route('pts.export-data-belum-usulan') }}" formtarget="_blank" class="btn btn-success btn-sm py-1 px-3">Export XLS</button>
              <button type="submit" class="btn btn-primary btn-sm py-1 px-3">Tampilkan</button>
          </div>
        </div>
      </div>
    </form>
  </div>

  {{-- Export Toolbar and Table --}}
  <div class="card-body pt-0">
    {{-- Export button moved next to form submit; kept empty here --}}
    {{-- Tabel Data --}}
    <div class="table-responsive">
      <table class="table table-bordered align-middle text-nowrap w-100 table-hover" id="monitoringTable">
        <thead class="text-center" style="background-color: #dbdee0;">
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
          @forelse ($dosenList as $i => $data)
          <tr>
            <td class="text-center">{{ $i + 1 }}</td>
            <td class="text-center">{{ $data->NIDN }}</td>
            <td class="text-center">{{ $data->NUPTK ?? '-' }}</td>
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
  // Filter Pencarian
  document.getElementById("searchInput").addEventListener("keyup", function() {
    const filter = this.value.toLowerCase();
    document.querySelectorAll("#monitoringTable tbody tr").forEach(row => {
      row.style.display = row.textContent.toLowerCase().includes(filter) ? "" : "none";
    });
  });

  // Export now uses form submission with `formaction` so no JS builder is required.

  // Tampilkan Detail Modal
  function showDetailModal(nama, kodeBelum) {
    document.getElementById('modalNamaDosen').textContent = nama;
    const kodeList = kodeBelum.split(',').map(k => k.trim()).filter(k => k !== '');
    const formatted = kodeList.map(bulan => {
      const padded = bulan.padEnd(10, ' ');
      return `${padded}: Belum Diusulkan`;
    }).join('\n');
    document.getElementById('modalListBulan').innerHTML = `<pre>${formatted}</pre>`;
    const modal = new bootstrap.Modal(document.getElementById('modalDetail'));
    modal.show();
  }
</script>
@endsection