@extends('layouts/contentNavbarLayout')

@section('title', 'SPTJM Online')

@section('content')

@php
$transaksi = $transaksi ?? null;
$months = [
'Januari',
'Februari',
'Maret',
'April',
'Mei',
'Juni',
'Juli',
'Agustus',
'September',
'Oktober',
'November',
'Desember',
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

  <form action="{{ route('monitoring-pembayaran.cari') }}" method="POST">
    @csrf
    <div class="row mb-3 mx-2">
      <label class="col-sm-2 col-form-label"><b style="font-size: 12px;">NIDN / NUPTK</b></label>
      <div class="col-sm-6">
        <input type="text" class="form-control" name="nidn" value="{{ old('nidn', $nidn ?? '') }}"
          placeholder="Masukkan NIDN/NUPTK" required>
      </div>
      <div class="col-sm-2">
        <button type="submit" class="btn btn-primary w-100">
          <span class="tf-icons bx bx-search"></span>&nbsp; Cari
        </button>
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
    </div>
  </form>

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
        value="{{ $transaksi->JabatanSelected ?? $transaksi->Jabatan12 }} - {{ $transaksi->Aktif == 1 ? 'Aktif' : 'Tidak Aktif' }}">
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
        @csrf
        <input type="hidden" name="start_year" value="{{ $startYear ?? old('start_year') }}">
        <input type="hidden" name="end_year" value="{{ $endYear ?? old('end_year') }}">

        <div>
          <label class="form-label mb-1" style="font-size: 12px;">Filter Tahun</label>
          <select name="tahun_versi" class="form-select">
            @foreach(($yearsForNidn ?? []) as $y)
              <option value="{{ $y }}" {{ ($selectedYear ?? null) == $y ? 'selected' : '' }}>{{ $y }}</option>
            @endforeach
          </select>
        </div>

        <form action="{{ route('monitoring-pembayaran.export-excel') }}" method="POST">
          @csrf
          <input type="hidden" name="nidn" value="{{ $nidn ?? '' }}">
          <input type="hidden" name="tahun_versi" id="export_tahun_versi" value="{{ $selectedYear ?? '' }}">
          <button type="submit" class="btn btn-success">
            <span class="tf-icons bx bx-download"></span>&nbsp; Cetak
          </button>
        </form>

        <form action="{{ route('monitoring-pembayaran.cetak-spt') }}" method="POST">
          @csrf
          <input type="hidden" name="nidn" value="{{ $nidn ?? '' }}">
          <input type="hidden" name="tahun_versi" id="cetak_spt_tahun_versi" value="{{ $selectedYear ?? '' }}">
          <button type="submit" class="btn btn-primary">
            <span class="tf-icons bx bx-printer"></span>&nbsp; Cetak SPT
          </button>
        </form>

        <a
          href="{{ route('monitoring-pembayaran.cek-koordinat-spt-pdf', ['nidn' => $nidn ?? '', 'tahun_versi' => $selectedYear ?? '']) }}"
          target="_blank"
          rel="noopener"
          class="btn btn-outline-danger d-none"
        >
          <span class="tf-icons bx bx-target-lock"></span>&nbsp; Cek Koordinat
        </a>
      </div>
    </div>
  </div>

  <div class="table-responsive text-nowrap mt-4" style="overflow:auto; padding-right:0;">
    <table class="table table-bordered table-hover" style="width:100%">
      <thead>
        <tr>
          <th rowspan="2" class="text-center">Tahun</th>
          <th rowspan="2" class="text-center">Bulan</th>
          <th rowspan="2" class="text-center">Kode Usulan</th>
          <th rowspan="2" class="text-center">Pangkat Golongan</th>
          <th rowspan="2" class="text-center">Gaji</th>
          <th colspan="6" class="text-center">Nominal</th>
          <th rowspan="2" class="text-center">NO SP2D</th>
          <th rowspan="2" class="text-center">TGL SP2D</th>
        </tr>
        <tr>
          <th class="text-center">Kotor TPD</th>
          <th class="text-center">Kotor TKGB</th>
          <th class="text-center">Pajak TPD</th>
          <th class="text-center">Pajak TKGB</th>
          <th class="text-center">Bersih TPD</th>
          <th class="text-center">Bersih TKGB</th>
        </tr>
      </thead>
      <tbody>
        @php
        $totalGaji = array_sum($gajiBulanan);
        $totalKotorTpd = array_sum($kotorTpd);
        $totalKotorTkgb = array_sum($kotorTkgb);
        $totalPajakTpd = array_sum($pajakTpd);
        $totalPajakTkgb = array_sum($pajakTkgb);
        $totalBersihTpd = array_sum($bersihTpd);
        $totalBersihTkgb = array_sum($bersihTkgb);
        @endphp

        @foreach ($months as $index => $month)
        <tr>
          <td class="text-center">{{ $selectedYear ?? '-' }}</td>
          <td>{{ $month }}</td>
          <td class="text-center">{{ $kodeUsulanBulanan[$index] ?? '-' }}</td>
          <td class="text-center">{{ $golonganBulanan[$index] ?? '-' }} -
            {{ $tahunBulanan[$index] ?? '-' }}
          </td>
          <td class="text-center">{{ number_format( $gajiBulanan[$index] ?? 0, 0, ',', '.') }}</td>
          <td class="text-end">{{ number_format($kotorTpd[$index] ?? 0, 0, ',', '.') }}</td>
          <td class="text-end">{{ number_format($kotorTkgb[$index] ?? 0, 0, ',', '.') }}</td>
          <td class="text-end">{{ number_format($pajakTpd[$index] ?? 0, 0, ',', '.') }}</td>
          <td class="text-end">{{ number_format($pajakTkgb[$index] ?? 0, 0, ',', '.') }}</td>
          <td class="text-end">{{ number_format($bersihTpd[$index] ?? 0, 0, ',', '.') }}</td>
          <td class="text-end">{{ number_format($bersihTkgb[$index] ?? 0, 0, ',', '.') }}</td>
          <td class="text-center">{{ $noSp2d[$index] ?? '-' }}</td>
          <td class="text-center">{{ $tglSp2d[$index] ?? '-' }}</td>
        </tr>
        @endforeach

        <tr class="fw-bold">
          <td colspan="4" class="text-center">Jumlah</td>
          <td class="text-end">{{ number_format($totalGaji, 0, ',', '.') }}</td>
          <td class="text-end">{{ number_format($totalKotorTpd, 0, ',', '.') }}</td>
          <td class="text-end">{{ number_format($totalKotorTkgb, 0, ',', '.') }}</td>
          <td class="text-end">{{ number_format($totalPajakTpd, 0, ',', '.') }}</td>
          <td class="text-end">{{ number_format($totalPajakTkgb, 0, ',', '.') }}</td>
          <td class="text-end">{{ number_format($totalBersihTpd, 0, ',', '.') }}</td>
          <td class="text-end">{{ number_format($totalBersihTkgb, 0, ',', '.') }}</td>
          <td></td>
          <td></td>
        </tr>

        <tr class="fw-bold">
          <td colspan="5" class="text-center">Jumlah Selisih Bayar</td>
          <td class="text-end">{{ number_format((float)($selisihTotals['selisihTpd'] ?? 0), 0, ',', '.') }}</td>
          <td class="text-end">{{ number_format((float)($selisihTotals['selisihTkgb'] ?? 0), 0, ',', '.') }}</td>
          <td class="text-end">{{ number_format((float)($selisihTotals['selisihPajakTpd'] ?? 0), 0, ',', '.') }}</td>
          <td class="text-end">{{ number_format((float)($selisihTotals['selisihPajakTkgb'] ?? 0), 0, ',', '.') }}</td>
          <td class="text-end">{{ number_format((float)($selisihTotals['selisihBersihTpd'] ?? 0), 0, ',', '.') }}</td>
          <td class="text-end">{{ number_format((float)($selisihTotals['selisihBersihTkgb'] ?? 0), 0, ',', '.') }}</td>
          <td></td>
          <td></td>
        </tr>
      </tbody>
    </table>
  </div>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const filterSelect = document.querySelector('select[name="tahun_versi"]');
      if (!filterSelect) return;

      const token = document.querySelector('input[name="_token"]')?.value;
      const nidnInput = document.querySelector('input[name="nidn"]');
      const startYearInput = document.querySelector('input[name="start_year"]');
      const endYearInput = document.querySelector('input[name="end_year"]');

      function formatNumber(n) {
        return n.toLocaleString('id-ID', {maximumFractionDigits:0});
      }

      filterSelect.addEventListener('change', function() {
        const tahun = this.value;
        const nidn = nidnInput ? nidnInput.value : '';
        const startYear = startYearInput ? startYearInput.value : '';
        const endYear = endYearInput ? endYearInput.value : '';

        const exportYear = document.getElementById('export_tahun_versi');
        if (exportYear) exportYear.value = tahun;

        const cetakSptYear = document.getElementById('cetak_spt_tahun_versi');
        if (cetakSptYear) cetakSptYear.value = tahun;

        fetch("{{ route('monitoring-pembayaran.data') }}", {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token
          },
          body: JSON.stringify({ nidn: nidn, start_year: startYear, end_year: endYear, tahun_versi: tahun })
        })
        .then(r => r.json())
        .then(data => {
          if (!data.success) {
            console.error(data.message || 'Failed to load data');
            return;
          }

          // update header fields
          const headerNidn = document.querySelector('input[readonly][value*=" - "]');
          // safer: update dedicated fields by name
          const nidnNamaInput = document.querySelector('input[readonly][value]');
          if (nidnNamaInput) {
            nidnNamaInput.value = (data.header.NIDN || '') + ' - ' + (data.header.Nama || '');
          }
          const jabatanInput = document.querySelectorAll('input[readonly]')[1];
          if (jabatanInput) {
            jabatanInput.value = (data.header.JabatanSelected || '') + ' - ' + ((data.header.Aktif == 1) ? 'Aktif' : 'Tidak Aktif');
          }
          const ptInput = document.querySelectorAll('input[readonly]')[2];
          if (ptInput) {
            ptInput.value = (data.header.Kode_PT || '') + ' - ' + (data.header.PTS || '');
          }

          // rebuild table body
          const tbody = document.querySelector('table.table tbody');
          if (!tbody) return;
          tbody.innerHTML = '';

          const months = data.months || [];
          for (let i = 0; i < months.length; i++) {
            const tr = document.createElement('tr');

            const tdYear = document.createElement('td'); tdYear.className = 'text-center'; tdYear.innerHTML = '' + (data.selectedYear || '-') + '';
            const tdMonth = document.createElement('td'); tdMonth.textContent = months[i];
            const tdKode = document.createElement('td'); tdKode.className = 'text-center'; tdKode.textContent = data.kodeUsulanBulanan[i] ?? '-';
            const tdGol = document.createElement('td'); tdGol.className = 'text-center'; tdGol.innerHTML = (data.golonganBulanan[i] ?? '-') + ' - ' + (data.tahunBulanan[i] ?? '-');
            const tdGaji = document.createElement('td'); tdGaji.className = 'text-center'; tdGaji.textContent = formatNumber(data.gajiBulanan[i] ?? 0);

            const tdKotorTpd = document.createElement('td'); tdKotorTpd.className = 'text-end'; tdKotorTpd.textContent = formatNumber(data.kotorTpd[i] ?? 0);
            const tdKotorTkgb = document.createElement('td'); tdKotorTkgb.className = 'text-end'; tdKotorTkgb.textContent = formatNumber(data.kotorTkgb[i] ?? 0);
            const tdPajakTpd = document.createElement('td'); tdPajakTpd.className = 'text-end'; tdPajakTpd.textContent = formatNumber(data.pajakTpd[i] ?? 0);
            const tdPajakTkgb = document.createElement('td'); tdPajakTkgb.className = 'text-end'; tdPajakTkgb.textContent = formatNumber(data.pajakTkgb[i] ?? 0);
            const tdBersihTpd = document.createElement('td'); tdBersihTpd.className = 'text-end'; tdBersihTpd.textContent = formatNumber(data.bersihTpd[i] ?? 0);
            const tdBersihTkgb = document.createElement('td'); tdBersihTkgb.className = 'text-end'; tdBersihTkgb.textContent = formatNumber(data.bersihTkgb[i] ?? 0);
            const tdNoSp2d = document.createElement('td'); tdNoSp2d.className = 'text-center'; tdNoSp2d.textContent = data.noSp2d[i] ?? '-';
            const tdTglSp2d = document.createElement('td'); tdTglSp2d.className = 'text-center'; tdTglSp2d.textContent = data.tglSp2d[i] ?? '-';

            tr.appendChild(tdYear);
            tr.appendChild(tdMonth);
            tr.appendChild(tdKode);
            tr.appendChild(tdGol);
            tr.appendChild(tdGaji);
            tr.appendChild(tdKotorTpd);
            tr.appendChild(tdKotorTkgb);
            tr.appendChild(tdPajakTpd);
            tr.appendChild(tdPajakTkgb);
            tr.appendChild(tdBersihTpd);
            tr.appendChild(tdBersihTkgb);
            tr.appendChild(tdNoSp2d);
            tr.appendChild(tdTglSp2d);

            tbody.appendChild(tr);
          }

          // append totals row
          const trTotal = document.createElement('tr'); trTotal.className = 'fw-bold';
          trTotal.innerHTML = `
            <td colspan="4">Jumlah</td>
            <td class="text-end">${formatNumber(data.totals.gaji)}</td>
            <td class="text-end">${formatNumber(data.totals.kotorTpd)}</td>
            <td class="text-end">${formatNumber(data.totals.kotorTkgb)}</td>
            <td class="text-end">${formatNumber(data.totals.pajakTpd)}</td>
            <td class="text-end">${formatNumber(data.totals.pajakTkgb)}</td>
            <td class="text-end">${formatNumber(data.totals.bersihTpd)}</td>
            <td class="text-end">${formatNumber(data.totals.bersihTkgb)}</td>
            <td></td>
            <td></td>
          `;
          tbody.appendChild(trTotal);

          const sel = data.selisihTotals || {};
          const trSelisih = document.createElement('tr');
          trSelisih.className = 'fw-bold';
          trSelisih.innerHTML = `
            <td colspan="5">Jumlah Selisih Bayar</td>
            <td class="text-end">${formatNumber(sel.selisihTpd || 0)}</td>
            <td class="text-end">${formatNumber(sel.selisihTkgb || 0)}</td>
            <td class="text-end">${formatNumber(sel.selisihPajakTpd || 0)}</td>
            <td class="text-end">${formatNumber(sel.selisihPajakTkgb || 0)}</td>
            <td class="text-end">${formatNumber(sel.selisihBersihTpd || 0)}</td>
            <td class="text-end">${formatNumber(sel.selisihBersihTkgb || 0)}</td>
            <td></td>
            <td></td>
          `;
          tbody.appendChild(trSelisih);
        })
        .catch(err => console.error(err));
      });
    });
  </script>
  @endif
</div>

@endsection