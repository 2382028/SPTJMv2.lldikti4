@extends('layouts/contentNavbarLayout')

@section('title', 'SPTJM Online')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@section('content')

<div class="card" style="width: 100%; padding: 10px;">
  <h5 class="card-header text-start p-2">Status Keaktifan</h5>
  <hr>
  <div class="table-responsive text-nowrap">
    <div class="d-flex justify-content-end align-items-center mb-3 px-3">
      <div class="input-group me-3" id="DataTables_Table_0_filter" style="max-width: 200px;">
        <span class="input-group-text"><i class="bx bx-search"></i></span>
        <input type="search" class="form-control" id="searchInput" placeholder="Search..."
          aria-controls="DataTables_Table_0">
      </div>
      <button class="btn btn-sm btn-primary" id="addKeaktifanBtn" type="button" data-bs-toggle="modal"
        data-bs-target="#modalKeaktifanForm">
        <i class="bx bx-plus bx-sm me-1"></i><span class="d-none d-sm-inline-block">Tambah</span>
      </button>
    </div>

    <table class="table table-sm table-hover" id="keaktifanTable">
      <thead style="background-color: #dbdee0;">
        <tr>
          <th>Kode</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody class="table-border-bottom-0">
        @foreach ($f_keaktifan as $keaktifan)
        <tr>
          <td>{{ $keaktifan->kode }}</td>
          <td>{{ $keaktifan->aktif }}</td>
          <td>
            <button class="btn btn-sm btn-warning edit-keaktifan" data-id="{{ $keaktifan->kode }}"
              data-kode="{{ $keaktifan->kode }}" data-aktif="{{ $keaktifan->aktif }}"
              data-bs-toggle="modal" data-bs-target="#modalKeaktifanForm">
              <i class="bx bx-edit"></i>
            </button>
            <form action="{{ route('admin/data-keaktifan.destroy', $keaktifan->kode) }}" method="POST"
              class="d-inline delete-form">
              @csrf
              @method('DELETE')
              <button type="button" class="btn btn-sm btn-danger delete-keaktifan" id="confirm-text"
                data-id="{{ $keaktifan->kode }}">
                <i class="bx bx-trash"></i>
              </button>
            </form>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalKeaktifanForm" tabindex="-1" aria-labelledby="modalKeaktifanFormLabel"
  aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalKeaktifanTitle">Tambah Status Keaktifan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="keaktifanForm" method="POST">
        @csrf
        <input type="hidden" name="_method" id="formMethod" value="POST">
        <input type="hidden" id="keaktifanId" name="id">
        <div class="modal-body">
          <div class="mb-3">
            <label>Kode</label>
            <input type="text" class="form-control" id="kode" name="kode" required>
          </div>
          <div class="mb-3">
            <label>Status</label>
            <input type="text" class="form-control" id="aktif" name="aktif" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Simpan</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    // SweetAlert untuk Notifikasi Sukses
    const loadingAlert = (message) => {
      return Swal.fire({
        title: 'Mohon tunggu...',
        html: `
                        <div class="d-flex justify-content-center align-items-center flex-column">
                            <div class='spinner-border spinner-border-lg ${message? 'text-success':'text-danger'}' role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <div class="mt-2">Mohon tunggu <br>${message ?? 'Sedang menghapus data!'}</div>
                        </div>
                    `,
        showConfirmButton: false,
        allowOutsideClick: false,
        allowEscapeKey: false,
        backdrop: true
      });
    }

    keaktifanForm.addEventListener('submit', function() {
      const method = document.getElementById('formMethod').value;

      // tutup modal
      const modalSync = document.getElementById('modalKeaktifanForm');
      const modalInstance = bootstrap.Modal.getInstance(modalSync);
      if (modalInstance) modalInstance.hide();

      method === "POST" ?
        loadingAlert("Sedang menyimpan data!") :
        loadingAlert("Sedang mengupdate data!");
    });

    // SweetAlert untuk Notifikasi Sukses
    @if(session('success'))
    Swal.fire({
      title: 'Berhasil!',
      text: "{{ session('success') }}",
      icon: 'success',
      customClass: {
        confirmButton: 'btn btn-primary'
      },
      buttonsStyling: false
    });
    @endif

    // Reset Modal Form Saat Tambah Data Baru
    document.getElementById('addKeaktifanBtn').addEventListener('click', function() {
      document.getElementById('modalKeaktifanTitle').innerText = 'Tambah Status Keaktifan';
      document.getElementById('keaktifanForm').reset();
      document.getElementById('formMethod').value = 'POST';
      document.getElementById('keaktifanForm').setAttribute('action',
        "{{ route('admin/data-keaktifan.store') }}");
    });

    // Edit data
    document.body.addEventListener('click', function(event) {
      if (event.target.closest('.edit-keaktifan')) {
        let button = event.target.closest('.edit-keaktifan');

        document.getElementById('modalKeaktifanTitle').innerText = 'Edit Status Keaktifan';
        document.getElementById('keaktifanId').value = button.dataset.id;
        document.getElementById('kode').value = button.dataset.kode;
        document.getElementById('aktif').value = button.dataset.aktif;
        document.getElementById('formMethod').value = 'PUT';
        document.getElementById('keaktifanForm').setAttribute('action',
          `/admin/data-keaktifan/${button.dataset.id}`);
      }
    });

    // SweetAlert Konfirmasi Hapus Data
    document.querySelectorAll('.delete-keaktifan').forEach(button => {
      button.addEventListener('click', function() {
        let form = this.closest('.delete-form');
        Swal.fire({
          title: 'Apakah Anda Yakin?',
          text: "Data yang dihapus tidak bisa dikembalikan!",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#d33',
          cancelButtonColor: '#6c757d',
          confirmButtonText: 'Ya, Hapus!',
          cancelButtonText: 'Batal',
          customClass: {
            confirmButton: 'btn btn-danger me-1',
            cancelButton: 'btn btn-secondary'
          },
          buttonsStyling: false
        }).then((result) => {
          if (result.isConfirmed) {
            loadingAlert()
            form.submit();
          }
        });
      });
    });

    // Fitur Pencarian
    document.getElementById("searchInput").addEventListener("keyup", function() {
      var filter = this.value.toLowerCase();
      document.querySelectorAll("#keaktifanTable tbody tr").forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(filter) ? "" :
          "none";
      });
    });
  });
</script>
@endsection