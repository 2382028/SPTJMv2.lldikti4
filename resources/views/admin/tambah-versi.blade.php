@extends('layouts/contentNavbarLayout')

@section('title', 'SPTJM Online')

@section('content')
<div class="card" style="width: 100%; padding: 10px;">
  <h5 class="card-header text-start p-2">Tambah Versi</h5>
  <hr>
  @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif
  @if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <form method="POST" action="{{ route('admin.versi.store') }}" onsubmit="return validateForm()">
    @csrf
    <div class="row mb-4 mx-1">
      <div class="col-lg-2 col-md-4 mb-2">
        <label class="form-label">Pilih Tahun Acuan</label>
        <select id="tahun_acuan" class="form-select" name="tahun_acuan">
          @if($tahunAcuan)
            <option value="{{ $tahunAcuan }}" selected>{{ $tahunAcuan }}</option>
          @else
            <option value="" selected disabled>—</option>
          @endif
        </select>
      </div>
      <div class="col-lg-4 col-md-4 mb-2">
        <label class="form-label" for="tahun_target">Masukkan Tahun Versi</label>
        <input type="text" class="form-control" id="tahun_target" name="tahun_target" placeholder="Masukkan tahun versi" />
      </div>
      <div class="col-lg-2 col-md-4 mb-2 d-flex align-items-end">
        <button type="submit" class="btn btn-warning">
          <span class="tf-icons bx bx-loader"></span>&nbsp; Proses
        </button>
      </div>
      <div class="col-lg-3 col-md-4 mb-2 ms-auto d-flex align-items-end">

      </div>
    </div>
  </form>

  <div class="card mb-4">
    <div class="card-body">
      <div class="d-flex align-items-center justify-content-between flex-wrap">
        <h5 class="mb-3 me-3">Data Tahun Versi </h5>
        
        <div class="d-flex align-items-center gap-3 flex-wrap">
          <div class="form-check form-switch mb-2">
            <input class="form-check-input" type="checkbox" id="toggleStatusClick">
            <label class="form-check-label" for="toggleStatusClick">Aktifkan ubah status</label>
          </div>

          <!-- Search removed as requested -->
        </div>
</div>

      </div>

      <div class="table-responsive text-nowrap">
        <table class="table table-bordered text-center">
          <thead class="table-light">
            <tr>
              <th>NO</th>
              <th>TAHUN VERSI</th>
              <th>Status</th>
              <th>JUMLAH BARIS</th>
            </tr>
          </thead>
          <tbody class="table-border-bottom-0">
            @forelse ($versis as $row)
              <tr>
                <td>{{ $versis->firstItem() + $loop->index }}</td>
                <td>{{ $row->Tahun_Versi }}</td>
                <td>
                  @php $isActive = in_array($row->Tahun_Versi, $activeYears ?? []); @endphp
                  <button type="button" class="btn btn-sm toggle-status-btn {{ $isActive ? 'btn-success' : 'btn-secondary' }}" data-year="{{ $row->Tahun_Versi }}">
                    {{ $isActive ? 'Aktif' : 'Non-aktif' }}
                  </button>
                </td>
                <td>{{ $row->jumlah }}</td>
              </tr>
            @empty
              <tr><td colspan="4" class="text-center">Data tidak ditemukan</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="mt-3">{{ $versis->links() }}</div>
    </div>
  </div>
</div>
@endsection

@section('page-script')
<script>
  function validateForm() {
    const acuan = document.getElementById('tahun_acuan').value;
    const target = document.getElementById('tahun_target').value;
    if (!acuan || !target) { alert('Isi Tahun Acuan dan Target sebelum proses!'); return false; }
    if (isNaN(acuan) || isNaN(target)) { alert('Tahun Acuan dan Tahun Target hanya menerima angka.'); return false; }
    if (String(target).length !== 4) { alert('Tahun target harus 4 digit.'); return false; }
    return true;
  }
  document.addEventListener('DOMContentLoaded', function () {
    const toggle = document.getElementById('toggleStatusClick');
    function applyToggle() {
      const enable = toggle.checked;
      document.querySelectorAll('.toggle-status-btn').forEach(btn => {
        btn.disabled = !enable;
        if (enable) {
          btn.classList.remove('disabled');
        } else {
          btn.classList.add('disabled');
        }
      });
    }

    const buttons = document.querySelectorAll('.toggle-status-btn');
    buttons.forEach(btn => {
      btn.addEventListener('click', async function () {
        if (this.disabled) return;
        const year = this.getAttribute('data-year');
        try {
          const resp = await fetch("{{ route('admin.versi.toggle-status') }}", {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ tahun: year })
          });
          const data = await resp.json();
          if (!resp.ok || !data.success) throw new Error(data.message || 'Gagal mengubah status');
          // Update button style/text
          if (data.active) {
            this.classList.remove('btn-secondary');
            this.classList.add('btn-success');
            this.textContent = 'Aktif';
          } else {
            this.classList.remove('btn-success');
            this.classList.add('btn-secondary');
            this.textContent = 'Non-aktif';
          }
        } catch (e) {
          alert(e.message || 'Terjadi kesalahan saat mengubah status.');
        }
      });
    });

    // Default: non-aktif sampai diaktifkan oleh user
    if (toggle) {
      applyToggle();
      toggle.addEventListener('change', applyToggle);
    }
    // Show loading modal on form submit (long-running process)
    (function() {
      var form = document.querySelector('form[action="{{ route('admin.versi.store') }}"]');
      if (!form) return;
      form.addEventListener('submit', function (e) {
        // validateForm is already used via onsubmit; if it returns false, prevent showing modal
        if (typeof validateForm === 'function' && !validateForm()) {
          e.preventDefault();
          return false;
        }

        var btn = form.querySelector('button[type="submit"]');
        if (btn) {
          btn.disabled = true;
          btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> &nbsp; Memproses...';
        }

        var el = document.getElementById('tambahVersiProgressModal');
        if (el) {
          if (window.bootstrap && bootstrap.Modal) {
            var modal = new bootstrap.Modal(el, { backdrop: 'static', keyboard: false });
            modal.show();
          } else {
            el.classList.add('show');
            el.style.display = 'block';
          }
        }
      });
    })();
  });
</script>
@endsection

{{-- Modal progress tambah versi --}}
<div class="modal fade" id="tambahVersiProgressModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Membuat Versi</h5>
      </div>
      <div class="modal-body">
        <p class="mb-3">Proses pembuatan versi sedang berjalan. Mohon tunggu hingga selesai…</p>
        <div class="progress" role="progressbar" aria-label="Processing" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">
          <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 100%"></div>
        </div>
      </div>
      <div class="modal-footer">
        <small class="text-muted">Jangan tutup halaman ini selama proses berlangsung.</small>
      </div>
    </div>
  </div>
</div>
