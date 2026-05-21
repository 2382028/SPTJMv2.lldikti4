@extends('layouts/contentNavbarLayout')

@section('title', 'SPTJM Online')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@section('content')

<div class="card" style="width: 100%; padding: 10px;">
  <h5 class="card-header text-start p-2">Status Pegawai</h5>
  <hr>
  <div class="table-responsive text-nowrap">
    <div class="d-flex justify-content-end align-items-center mb-3 px-3">
      <div class="input-group me-3" id="DataTables_Table_0_filter" style="max-width: 200px;">
        <span class="input-group-text"><i class="bx bx-search"></i></span>
        <input type="search" class="form-control" id="searchInput" placeholder="Search..."
          aria-controls="DataTables_Table_0">
      </div>
      <button class="btn btn-sm btn-primary" id="addPegawaiBtn" type="button" data-bs-toggle="modal"
        data-bs-target="#modalPegawaiForm">
        <i class="bx bx-plus bx-sm me-1"></i><span class="d-none d-sm-inline-block">Tambah</span>
      </button>
    </div>

    <table class="table table-sm table-hover" id="pegawaiTable">
      <thead style="background-color: #dbdee0;">
        <tr>
          <th>Kode</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody class="table-border-bottom-0">
        @foreach ($g_pegawai as $pegawai)
        <tr>
          <td>{{ $pegawai->kode }}</td>
          <td>{{ $pegawai->jenis }}</td>
          <td>
            <button class="btn btn-sm btn-warning edit-pegawai" data-id="{{ $pegawai->kode }}"
              data-kode="{{ $pegawai->kode }}" data-jenis="{{ $pegawai->jenis }}" data-bs-toggle="modal"
              data-bs-target="#modalPegawaiForm">
              <i class="bx bx-edit"></i>
            </button>
            <form action="{{ route('admin/data-pegawai.destroy', $pegawai->kode) }}" method="POST"
              class="d-inline delete-form">
              @csrf
              @method('DELETE')
              <button type="button" class="btn btn-sm btn-danger delete-pegawai" id="confirm-text"
                data-id="{{ $pegawai->kode }}">
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
<div class="modal fade" id="modalPegawaiForm" tabindex="-1" aria-labelledby="modalPegawaiFormLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalPegawaiTitle">Tambah Status Pegawai</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="pegawaiForm" method="POST">
        @csrf
        <input type="hidden" name="_method" id="formMethod" value="POST">
        <input type="hidden" id="pegawaiId" name="id">
        <div class="modal-body">
          <div class="mb-3">
            <label>Kode</label>
            <input type="text" class="form-control" id="kode" name="kode" required>
          </div>
          <div class="mb-3">
            <label>Status</label>
            <input type="text" class="form-control" id="jenis" name="jenis" required>
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
    const pegawaiForm = document.getElementById('pegawaiForm')
    pegawaiForm.addEventListener('submit', function() {
      const method = document.getElementById('formMethod').value;

      // tutup modal
      const modalSync = document.getElementById('modalPegawaiForm');
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
    document.getElementById('addPegawaiBtn').addEventListener('click', function() {
      document.getElementById('modalPegawaiTitle').innerText = 'Tambah Status Pegawai';
      document.getElementById('pegawaiForm').reset();
      document.getElementById('formMethod').value = 'POST';
      document.getElementById('pegawaiForm').setAttribute('action',
        "{{ route('admin/data-pegawai.store') }}");
    });

    // Edit data
    document.body.addEventListener('click', function(event) {
      if (event.target.closest('.edit-pegawai')) {
        let button = event.target.closest('.edit-pegawai');
        document.getElementById('modalPegawaiTitle').innerText = 'Edit Status Pegawai';
        document.getElementById('pegawaiId').value = button.dataset.id;
        document.getElementById('kode').value = button.dataset.kode;
        document.getElementById('jenis').value = button.dataset.jenis;
        document.getElementById('formMethod').value = 'PUT';
        document.getElementById('pegawaiForm').setAttribute('action',
          `/admin/data-pegawai/${button.dataset.id}`);
      }
    });

    // SweetAlert Konfirmasi Hapus Data
    document.querySelectorAll('.delete-pegawai').forEach(button => {
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
      document.querySelectorAll("#pegawaiTable tbody tr").forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(filter) ? "" :
          "none";
      });
    });
  });
</script>
@endsection