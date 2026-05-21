@php
  $fmt = function ($v) { return number_format((float) $v, 0, ',', '.'); };
  $totals = $totals ?? [];
  $selectedYear = $selectedYear ?? '-';
  $transaksi = $transaksi ?? null;
  $sp2dCount = (int) ($totals['sp2dCount'] ?? 0);
@endphp

<div class="card sptjm-kpi-card h-100">
  <div class="card-body">
    <h6 class="mb-1">Ringkasan Tahun {{ $selectedYear }}</h6>
    <div class="text-muted" style="font-size:.85rem;">Akumulasi berdasarkan 12 bulan.</div>

    <hr class="my-3 sptjm-muted-border">

    <div class="d-flex justify-content-between align-items-center">
      <div class="text-muted">Total Gaji</div>
      <div class="fw-semibold">Rp {{ $fmt($totals['gaji'] ?? 0) }}</div>
    </div>
    <div class="d-flex justify-content-between align-items-center mt-2">
      <div class="text-muted">Kotor (TPD)</div>
      <div class="fw-semibold">Rp {{ $fmt($totals['kotorTpd'] ?? 0) }}</div>
    </div>
    <div class="d-flex justify-content-between align-items-center mt-2">
      <div class="text-muted">Kotor (TKGB)</div>
      <div class="fw-semibold">Rp {{ $fmt($totals['kotorTkgb'] ?? 0) }}</div>
    </div>
    <div class="d-flex justify-content-between align-items-center mt-2">
      <div class="text-muted">Pajak (TPD)</div>
      <div class="fw-semibold">Rp {{ $fmt($totals['pajakTpd'] ?? 0) }}</div>
    </div>
    <div class="d-flex justify-content-between align-items-center mt-2">
      <div class="text-muted">Pajak (TKGB)</div>
      <div class="fw-semibold">Rp {{ $fmt($totals['pajakTkgb'] ?? 0) }}</div>
    </div>
    <div class="d-flex justify-content-between align-items-center mt-2">
      <div class="text-muted">Bersih (TPD)</div>
      <div class="fw-semibold">Rp {{ $fmt($totals['bersihTpd'] ?? 0) }}</div>
    </div>
    <div class="d-flex justify-content-between align-items-center mt-2">
      <div class="text-muted">Bersih (TKGB)</div>
      <div class="fw-semibold">Rp {{ $fmt($totals['bersihTkgb'] ?? 0) }}</div>
    </div>
    <div class="d-flex justify-content-between align-items-center mt-3">
      <div class="text-muted">SP2D terbit</div>
      <div class="fw-semibold">{{ $sp2dCount }} bulan</div>
    </div>
  </div>
</div>
