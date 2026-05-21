@extends('layouts/contentNavbarLayout')

@section('title', 'SPTJM Online')

@section('content')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="card" style="width: 100%; padding: 10px;">
  <h5 class="card-header text-start p-2">Data Grade Serdos</h5>
  <hr>
  <div class="table-responsive text-nowrap">
    <div class="d-flex justify-content-end align-items-center mb-3 px-3">
      <button class="btn btn-sm btn-primary" type="button" id="addGradeBtn" data-bs-toggle="modal"
        data-bs-target="#modalGradeForm">
        <i class="bx bx-plus bx-sm me-1"></i> Tambah
      </button>
    </div>

    <table class="table table-sm table-hover" id="gradeTable">
      <thead style="background-color: #dbdee0;">
        <tr>
          <th>Jabatan</th>
          <th>Masa Kerja Bawah</th>
          <th>Masa Kerja Atas</th>
          <th>Golongan</th>
          <th>Aksi</th>
        </tr>
      </thead>
    </table>
  </div>
</div>

<!-- Modal Tambah/Edit Grade -->
<div class="modal fade" id="modalGradeForm" tabindex="-1" aria-labelledby="modalGradeFormLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalGradeTitle">Tambah Data Grade</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="gradeForm" method="POST">
        @csrf
        <input type="hidden" name="_method" id="formMethod" value="POST">
        <input type="hidden" id="gradeId" name="id">
        <div class="modal-body">
          <div class="mb-3">
            <label>Jabatan</label>
            <input type="text" class="form-control" id="jabatan" name="jabatan" required>
          </div>
          <div class="mb-3">
            <label>Masa Kerja Bawah</label>
            <input type="number" class="form-control" id="masa_kerja_bawah" name="masa_kerja_bawah" required>
          </div>
          <div class="mb-3">
            <label>Masa Kerja Atas</label>
            <input type="number" class="form-control" id="masa_kerja_atas" name="masa_kerja_atas" required>
          </div>
          <div class="mb-3">
            <label>Golongan</label>
            <input type="text" class="form-control" id="golongan" name="golongan" required>
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
    //ini alert
    const alert = (text = "Data berhasil tersimpan!", title = "Berhasil", icon = "success",
      warnaBtn =
      "btn btn-primary") => {
      return Swal.fire({
        title,
        text: text,
        icon: icon,
        confirmButtonText: 'OK',
        timer: 1500,
        timerProgressBar: true,
        customClass: {
          confirmButton: warnaBtn
        },
        buttonsStyling: false
      })
    }

    const loadingAlert = (message) => {
      return Swal.fire({
        title: 'Mohon tunggu...',
        html: `
                        <div class="d-flex justify-content-center align-items-center flex-column">
                            <div class="spinner-border spinner-border-lg text-danger" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <div class="mt-2">Mohon tunggu <br> ${message ?? 'Sedang menghapus data!' }</div>
                        </div>
                    `,
        showConfirmButton: false,
        allowOutsideClick: false,
        allowEscapeKey: false,
        backdrop: true
      });
    }

    // Reset modal saat tambah data
    document.getElementById('addGradeBtn').addEventListener('click', function() {
      document.getElementById('modalGradeTitle').innerText = 'Tambah Data Grade Serdos';
      document.getElementById('gradeForm').reset();
      document.getElementById('formMethod').value = 'POST';
      document.getElementById('gradeForm').setAttribute('action',
        "{{ route('admin.grade-serdos.store') }}");
    });

    // add
    const gradeForm = document.getElementById('gradeForm');
    gradeForm.addEventListener('submit', function(e) {
      e.preventDefault();
      const modalSync = document.getElementById('modalGradeForm');
      const modalInstance = bootstrap.Modal.getInstance(modalSync);
      if (modalInstance) modalInstance.hide();
      const dataForm = new FormData(gradeForm);
      const method = document.getElementById('formMethod').value
      method == "POST" ? loadingAlert('Sedang menyimpan data!') : loadingAlert(
        'Sedang mengupdate data!')
      fetch(gradeForm.action, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          },
          body: dataForm
        })
        .then(async (res) => {
          let data = {};
          try {
            data = await res.json();
          } catch (e) {
            data = {};
          }

          if (res.ok && data && data.success) {
            await Swal.fire({
              title: 'Berhasil',
              text: data.message || 'Berhasil menyimpan data.',
              icon: 'success',
              timer: 1500,
              showConfirmButton: false
            });
            table.ajax.reload();
            return;
          }

          // Build a nicer validation message (Laravel 422)
          let msg = (data && data.message) ? data.message : 'Terjadi kesalahan.';
          if (data && data.errors) {
            const firstKey = Object.keys(data.errors)[0];
            if (firstKey && data.errors[firstKey] && data.errors[firstKey][0]) {
              msg = data.errors[firstKey][0];
            }
          }

          return Swal.fire({
            title: 'Gagal',
            text: msg,
            icon: 'error'
          });
        })
        .catch(err => {
          console.error(err);
          Swal.fire('Gagal', 'Terjadi kesalahan saat menyimpan data.', 'error');
        })
        .finally(() => {
          Swal.close();
        });
    });

    //data table
    const table = $('#gradeTable').DataTable({
      processing: true,
      serverSide: true,
      responsive: true,
      pageLength: 25,
      lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
      ajax: {
        url: "{{ route('admin.grade-serdos.index') }}"
      },
      columns: [
        { data: 'jabatan', name: 'jabatan' },
        { data: 'masa_kerja_bawah', name: 'masa_kerja_bawah' },
        { data: 'masa_kerja_atas', name: 'masa_kerja_atas' },
        { data: 'golongan', name: 'golongan' },
        { data: 'aksi', name: 'aksi', orderable: false, searchable: false }
      ],
      language: {
        paginate: {
          first: 'Awal',
          last: 'Akhir',
          next: '→',
          previous: '←',
        },
        zeroRecords: 'Data tidak ditemukan',
        infoEmpty: 'Tidak ada data tersedia',
        searchPlaceholder: 'Cari data...',
        search: 'Cari:'
      },
    });

    // edit
    $('#gradeTable').on('click', '.edit-grade', function() {
      const id = $(this).data('id');
      fetch(`/admin/grade-serdos/${id}/edit`).then(res => res.json()).then(data => {
        $('#modalGradeTitle').text('Edit Data Grade Serdos');
        $('#gradeId').val(data.id);
        $('#jabatan').val(data.jabatan);
        $('#masa_kerja_bawah').val(data.masa_kerja_bawah);
        $('#masa_kerja_atas').val(data.masa_kerja_atas);
        $('#golongan').val(data.golongan);
        $('#formMethod').val('PUT');
        $('#gradeForm').attr('action', `/admin/grade-serdos/${data.id}`);
        $('#modalGradeForm').modal('show');
      });
    });

    //hapus
    $('#gradeTable').on('click', '.delete-grade', function() {
      const form = $(this).closest('.delete-form')[0];
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
          loadingAlert();
          fetch(form.action, {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': '{{ csrf_token() }}',
              'X-Requested-With': 'XMLHttpRequest',
              'Accept': 'application/json'
            },
            body: new FormData(form)
          })
            .then(res => res.json())
            .then(data => {
              if (!data.success) return Swal.fire('Gagal', data.message || 'Terjadi kesalahan', 'error');
              Swal.fire({
                title: 'Berhasil',
                text: data.message,
                icon: 'success',
                timer: 1500,
                showConfirmButton: false
              });
              table.ajax.reload();
            })
            .catch(err => {
              console.error(err);
              Swal.fire('Gagal', 'Terjadi kesalahan saat menghapus data.', 'error');
            })
            .finally(() => {
              Swal.close();
            });
        }
      });
    });
  });
</script>
@endsection