@extends('layouts/contentNavbarLayoutDosen')

@section('title', 'SPTJM Online')

@section('page-style')
<style>
  .sptjm-kpi-card {
    border: 0;
    box-shadow: 0 6px 18px rgba(34, 48, 62, .08);
  }
  .sptjm-kpi-label {
    font-size: .78rem;
    color: #6c757d;
  }
  .sptjm-kpi-value {
    font-weight: 700;
    letter-spacing: -.02em;
  }
  .sptjm-badge-soft {
    background: rgba(105, 108, 255, .12);
    color: #696cff;
    border: 1px solid rgba(105, 108, 255, .25);
  }
  .sptjm-muted-border {
    border-color: rgba(67, 89, 113, .12) !important;
  }
</style>
@endsection

@section('content')
@php
  $transaksi = $transaksi ?? null;
  $yearsForDosen = $yearsForDosen ?? [];
  $selectedYear = $selectedYear ?? '';
  $totals = $totals ?? [];
  $masaKerjaTerakhir = $masaKerjaTerakhir ?? null;
  $noRekening = $noRekening ?? null;
  $fmt = function ($v) {
    return number_format((float) $v, 0, ',', '.');
  };

  $isAktif = $transaksi ? ((string) ($transaksi->Aktif ?? '') === '1') : false;
  $nama = $transaksi->Nama ?? null;
  $nidn = $transaksi->NIDN ?? null;
  $nuptk = $transaksi->NUPTK ?? null;
  $pts = $transaksi->PTS ?? null;
  $kodePt = $transaksi->Kode_PT ?? null;
  $eligible = $transaksi->Eligible_span ?? null;
  $lastUpdate = $transaksi->Tanggal_Update_Terakhir ?? null;

  $totalBersih = (float) (($totals['bersihTpd'] ?? 0) + ($totals['bersihTkgb'] ?? 0));
@endphp

<div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-3">
  <div>
    <div class="sptjm-dashboard-title fw-bold">Dashboard Dosen</div>
    <div class="text-muted">Ringkasan informasi dan monitoring pembayaran dari data transaksi.</div>
  </div>
</div>

@if (!empty($errorMessage))
  <div class="alert alert-warning" role="alert">
    {{ $errorMessage }}
  </div>
@endif

<div class="row g-3 mb-3">
  <div class="col-12 col-md-4">
    <div class="card sptjm-kpi-card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div>
            <div class="sptjm-kpi-label">Status</div>
            <div class="d-flex align-items-center gap-2 mt-1">
              <div class="sptjm-kpi-value">{{ $transaksi ? ($isAktif ? 'Aktif' : 'Tidak Aktif') : '-' }}</div>
              @if($transaksi)
                <span class="badge {{ $isAktif ? 'bg-label-success' : 'bg-label-danger' }}">{{ $isAktif ? 'Aktif' : 'Nonaktif' }}</span>
              @endif
            </div>
            <div class="text-muted mt-1" style="font-size:.8rem;">Eligible: {{ $eligible ?: '-' }}</div>
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded bg-label-success p-4">
              <i class="bx bx-badge-check bx-lg"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-md-4">
    <div class="card sptjm-kpi-card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div>
            <div class="sptjm-kpi-label">Tahun Versi</div>
            <div class="d-flex align-items-center gap-2 mt-1">
              <div id="tahunVersiValue" class="sptjm-kpi-value">{{ $selectedYear !== '' ? $selectedYear : '-' }}</div>
              @if($selectedYear !== '')
                <span id="tahunVersiBadge" class="badge sptjm-badge-soft">{{ $selectedYear }}</span>
              @endif
            </div>
            <div class="text-muted mt-1" style="font-size:.8rem;">Update terakhir: {{ $lastUpdate ?: '-' }}</div>
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded bg-label-primary p-4">
              <i class="bx bx-calendar bx-lg"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-md-4">
    <div class="card sptjm-kpi-card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
              <div>
                <div class="sptjm-kpi-label">Total Bersih (TPD + TKGB)</div>
                <div class="sptjm-kpi-value mt-1">Rp <span id="totalBersihValue">{{ $fmt($totalBersih) }}</span></div>
                <!-- Kotor totals removed per request -->
                <div class="text-muted mt-1" style="font-size:.8rem;">SP2D terbit: <span id="sp2dCountValue">{{ (int) ($totals['sp2dCount'] ?? 0) }}</span> bulan</div>
              </div>
          <div class="avatar">
            <span class="avatar-initial rounded bg-label-warning p-4">
              <i class="bx bx-wallet bx-lg"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-12 col-lg-8">
    <div class="card sptjm-kpi-card h-100">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
          <div>
            <h6 class="mb-1">Profil Dosen (dari data transaksi)</h6>
            <div class="text-muted" style="font-size:.85rem;">Informasi identitas & instansi berdasarkan `s_transaksi_2`.</div>
          </div>

          <div class="d-flex align-items-center gap-2">
            <label class="text-muted" style="font-size:.85rem;">Tahun versi</label>
            <select id="tahunVersiSelect" name="tahun_versi" class="form-select" style="min-width: 140px;" data-ajax-url="{{ route('dosen.dashboard.summary') }}" {{ empty($yearsForDosen) ? 'disabled' : '' }}>
              @if (empty($yearsForDosen))
                <option value="">-</option>
              @else
                @foreach($yearsForDosen as $y)
                  <option value="{{ $y }}" {{ (string) $selectedYear === (string) $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
              @endif
            </select>
          </div>
        </div>

        <hr class="my-3 sptjm-muted-border">

        <div class="row g-3">
          <div class="col-12 col-md-6">
            <div class="d-flex justify-content-between gap-3">
              <div class="text-muted">Nama</div>
              <div class="text-end fw-semibold">{{ $nama ?: '-' }}</div>
            </div>
            <div class="d-flex justify-content-between gap-3 mt-2">
              <div class="text-muted">NIDN</div>
              <div class="text-end">{{ $nidn ?: '-' }}</div>
            </div>
            <div class="d-flex justify-content-between gap-3 mt-2">
              <div class="text-muted">NUPTK</div>
              <div class="text-end">{{ $nuptk ?: '-' }}</div>
            </div>
            <div class="d-flex justify-content-between gap-3 mt-2">
              <div class="text-muted">NIK</div>
              <div class="text-end">{{ $transaksi->NIK ?? '-' }}</div>
            </div>
            <div class="d-flex justify-content-between gap-3 mt-2">
              <div class="text-muted">Jabatan</div>
              <div class="text-end">{{ $jabatanTerakhir ?: '-' }}</div>
            </div>
          </div>

          <div class="col-12 col-md-6">
            <div class="d-flex justify-content-between gap-3">
              <div class="text-muted">Perguruan Tinggi</div>
              <div class="text-end fw-semibold">{{ ($kodePt ?: '-') }}{{ $pts ? ' - ' . $pts : '' }}</div>
            </div>
            <div class="d-flex justify-content-between gap-3 mt-2">
              <div class="text-muted">Jenis</div>
              <div class="text-end">{{ $transaksi->Jenis ?? '-' }}</div>
            </div>
            <div class="d-flex justify-content-between gap-3 mt-2">
              <div class="text-muted">Golongan</div>
              <div class="text-end">
                @php
                  $golMkg = trim((string) ($golTerakhir ?? ''));
                  $mkg = trim((string) ($masaKerjaTerakhir ?? ''));
                  $golDisplay = $golMkg !== '' ? $golMkg : '-';
                  if ($golMkg !== '' && $mkg !== '') {
                    $golDisplay = $golMkg . ' - ' . $mkg;
                  }
                @endphp
                {{ $golDisplay }}
              </div>
            </div>
            <div class="d-flex justify-content-between gap-3 mt-2">
              <div class="text-muted">NPWP</div>
              <div class="text-end">{{ $transaksi->NPWP ?? '-' }}</div>
            </div>
            <div class="d-flex justify-content-between gap-3 mt-2">
              <div class="text-muted">Bank</div>
              <div class="text-end">
                @php
                  $bank = trim((string) ($transaksi->Bank ?? ''));
                  $rek = trim((string) ($noRekening ?? ''));
                  $bankDisplay = $bank !== '' ? $bank : '-';
                  if ($bank !== '' && $rek !== '') {
                    $bankDisplay = $bank . ' - ' . $rek;
                  } elseif ($bank === '' && $rek !== '') {
                    $bankDisplay = $rek;
                  }
                @endphp
                {{ $bankDisplay }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-4">
    <div id="ringkasanCard">
      @include('dosen.partials.year-summary', ['selectedYear' => $selectedYear, 'totals' => $totals, 'transaksi' => $transaksi])
    </div>
  </div>
</div>


@if (session('dosen_force_password_reset'))
  <div class="modal fade" id="forceResetPasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Perbarui Password</h5>
        </div>
        <div class="modal-body">
          <div class="alert alert-warning" role="alert">
            Demi keamanan, silakan ganti password karena masih menggunakan default (NIDN/NUPTK).
          </div>

          @if (session('force_password_error'))
            <div class="alert alert-danger" role="alert">{{ session('force_password_error') }}</div>
          @endif

          <form method="POST" action="{{ route('dosen.password.update') }}" id="forceResetPasswordForm">
            @csrf

            <div class="mb-3">
              <label class="form-label">Password Baru</label>
              <input type="password" name="new_password" class="form-control" minlength="8" required autocomplete="new-password">
              <div class="form-text">Minimal 8 karakter.</div>
            </div>

            <div class="mb-3">
              <label class="form-label">Konfirmasi Password Baru</label>
              <input type="password" name="new_password_confirmation" class="form-control" minlength="8" required autocomplete="new-password">
            </div>

            <button type="submit" class="btn btn-primary w-100">
              Simpan Password Baru
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

  @section('page-script')
  <script>
    (function () {
      const el = document.getElementById('forceResetPasswordModal');
      if (!el || !window.bootstrap) return;

      const modal = new bootstrap.Modal(el, {
        backdrop: 'static',
        keyboard: false
      });
      modal.show();
    })();
  </script>
  @endsection
@endif

<script>
  (function () {
    const select = document.getElementById('tahunVersiSelect');
    if (!select) return;

    const ringkasanEl = document.getElementById('ringkasanCard');
    const nilaiEl = document.getElementById('tahunVersiValue');
    const badgeEl = document.getElementById('tahunVersiBadge');

    select.addEventListener('change', async function () {
      const val = this.value;
      const urlBase = this.dataset.ajaxUrl;
      if (!urlBase) return;

      if (ringkasanEl) {
        ringkasanEl.innerHTML = '<div class="card sptjm-kpi-card"><div class="card-body">Memuat...</div></div>';
      }

      try {
        const res = await fetch(urlBase + '?tahun_versi=' + encodeURIComponent(val), { credentials: 'same-origin' });
        if (!res.ok) throw new Error('Gagal memuat ringkasan');
        const payload = await res.json();
        if (ringkasanEl && payload.html) {
          ringkasanEl.innerHTML = payload.html;
        }

        // Update top card values if totals provided
        const fmt = (n) => {
          const num = Number(n) || 0;
          return num.toLocaleString('id-ID');
        };

        if (payload.totals) {
          const tb = document.getElementById('totalBersihValue');
          const sp2d = document.getElementById('sp2dCountValue');

          const bersih = (Number(payload.totals.bersihTpd) || 0) + (Number(payload.totals.bersihTkgb) || 0);
          if (tb) tb.textContent = fmt(bersih);
          if (sp2d) sp2d.textContent = String(Number(payload.totals.sp2dCount) || 0);
        }

        if (nilaiEl) nilaiEl.textContent = val || '-';
        if (badgeEl) badgeEl.textContent = val || '';
      } catch (e) {
        if (ringkasanEl) {
          ringkasanEl.innerHTML = '<div class="alert alert-danger">Gagal memuat ringkasan.</div>';
        }
      }
    });
  })();
</script>
@endsection
