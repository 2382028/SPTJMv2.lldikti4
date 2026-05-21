@extends('layouts/contentNavbarLayoutDosen')

@section('title', 'SPTJM Online')

@section('content')

@php
$transaksi = $transaksi ?? null;
$months = [
  'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember',
];
@endphp

<div class="card" style="width: 100%; padding: 10px;">
  <h5 class="card-header text-start p-2">Monitoring Pembayaran</h5>
  <hr>

  @if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  @if (!empty($errorMessage))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
      {{ $errorMessage }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <form id="monitoringForm" action="{{ route('dosen.monitoring-pembayaran.cari') }}" method="POST">
    @csrf

    <div class="row mb-3 mx-2">
      <label class="col-sm-2 col-form-label"><b style="font-size: 12px;">NIDN / NUPTK</b></label>
      <div class="col-sm-10">
        <input type="text" class="form-control" value="{{ $nidn ?? '-' }}" readonly style="background-color: #eceef1;">
      </div>
    </div>

    <div class="row mb-3 mx-2">
      <label class="col-sm-2 col-form-label"><b style="font-size: 12px;">Tahun</b></label>
      <div class="col-sm-2">
        <select name="start_year" class="form-select">
          @if(!empty($years))
            @php $selStart = old('start_year', $startYear ?? $years[0]); @endphp
            @foreach($years as $y)
              <option value="{{ $y }}" {{ $selStart == $y ? 'selected' : '' }}>{{ $y }}</option>
            @endforeach
          @else
            <option value="">-</option>
          @endif
        </select>
      </div>
      <div class="col-auto d-flex align-items-center">
        <strong style="margin:0 6px;">s/d</strong>
      </div>
      <div class="col-sm-2">
        <select name="end_year" class="form-select">
          @if(!empty($years))
            @php $selEnd = old('end_year', $endYear ?? end($years)); @endphp
            @foreach($years as $y)
              <option value="{{ $y }}" {{ $selEnd == $y ? 'selected' : '' }}>{{ $y }}</option>
            @endforeach
          @else
            <option value="">-</option>
          @endif
        </select>
      </div>
      <div class="col-sm-3">
        <button type="submit" class="btn btn-primary w-100">
          <span class="tf-icons bx bx-search"></span>&nbsp; Tampilkan
        </button>
      </div>
    </div>

    @if ($transaksi)
      <div class="row mb-3 mx-2">
        <label class="col-sm-2 col-form-label">NIDN - Nama</label>
        <div class="col-sm-10">
          <input type="text" class="form-control" readonly style="background-color: #eceef1;"
            value="{{ $transaksi->NIDN }} - {{ $transaksi->Nama }}">
        </div>
      </div>

      <div class="row mb-3 mx-2">
        <label class="col-sm-2 col-form-label">Jabatan - Status</label>
        <div class="col-sm-10">
          <input type="text" class="form-control" readonly style="background-color: #eceef1;"
            value="{{ ($transaksi->JabatanSelected ?? $transaksi->Jabatan12) }} - {{ $transaksi->Aktif == 1 ? 'Aktif' : 'Tidak Aktif' }}">
        </div>
      </div>

      <div class="row mb-3 mx-2">
        <label class="col-sm-2 col-form-label">Perguruan Tinggi</label>
        <div class="col-sm-10">
          <input type="text" class="form-control" readonly style="background-color: #eceef1;"
            value="{{ $transaksi->Kode_PT }} - {{ $transaksi->PTS }}">
        </div>
      </div>

      <hr>

      <div class="row mb-3 mx-2 align-items-end">
        <div class="col-sm-6">
          <div class="d-flex gap-2 align-items-end">
            <div>
              <label class="form-label mb-1" style="font-size: 12px;">Filter Tahun</label>
              <select id="filterTahunVersi" name="tahun_versi" class="form-select" data-ajax-table-url="{{ route('dosen.monitoring-pembayaran.table') }}">
                @foreach(($yearsForNidn ?? []) as $y)
                  <option value="{{ $y }}" {{ ($selectedYear ?? null) == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
              </select>
            </div>

            <button type="button" class="btn btn-success" onclick="document.getElementById('exportForm').submit()">
              <span class="tf-icons bx bx-download"></span>&nbsp; Cetak
            </button>

            <button type="button" class="btn btn-primary" onclick="document.getElementById('cetakSptForm').submit()">
              <span class="tf-icons bx bx-printer"></span>&nbsp; Cetak SPT
            </button>
          </div>
        </div>
      </div>
    @endif
  </form>

  @if ($transaksi)
    <form id="exportForm" action="{{ route('dosen.monitoring-pembayaran.export-excel') }}" method="POST" class="d-none">
      @csrf
      <input id="exportTahunVersi" type="hidden" name="tahun_versi" value="{{ $selectedYear ?? '' }}">
    </form>

    <form id="cetakSptForm" action="{{ route('dosen.monitoring-pembayaran.cetak-spt') }}" method="POST" class="d-none">
      @csrf
      <input id="cetakSptTahunVersi" type="hidden" name="tahun_versi" value="{{ $selectedYear ?? '' }}">
      <input type="hidden" name="nidn" value="{{ $nidn ?? '' }}">
    </form>

    <div id="monitoringTableContainer">
      @include('dosen.partials.monitoring-pembayaran-table')
    </div>
  @endif

</div>
@endsection

@section('page-script')
<script>
(function () {
  const form = document.getElementById('monitoringForm');
  const select = document.getElementById('filterTahunVersi');
  const tableContainer = document.getElementById('monitoringTableContainer');
  const exportTahun = document.getElementById('exportTahunVersi');
  const cetakSptTahun = document.getElementById('cetakSptTahunVersi');

  if (!form || !select || !tableContainer) return;

  const csrf = form.querySelector('input[name="_token"]')?.value;
  const url = select.getAttribute('data-ajax-table-url');

  const setLoading = () => {
    tableContainer.innerHTML = '<div class="text-muted" style="padding:8px;">Memuat tabel...</div>';
  };

  select.addEventListener('change', async function () {
    try {
      setLoading();

      // Keep hidden forms in sync immediately (avoid race if user clicks quickly).
      if (exportTahun) exportTahun.value = select.value || '';
      if (cetakSptTahun) cetakSptTahun.value = select.value || '';

      const fd = new FormData();
      fd.append('_token', csrf || '');
      fd.append('start_year', form.querySelector('select[name="start_year"]')?.value || '');
      fd.append('end_year', form.querySelector('select[name="end_year"]')?.value || '');
      fd.append('tahun_versi', select.value || '');

      const res = await fetch(url, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: fd,
      });

      if (!res.ok) {
        tableContainer.innerHTML = '<div class="alert alert-danger" role="alert">Gagal memuat tabel.</div>';
        return;
      }

      const payload = await res.json();
      tableContainer.innerHTML = payload.html || '';
      if (payload.selectedYear) {
        if (exportTahun) exportTahun.value = payload.selectedYear;
        if (cetakSptTahun) cetakSptTahun.value = payload.selectedYear;
      }
    } catch (e) {
      tableContainer.innerHTML = '<div class="alert alert-danger" role="alert">Terjadi kesalahan saat memuat tabel.</div>';
    }
  });
})();
</script>
@endsection
