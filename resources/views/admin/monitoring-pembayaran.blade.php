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
  @php
    $jenis = trim($transaksi->Jenis ?? '');
    $isPns = stripos($jenis, 'PNS') !== false && stripos($jenis, 'NON') === false;
  @endphp
  <div class="row mb-2 mx-2">
    <label class="col-sm-2 col-form-label py-1" style="font-size:13px;font-weight:600;">NIDN - Nama</label>
    <div class="col-sm-7">
      <input id="hdr-nidn" type="text" class="form-control" readonly style="background:#eceef1;font-size:14px;font-weight:600;" value="{{ $transaksi->NIDN }} - {{ $transaksi->Nama }}">
    </div>
    <div class="col-sm-3 d-flex align-items-center">
      <span id="badge-jenis" class="badge {{ $isPns ? 'bg-label-primary' : 'bg-label-success' }}" style="font-size:14px;font-weight:700;padding:6px 14px;">{{ $isPns ? 'PNS' : 'Non-PNS' }}</span>
    </div>
  </div>
  <div class="row mb-2 mx-2">
    <label class="col-sm-2 col-form-label py-1" style="font-size:13px;font-weight:600;">Jabatan - Status</label>
    <div class="col-sm-10">
      <input id="hdr-jabatan" type="text" class="form-control" readonly style="background:#eceef1;font-size:14px;" value="{{ $transaksi->JabatanSelected ?? $transaksi->Jabatan12 }} - {{ $transaksi->Aktif == 1 ? 'Aktif' : 'Tidak Aktif' }}">
    </div>
  </div>
  <div class="row mb-2 mx-2">
    <label class="col-sm-2 col-form-label py-1" style="font-size:13px;font-weight:600;">Perguruan Tinggi</label>
    <div class="col-sm-10">
      <input id="hdr-pt" type="text" class="form-control" readonly style="background:#eceef1;font-size:14px;" value="{{ $transaksi->Kode_PT }} - {{ $transaksi->PTS }}">
    </div>
  </div>

  {{-- Summary Cards --}}
  @php
    $sKewajiban = $summary['totalKewajiban'] ?? 0;
    $sDibayar = $summary['totalDibayar'] ?? 0;
    $sSelisih = $summary['totalSelisih'] ?? 0;
  @endphp
  <div class="row mx-2 mb-2 mt-1 g-2">
    <div class="col-md-4">
      <div class="card shadow-none border mb-0"><div class="card-body py-1 px-2">
        <div class="d-flex align-items-center">
          <span class="avatar-initial rounded bg-label-danger me-2" style="width:28px;height:28px;display:flex;align-items:center;justify-content:center;font-size:14px;"><i class="bx bx-upload"></i></span>
          <div><small class="text-muted" style="font-size:10px;">Total Kewajiban (OUT)</small><br><strong id="sum-kewajiban" style="font-size:13px;">Rp {{ number_format($sKewajiban,0,',','.') }}</strong></div>
        </div>
      </div></div>
    </div>
    <div class="col-md-4">
      <div class="card shadow-none border mb-0"><div class="card-body py-1 px-2">
        <div class="d-flex align-items-center">
          <span class="avatar-initial rounded bg-label-info me-2" style="width:28px;height:28px;display:flex;align-items:center;justify-content:center;font-size:14px;"><i class="bx bx-download"></i></span>
          <div><small class="text-muted" style="font-size:10px;">Total Dibayar (IN)</small><br><strong id="sum-dibayar" style="font-size:13px;">Rp {{ number_format($sDibayar,0,',','.') }}</strong></div>
        </div>
      </div></div>
    </div>
    <div class="col-md-4">
      <div class="card shadow-none border mb-0"><div class="card-body py-1 px-2">
        <div class="d-flex align-items-center">
          <span id="sum-selisih-icon" class="avatar-initial rounded {{ $sSelisih == 0 ? 'bg-label-success' : 'bg-label-danger' }} me-2" style="width:28px;height:28px;display:flex;align-items:center;justify-content:center;font-size:14px;"><i class="bx bx-transfer"></i></span>
          <div><small class="text-muted" style="font-size:10px;">Total Selisih</small><br><strong id="sum-selisih" style="font-size:13px;" class="{{ $sSelisih == 0 ? 'text-success' : 'text-danger' }}">Rp {{ number_format($sSelisih,0,',','.') }}</strong></div>
        </div>
      </div></div>
    </div>
  </div>

  <hr class="my-2">

  <div class="row mb-3 mx-2 align-items-end">
    <div class="col-sm-6">
      <div class="d-flex gap-2 align-items-end">
        @csrf
        <input type="hidden" name="start_year" value="{{ $startYear ?? old('start_year') }}">
        <input type="hidden" name="end_year" value="{{ $endYear ?? old('end_year') }}">

        <div>
          <label class="form-label mb-1" style="font-size:11px;">Filter Tahun</label>
          <select name="tahun_versi" class="form-select form-select-sm">
            @foreach(($yearsForNidn ?? []) as $y)
              <option value="{{ $y }}" {{ ($selectedYear ?? null) == $y ? 'selected' : '' }}>{{ $y }}</option>
            @endforeach
          </select>
        </div>

        <form action="{{ route('monitoring-pembayaran.export-excel') }}" method="POST">
          @csrf
          <input type="hidden" name="nidn" value="{{ $nidn ?? '' }}">
          <input type="hidden" name="tahun_versi" id="export_tahun_versi" value="{{ $selectedYear ?? '' }}">
          <button type="submit" class="btn btn-success btn-sm">
            <span class="tf-icons bx bx-download"></span>&nbsp; Cetak
          </button>
        </form>

        <form action="{{ route('monitoring-pembayaran.cetak-spt') }}" method="POST">
          @csrf
          <input type="hidden" name="nidn" value="{{ $nidn ?? '' }}">
          <input type="hidden" name="tahun_versi" id="cetak_spt_tahun_versi" value="{{ $selectedYear ?? '' }}">
          <button type="submit" class="btn btn-primary btn-sm">
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

  <style>.mp-tbl{font-size:12px;line-height:1.3}.mp-tbl th,.mp-tbl td{padding:3px 5px!important;vertical-align:middle}</style>
  @php
    $hasTkgb = collect($kotorTkgb)->merge($pajakTkgb)->merge($bersihTkgb)->sum() != 0;
    $totalGaji = array_sum($gajiBulanan);
    $totalKotorTpd = array_sum($kotorTpd);
    $totalKotorTkgb = array_sum($kotorTkgb);
    $totalPajakTpd = array_sum($pajakTpd);
    $totalPajakTkgb = array_sum($pajakTkgb);
    $totalBersihTpd = array_sum($bersihTpd);
    $totalBersihTkgb = array_sum($bersihTkgb);
    $nomColspan = $hasTkgb ? 6 : 3;
    $statusMap = ['usulan'=>['bg-label-warning','Usulan'],'proses'=>['bg-label-info','Proses'],'kurang'=>['bg-label-danger','Kurang'],'lebih'=>['bg-label-secondary','Lebih'],'selesai'=>['bg-label-success','Selesai']];
  @endphp
  <div class="table-responsive text-nowrap mt-2" style="overflow:auto;padding-right:0;">
    <table class="table table-bordered table-hover table-sm mp-tbl" id="mp-table" style="width:100%" data-has-tkgb="{{ $hasTkgb ? '1' : '0' }}">
      <thead>
        <tr>
          <th rowspan="2" class="text-center">Tahun</th>
          <th rowspan="2" class="text-center">Bulan</th>
          <th rowspan="2" class="text-center">Kode Usulan</th>
          <th rowspan="2" class="text-center">Gol/MK</th>
          <th rowspan="2" class="text-center">Gaji</th>
          <th colspan="{{ $nomColspan }}" class="text-center">Nominal</th>
          <th rowspan="2" class="text-center">NO SP2D</th>
          <th rowspan="2" class="text-center">TGL SP2D</th>
          <th rowspan="2" class="text-center">Selisih</th>
          <th rowspan="2" class="text-center">Status</th>
        </tr>
        <tr>
          <th class="text-center">Kotor TPD</th>
          @if($hasTkgb)<th class="text-center tkgb-col">Kotor TKGB</th>@endif
          <th class="text-center">Pajak TPD</th>
          @if($hasTkgb)<th class="text-center tkgb-col">Pajak TKGB</th>@endif
          <th class="text-center">Bersih TPD</th>
          @if($hasTkgb)<th class="text-center tkgb-col">Bersih TKGB</th>@endif
        </tr>
      </thead>
      <tbody>
        @foreach ($months as $index => $month)
        @php $sel = $selisihBulanan[$index] ?? 0; $st = $statusBulanan[$index] ?? null; @endphp
        <tr>
          <td class="text-center">{{ $selectedYear ?? '-' }}</td>
          <td>{{ $month }}</td>
          <td class="text-center">{{ $kodeUsulanBulanan[$index] ?? '-' }}</td>
          <td class="text-center">{{ $golonganBulanan[$index] ?? '-' }} - {{ $tahunBulanan[$index] ?? '-' }}</td>
          <td class="text-end">{{ number_format($gajiBulanan[$index] ?? 0,0,',','.') }}</td>
          <td class="text-end">{{ number_format($kotorTpd[$index] ?? 0,0,',','.') }}</td>
          @if($hasTkgb)<td class="text-end tkgb-col">{{ number_format($kotorTkgb[$index] ?? 0,0,',','.') }}</td>@endif
          <td class="text-end">{{ number_format($pajakTpd[$index] ?? 0,0,',','.') }}</td>
          @if($hasTkgb)<td class="text-end tkgb-col">{{ number_format($pajakTkgb[$index] ?? 0,0,',','.') }}</td>@endif
          <td class="text-end">{{ number_format($bersihTpd[$index] ?? 0,0,',','.') }}</td>
          @if($hasTkgb)<td class="text-end tkgb-col">{{ number_format($bersihTkgb[$index] ?? 0,0,',','.') }}</td>@endif
          <td class="text-center" style="font-size:11px;">{{ $noSp2d[$index] ?? '-' }}</td>
          @php
             $tglSp2dStr = $tglSp2d[$index] ?? '-';
             if ($tglSp2dStr !== '' && $tglSp2dStr !== '-') {
                 try { $tglSp2dStr = \Carbon\Carbon::parse($tglSp2dStr)->format('d/m/Y'); } catch(\Exception $e) {}
             }
          @endphp
          <td class="text-center" style="font-size:11px;">{{ $tglSp2dStr }}</td>
          <td class="text-end fw-bold {{ $sel < 0 ? 'text-danger' : ($sel > 0 ? 'text-purple' : 'text-success') }}" style="{{ $sel > 0 ? 'color:#7c3aed!important;' : '' }}">{{ $sel < 0 ? '-' : ($sel > 0 ? '+' : '') }}{{ number_format(abs($sel),0,',','.') }}</td>
          <td class="text-center">@if($st && isset($statusMap[$st]))<span class="badge {{ $statusMap[$st][0] }}" style="font-size:10px;">{{ $statusMap[$st][1] }}</span>@else - @endif</td>
        </tr>
        @endforeach

        @php $colsBefore = $hasTkgb ? 4 : 4; @endphp
        <tr class="fw-bold table-light">
          <td colspan="4" class="text-center">Jumlah</td>
          <td class="text-end">{{ number_format($totalGaji,0,',','.') }}</td>
          <td class="text-end">{{ number_format($totalKotorTpd,0,',','.') }}</td>
          @if($hasTkgb)<td class="text-end tkgb-col">{{ number_format($totalKotorTkgb,0,',','.') }}</td>@endif
          <td class="text-end">{{ number_format($totalPajakTpd,0,',','.') }}</td>
          @if($hasTkgb)<td class="text-end tkgb-col">{{ number_format($totalPajakTkgb,0,',','.') }}</td>@endif
          <td class="text-end">{{ number_format($totalBersihTpd,0,',','.') }}</td>
          @if($hasTkgb)<td class="text-end tkgb-col">{{ number_format($totalBersihTkgb,0,',','.') }}</td>@endif
          <td colspan="2"></td><td colspan="2"></td>
        </tr>

        <tr class="fw-bold" style="background-color: #fff0f0;">
          <td colspan="5" class="text-center">Jumlah Selisih Bayar</td>
          <td class="text-end">{{ number_format((float)($selisihTotals['selisihTpd'] ?? 0),0,',','.') }}</td>
          @if($hasTkgb)<td class="text-end tkgb-col">{{ number_format((float)($selisihTotals['selisihTkgb'] ?? 0),0,',','.') }}</td>@endif
          <td class="text-end">{{ number_format((float)($selisihTotals['selisihPajakTpd'] ?? 0),0,',','.') }}</td>
          @if($hasTkgb)<td class="text-end tkgb-col">{{ number_format((float)($selisihTotals['selisihPajakTkgb'] ?? 0),0,',','.') }}</td>@endif
          <td class="text-end">{{ number_format((float)($selisihTotals['selisihBersihTpd'] ?? 0),0,',','.') }}</td>
          @if($hasTkgb)<td class="text-end tkgb-col">{{ number_format((float)($selisihTotals['selisihBersihTkgb'] ?? 0),0,',','.') }}</td>@endif
          <td colspan="2"></td><td colspan="2"></td>
        </tr>

        @php
          // Total Akhir = Jumlah + Total Pembayaran Uraian
          $riwayatNominalSum = collect($riwayatPembayaran ?? [])->sum('nominal');
          $riwayatPajakSum = collect($riwayatPembayaran ?? [])->sum('pajak');
          $riwayatBersihSum = collect($riwayatPembayaran ?? [])->sum('bersih');
          $totalAkhirKotorTpd = $totalKotorTpd + $riwayatNominalSum;
          $totalAkhirKotorTkgb = $totalKotorTkgb;
          $totalAkhirPajakTpd = $totalPajakTpd + $riwayatPajakSum;
          $totalAkhirPajakTkgb = $totalPajakTkgb;
          $totalAkhirBersihTpd = $totalBersihTpd + $riwayatBersihSum;
          $totalAkhirBersihTkgb = $totalBersihTkgb;
          $totalAkhirGaji = $totalGaji;
        @endphp
        <tr class="fw-bold" style="background-color: #f0fff4;">
          <td colspan="4" class="text-center">Total Akhir</td>
          <td class="text-end">{{ number_format($totalAkhirGaji,0,',','.') }}</td>
          <td class="text-end">{{ number_format($totalAkhirKotorTpd,0,',','.') }}</td>
          @if($hasTkgb)<td class="text-end tkgb-col">{{ number_format($totalAkhirKotorTkgb,0,',','.') }}</td>@endif
          <td class="text-end">{{ number_format($totalAkhirPajakTpd,0,',','.') }}</td>
          @if($hasTkgb)<td class="text-end tkgb-col">{{ number_format($totalAkhirPajakTkgb,0,',','.') }}</td>@endif
          <td class="text-end">{{ number_format($totalAkhirBersihTpd,0,',','.') }}</td>
          @if($hasTkgb)<td class="text-end tkgb-col">{{ number_format($totalAkhirBersihTkgb,0,',','.') }}</td>@endif
          <td colspan="2"></td><td colspan="2"></td>
        </tr>
      </tbody>
    </table>
  </div>

  <hr class="mt-5 mb-4">
  <h6 class="text-start px-2 fw-bold" style="color: #566a7f;">Uraian Pembayaran</h6>
  
  <div class="table-responsive text-nowrap mb-4" style="overflow:auto; padding-right:0;">
    <table class="table table-bordered table-hover" id="tabel-riwayat" style="width:100%">
      <thead>
        <tr>
          <th class="text-center">No</th>
          <th class="text-center">Uraian Pembayaran</th>
          <th class="text-center">Bulan</th>
          <th class="text-center">Nominal</th>
          <th class="text-center">Pajak</th>
          <th class="text-center">Bersih</th>
          <th class="text-center">No SP2D</th>
          <th class="text-center">Tanggal</th>
        </tr>
      </thead>
      <tbody>
        @php
          $totalUraianBersih = 0;
          $totalUraianNominal = 0;
          $totalUraianPajak = 0;
        @endphp
        @forelse ($riwayatPembayaran ?? [] as $index => $riwayat)
        @php
          $totalUraianBersih += ($riwayat->bersih ?? 0);
          $totalUraianNominal += ($riwayat->nominal ?? 0);
          $totalUraianPajak += ($riwayat->pajak ?? 0);
        @endphp
        <tr>
          <td class="text-center">{{ $index + 1 }}</td>
          @php
             $uraianClean = $riwayat->uraian_pembayaran;
             foreach($months as $m) {
                 $uraianClean = str_ireplace(' ' . $m, '', $uraianClean);
             }
          @endphp
          <td>{{ ucfirst($uraianClean) }}</td>
          <td class="text-center">{{ $months[(int)$riwayat->bulan - 1] ?? $riwayat->bulan }}</td>
          <td class="text-end">{{ number_format($riwayat->nominal ?? 0, 0, ',', '.') }}</td>
          <td class="text-end">{{ number_format($riwayat->pajak ?? 0, 0, ',', '.') }}</td>
          <td class="text-end">{{ number_format($riwayat->bersih ?? 0, 0, ',', '.') }}</td>
          <td>{{ $riwayat->nomor }}</td>
          <td class="text-center">{{ $riwayat->tanggal ? \Carbon\Carbon::parse($riwayat->tanggal)->format('d-M-y') : '-' }}</td>
        </tr>
        @empty
        <tr>
          <td colspan="8" class="text-center">Tidak ada data riwayat pembayaran</td>
        </tr>
        @endforelse
        @if(count($riwayatPembayaran ?? []) > 0)
        @php
          $uraianSelisihNominal = (float)($selisihTotals['selisihTpd'] ?? 0);
          $uraianSelisihPajak = (float)($selisihTotals['selisihPajakTpd'] ?? 0);
          $uraianSelisihBersih = (float)($selisihTotals['selisihBersihTpd'] ?? 0);
          $uraianTotalAkhirNominal = $totalUraianNominal + $uraianSelisihNominal;
          $uraianTotalAkhirPajak = $totalUraianPajak + $uraianSelisihPajak;
          $uraianTotalAkhirBersih = $totalUraianBersih + $uraianSelisihBersih;
        @endphp
        <tr class="fw-bold table-light">
          <td colspan="3" class="text-start">Total Pembayaran</td>
          <td class="text-end">{{ number_format($totalUraianNominal, 0, ',', '.') }}</td>
          <td class="text-end">{{ number_format($totalUraianPajak, 0, ',', '.') }}</td>
          <td class="text-end">{{ number_format($totalUraianBersih, 0, ',', '.') }}</td>
          <td colspan="2"></td>
        </tr>
        <tr class="fw-bold" style="background-color: #fff0f0;">
          <td colspan="3" class="text-start">Selisih Bayar</td>
          <td class="text-end">{{ number_format($uraianSelisihNominal, 0, ',', '.') }}</td>
          <td class="text-end">{{ number_format($uraianSelisihPajak, 0, ',', '.') }}</td>
          <td class="text-end">{{ number_format($uraianSelisihBersih, 0, ',', '.') }}</td>
          <td colspan="2"></td>
        </tr>
        <tr class="fw-bold" style="background-color: #f0fff4;">
          <td colspan="3" class="text-start">Total Akhir</td>
          <td class="text-end">{{ number_format($uraianTotalAkhirNominal, 0, ',', '.') }}</td>
          <td class="text-end">{{ number_format($uraianTotalAkhirPajak, 0, ',', '.') }}</td>
          <td class="text-end">{{ number_format($uraianTotalAkhirBersih, 0, ',', '.') }}</td>
          <td colspan="2"></td>
        </tr>
        @endif
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
      const fmt = n => Number(n).toLocaleString('id-ID',{maximumFractionDigits:0});
      const statusCfg = {usulan:['bg-label-warning','Usulan'],proses:['bg-label-info','Proses'],kurang:['bg-label-danger','Kurang'],lebih:['bg-label-secondary','Lebih'],selesai:['bg-label-success','Selesai']};

      filterSelect.addEventListener('change', function() {
        const tahun = this.value, nidn = nidnInput?.value||'', sy = startYearInput?.value||'', ey = endYearInput?.value||'';
        const exy = document.getElementById('export_tahun_versi'); if(exy) exy.value=tahun;
        const csy = document.getElementById('cetak_spt_tahun_versi'); if(csy) csy.value=tahun;

        fetch("{{ route('monitoring-pembayaran.data') }}", {
          method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':token},
          body: JSON.stringify({nidn,start_year:sy,end_year:ey,tahun_versi:tahun})
        }).then(r=>r.json()).then(data=>{
          if(!data.success){console.error(data.message);return;}
          const h=data.header||{};

          // Header
          const el1=document.getElementById('hdr-nidn'); if(el1) el1.value=(h.NIDN||'')+' - '+(h.Nama||'');
          const el2=document.getElementById('hdr-jabatan'); if(el2) el2.value=(h.JabatanSelected||'')+' - '+(h.Aktif==1?'Aktif':'Tidak Aktif');
          const el3=document.getElementById('hdr-pt'); if(el3) el3.value=(h.Kode_PT||'')+' - '+(h.PTS||'');

          // PNS badge
          const badge=document.getElementById('badge-jenis');
          if(badge){
            const j=(h.Jenis||'').toUpperCase(), pns=j.indexOf('PNS')!==-1&&j.indexOf('NON')===-1;
            badge.textContent=pns?'PNS':'Non-PNS';
            badge.className='badge '+(pns?'bg-label-primary':'bg-label-success');
            badge.style.cssText='font-size:13px;font-weight:700;padding:6px 14px;';
          }
          const sm=data.summary||{};
          const ek=document.getElementById('sum-kewajiban'); if(ek) ek.textContent='Rp '+fmt(sm.totalKewajiban||0);
          const ed=document.getElementById('sum-dibayar'); if(ed) ed.textContent='Rp '+fmt(sm.totalDibayar||0);
          const es=document.getElementById('sum-selisih');
          if(es){es.textContent='Rp '+fmt(sm.totalSelisih||0); es.className=(sm.totalSelisih||0)==0?'text-success':'text-danger';}
          const si=document.getElementById('sum-selisih-icon');
          if(si){si.className='avatar-initial rounded '+((sm.totalSelisih||0)==0?'bg-label-success':'bg-label-danger')+' me-2';}

          const hasTkgb=(data.kotorTkgb||[]).some(v=>v!=0)||(data.pajakTkgb||[]).some(v=>v!=0)||(data.bersihTkgb||[]).some(v=>v!=0);
          const tbl=document.getElementById('mp-table');
          if(tbl) tbl.dataset.hasTkgb=hasTkgb?'1':'0';

          const thead=tbl?.querySelector('thead');
          if(thead){
            const nc=hasTkgb?6:3;
            thead.innerHTML=`<tr><th rowspan="2" class="text-center">Tahun</th><th rowspan="2" class="text-center">Bulan</th><th rowspan="2" class="text-center">Kode Usulan</th><th rowspan="2" class="text-center">Gol/MK</th><th rowspan="2" class="text-center">Gaji</th><th colspan="${nc}" class="text-center">Nominal</th><th rowspan="2" class="text-center">NO SP2D</th><th rowspan="2" class="text-center">TGL SP2D</th><th rowspan="2" class="text-center">Selisih</th><th rowspan="2" class="text-center">Status</th></tr><tr><th class="text-center">Kotor TPD</th>${hasTkgb?'<th class="text-center">Kotor TKGB</th>':''}<th class="text-center">Pajak TPD</th>${hasTkgb?'<th class="text-center">Pajak TKGB</th>':''}<th class="text-center">Bersih TPD</th>${hasTkgb?'<th class="text-center">Bersih TKGB</th>':''}</tr>`;
          }

          const tbody=tbl?.querySelector('tbody'); 
          if(tbody) {
            tbody.innerHTML='';
            const months=data.months||[], sb=data.selisihBulanan||[], stb=data.statusBulanan||[];
            const tkc=(v)=>hasTkgb?`<td class="text-end">${fmt(v)}</td>`:'';

            for(let i=0;i<months.length;i++){
              const s=sb[i]||0, st=stb[i], sc=s<0?'text-danger fw-bold':(s>0?'fw-bold':'text-success fw-bold'), ss=s>0?'color:#7c3aed!important;':'';
              const pfx=s<0?'-':(s>0?'+':'');
              let stH='-'; if(st&&statusCfg[st]) stH=`<span class="badge ${statusCfg[st][0]}" style="font-size:10px">${statusCfg[st][1]}</span>`;
              
              let tglMain = data.tglSp2d[i] ?? '-';
              if (tglMain !== '' && tglMain !== '-') {
                  const dMain = new Date(tglMain);
                  if(!isNaN(dMain)) {
                      const d = String(dMain.getDate()).padStart(2, '0');
                      const m = String(dMain.getMonth() + 1).padStart(2, '0');
                      const y = dMain.getFullYear();
                      tglMain = `${d}/${m}/${y}`;
                  }
              }

              tbody.innerHTML+=`<tr><td class="text-center">${data.selectedYear||'-'}</td><td>${months[i]}</td><td class="text-center">${data.kodeUsulanBulanan[i]??'-'}</td><td class="text-center">${data.golonganBulanan[i]??'-'} - ${data.tahunBulanan[i]??'-'}</td><td class="text-end">${fmt(data.gajiBulanan[i]??0)}</td><td class="text-end">${fmt(data.kotorTpd[i]??0)}</td>${tkc(data.kotorTkgb[i]??0)}<td class="text-end">${fmt(data.pajakTpd[i]??0)}</td>${tkc(data.pajakTkgb[i]??0)}<td class="text-end">${fmt(data.bersihTpd[i]??0)}</td>${tkc(data.bersihTkgb[i]??0)}<td class="text-center" style="font-size:11px">${data.noSp2d[i]??'-'}</td><td class="text-center" style="font-size:11px">${tglMain}</td><td class="${sc}" style="${ss}text-align:right">${pfx}${fmt(Math.abs(s))}</td><td class="text-center">${stH}</td></tr>`;
            }

            // Totals
            const t=data.totals||{};
            tbody.innerHTML+=`<tr class="fw-bold table-light"><td colspan="4" class="text-center">Jumlah</td><td class="text-end">${fmt(t.gaji||0)}</td><td class="text-end">${fmt(t.kotorTpd||0)}</td>${tkc(t.kotorTkgb||0)}<td class="text-end">${fmt(t.pajakTpd||0)}</td>${tkc(t.pajakTkgb||0)}<td class="text-end">${fmt(t.bersihTpd||0)}</td>${tkc(t.bersihTkgb||0)}<td colspan="2"></td><td colspan="2"></td></tr>`;

            // Selisih totals (soft red)
            const sl=data.selisihTotals||{};
            tbody.innerHTML+=`<tr class="fw-bold" style="background-color:#fff0f0"><td colspan="5" class="text-center">Jumlah Selisih Bayar</td><td class="text-end">${fmt(sl.selisihTpd||0)}</td>${tkc(sl.selisihTkgb||0)}<td class="text-end">${fmt(sl.selisihPajakTpd||0)}</td>${tkc(sl.selisihPajakTkgb||0)}<td class="text-end">${fmt(sl.selisihBersihTpd||0)}</td>${tkc(sl.selisihBersihTkgb||0)}<td colspan="2"></td><td colspan="2"></td></tr>`;

            // Total Akhir (soft green) = Jumlah + Total Pembayaran Uraian
            const riwayatData2 = data.riwayatPembayaran || [];
            let riwNom=0, riwPjk=0, riwBrs=0;
            riwayatData2.forEach(item => { riwNom+=parseFloat(item.nominal||0); riwPjk+=parseFloat(item.pajak||0); riwBrs+=parseFloat(item.bersih||0); });
            const taKotorTpd=(t.kotorTpd||0)+riwNom;
            const taPajakTpd=(t.pajakTpd||0)+riwPjk;
            const taBersihTpd=(t.bersihTpd||0)+riwBrs;
            tbody.innerHTML+=`<tr class="fw-bold" style="background-color:#f0fff4"><td colspan="4" class="text-center">Total Akhir</td><td class="text-end">${fmt(t.gaji||0)}</td><td class="text-end">${fmt(taKotorTpd)}</td>${tkc(t.kotorTkgb||0)}<td class="text-end">${fmt(taPajakTpd)}</td>${tkc(t.pajakTkgb||0)}<td class="text-end">${fmt(taBersihTpd)}</td>${tkc(t.bersihTkgb||0)}<td colspan="2"></td><td colspan="2"></td></tr>`;
          }

          // UPDATE TABEL KEDUA (URAIAN PEMBAYARAN)
          const tbodyRiwayat = document.querySelector('#tabel-riwayat tbody');
          if (tbodyRiwayat) {
            tbodyRiwayat.innerHTML = '';
            const riwayatData = data.riwayatPembayaran || [];
            
            if (riwayatData.length === 0) {
              tbodyRiwayat.innerHTML = '<tr><td colspan="8" class="text-center">Tidak ada data riwayat pembayaran</td></tr>';
            } else {
              let totalUraianBersih = 0;
              riwayatData.forEach((item, index) => {
                totalUraianBersih += parseFloat(item.bersih || 0);
                const tr = document.createElement('tr');
                
                let tglFormat = '-';
                if(item.tanggal) {
                   const d = new Date(item.tanggal);
                   tglFormat = d.toLocaleDateString('id-ID', { day:'2-digit', month:'short', year:'2-digit' }).replace(/ /g, '-');
                }

                tr.innerHTML = `
                  <td class="text-center">${index + 1}</td>
                  <td>${(() => {
                      let u = item.uraian_pembayaran ? item.uraian_pembayaran.charAt(0).toUpperCase() + item.uraian_pembayaran.slice(1) : '-';
                      const mn = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                      mn.forEach(m => {
                          const regex = new RegExp('\\s+' + m, 'gi');
                          u = u.replace(regex, '');
                      });
                      return u;
                  })()}</td>
                  <td class="text-center">${(() => { const mn = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember']; const b = parseInt(item.bulan); return (b >= 1 && b <= 12) ? mn[b-1] : (item.bulan || '-'); })()}</td>
                  <td class="text-end">${fmt(item.nominal || 0)}</td>
                  <td class="text-end">${fmt(item.pajak || 0)}</td>
                  <td class="text-end">${fmt(item.bersih || 0)}</td>
                  <td>${item.nomor || '-'}</td>
                  <td class="text-center">${tglFormat}</td>
                `;
                tbodyRiwayat.appendChild(tr);
              });

              let totalUraianNominal = 0;
              let totalUraianPajak = 0;
              riwayatData.forEach(item => {
                totalUraianNominal += parseFloat(item.nominal || 0);
                totalUraianPajak += parseFloat(item.pajak || 0);
              });

              const slU = data.selisihTotals || {};
              const uraianSelisihNom = slU.selisihTpd || 0;
              const uraianSelisihPjk = slU.selisihPajakTpd || 0;
              const uraianSelisihBrs = slU.selisihBersihTpd || 0;

              // Row: Total Pembayaran
              const trTotal = document.createElement('tr');
              trTotal.className = 'fw-bold table-light';
              trTotal.innerHTML = `
                <td colspan="3" class="text-start">Total Pembayaran</td>
                <td class="text-end">${fmt(totalUraianNominal)}</td>
                <td class="text-end">${fmt(totalUraianPajak)}</td>
                <td class="text-end">${fmt(totalUraianBersih)}</td>
                <td colspan="2"></td>
              `;
              tbodyRiwayat.appendChild(trTotal);

              // Row: Selisih Bayar (soft red)
              const trSelisih = document.createElement('tr');
              trSelisih.className = 'fw-bold';
              trSelisih.style.backgroundColor = '#fff0f0';
              trSelisih.innerHTML = `
                <td colspan="3" class="text-start">Selisih Bayar</td>
                <td class="text-end">${fmt(uraianSelisihNom)}</td>
                <td class="text-end">${fmt(uraianSelisihPjk)}</td>
                <td class="text-end">${fmt(uraianSelisihBrs)}</td>
                <td colspan="2"></td>
              `;
              tbodyRiwayat.appendChild(trSelisih);

              // Row: Total Akhir (soft green) = Total Pembayaran + Selisih
              const trAkhir = document.createElement('tr');
              trAkhir.className = 'fw-bold';
              trAkhir.style.backgroundColor = '#f0fff4';
              trAkhir.innerHTML = `
                <td colspan="3" class="text-start">Total Akhir</td>
                <td class="text-end">${fmt(totalUraianNominal + uraianSelisihNom)}</td>
                <td class="text-end">${fmt(totalUraianPajak + uraianSelisihPjk)}</td>
                <td class="text-end">${fmt(totalUraianBersih + uraianSelisihBrs)}</td>
                <td colspan="2"></td>
              `;
              tbodyRiwayat.appendChild(trAkhir);
            }
          }

        }).catch(err=>console.error(err));
      });
    });
  </script>
  @endif
</div>

@endsection