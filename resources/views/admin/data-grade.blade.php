@extends('layouts/contentNavbarLayout')

@section('title', 'SPTJM Online')

@section('content')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="card" style="width: 100%; padding: 10px;">
  <h5 class="card-header text-start p-2">Data Grade</h5>
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
          <th>Kode</th>
          <th>Golongan</th>
          <th>Masa Kerja</th>
          <th>Nominal</th>
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
            <label>Kode</label>
            <input type="text" class="form-control" id="kode" name="kode" required>
          </div>
          <div class="mb-3">
            <label>Golongan</label>
            <input type="text" class="form-control" id="gol" name="gol" required>
          </div>
          <div class="mb-3">
            <label>Masa Kerja</label>
            <input type="number" class="form-control" id="masa_kerja" name="masa_kerja" required>
          </div>
          <div class="mb-3">
            <label>Nominal</label>
            <input type="number" class="form-control" id="nominal" name="nominal" required>
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
      document.getElementById('modalGradeTitle').innerText = 'Tambah Data Grade';
      document.getElementById('gradeForm').reset();
      document.getElementById('formMethod').value = 'POST';
      document.getElementById('gradeForm').setAttribute('action',
        "{{ route('admin/data-grade.store') }}");
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
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
          },
          body: dataForm
        })
        .then(res => res.json())
        .then(res => {
          console.log(res);
          Swal.close();
          if (!res.success) return Swal.fire('Gagal', res.message, 'error');
          Swal.fire({
            title: 'Berhasil',
            text: res.message,
            icon: 'success',
            timer: 1500,
            showConfirmButton: false
          });
          table.ajax.reload();
        }).catch(err => console.error(err));
      // try {
      //   const fetchingData = async () => {
      //     const data = await fetch(gradeForm.action, {
      //       method: 'POST',
      //       headers: {
      //         'x-CSRF-TOKEN': "{{ csrf_token() }}"
      //       },
      //       body: dataForm
      //     })
      //     const res = await data.json()
      //     console.log(res);
      //     Swal.close()
      //     if (!res.success) {
      //       method == "POST" ? alert("Gagal menyimpan data!", "Gagal", "error",
      //         "btn btn-warning") : alert(
      //         "Gagal mengupdate data!", "Gagal", "error", "btn btn-warning")
      //     }
      //     method == "POST" ? alert("Berhasil Menyimpan data!") : alert(
      //       "Berhasil mengupdate data!")
      //   }
      // } catch (error) {
      //   console.log(error.message);
      // }
    });

    //data table
    const table = $('#gradeTable').DataTable({
      processing: true,
      serverSide: true,
      responsive: true,
      ajax: {
        url: "{{ route('admin/data-grade') }}"
      },
      columns: [{
          data: 'kode',
          name: 'kode'
        },
        {
          data: 'gol',
          name: 'gol'
        },
        {
          data: 'masa_kerja',
          name: 'masa_kerja'
        },
        {
          data: 'nominal',
          name: 'nominal'
        },
        {
          data: 'aksi',
          name: 'aksi',
          orderable: false,
          searchable: false
        }
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
      const kode = $(this).data('id');
      fetch(`/admin/data-grade/${kode}/edit`).then(res => res.json()).then(data => {
        $('#modalGradeTitle').text('Edit Data Grade');
        $('#gradeId').val(data.kode);
        $('#kode').val(data.kode);
        $('#gol').val(data.gol);
        $('#masa_kerja').val(data.masa_kerja);
        $('#nominal').val(data.nominal);
        $('#formMethod').val('PUT');
        $('#gradeForm').attr('action', `/admin/data-grade/${data.kode}`);
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
              'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: new FormData(form)
          }).then(res => res.json()).then(data => {
            Swal.close();
            if (!data.success) return Swal.fire('Gagal', data.message, 'error');
            Swal.fire({
              title: 'Berhasil',
              text: data.message,
              icon: 'success',
              timer: 1500,
              showConfirmButton: false
            });
            table.ajax.reload();
          }).catch(err => console.error(err));
        }
      });
    });
  });
</script>
@endsection