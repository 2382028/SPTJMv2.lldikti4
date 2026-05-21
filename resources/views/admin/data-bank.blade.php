@extends('layouts/contentNavbarLayout')

@section('title', 'SPTJM Online')

@section('content')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="card" style="width: 100%; padding: 10px;">
  <h5 class="card-header text-start p-2">Data Bank</h5>
  <hr>
  <div class="table-responsive text-nowrap">
    <div class="d-flex justify-content-end align-items-center mb-3 px-3">
      <button class="btn btn-sm btn-primary" id="addBankBtn" data-bs-toggle="modal"
        data-bs-target="#modalBankForm">
        <i class="bx bx-plus bx-sm me-1"></i> Tambah
      </button>
    </div>

    <table class="table table-sm table-hover" id="bankTable">
      <thead style="background-color: #dbdee0;">
        <tr>
          <th>Kode Bank</th>
          <th>Nama Bank</th>
          <th>Aksi</th>
        </tr>
      </thead>
    </table>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalBankForm" tabindex="-1" aria-labelledby="modalBankFormLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalBankTitle">Tambah Data Bank</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="bankForm" method="POST">
        @csrf
        <input type="hidden" name="_method" id="formMethod" value="POST">
        <input type="hidden" id="bankId" name="id">
        <div class="modal-body">
          <div class="mb-3">
            <label>Kode Bank</label>
            <input type="text" class="form-control" id="kode_bank" name="kode_bank" required>
          </div>
          <div class="mb-3">
            <label>Nama Bank</label>
            <input type="text" class="form-control" id="nama_bank" name="nama_bank" required>
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
  // Reset Modal Form Saat Tambah Data Baru
  document.getElementById('addBankBtn').addEventListener('click', function() {
    document.getElementById('modalBankTitle').innerText = 'Tambah Data Bank';
    document.getElementById('bankForm').reset();
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('bankForm').setAttribute('action',
      "{{ route('admin/data-bank.store') }}");
  });

  $(document).ready(function() {
    //add bank
    //add data
    const bankForm = document.getElementById('bankForm');
    bankForm.addEventListener('submit', (e) => {
      e.preventDefault();
      //ttup modal
      const modalSync = document.getElementById('modalBankForm');
      const modalInstance = bootstrap.Modal.getInstance(modalSync);
      modalInstance.hide();
      loadingAlert()
      //ambil data
      const dataForm = new FormData(bankForm)
      const fetchingData = async () => {
        const data = await fetch(bankForm.action, {
          method: "POST",
          headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
          },
          body: dataForm
        })
        const res = await data.json()
        Swal.close()
        if (!res.success) {
          alert(res.message, 'Gagal!',
            'error',
            'btn btn-danger')
        }
        console.log(res);
        alert(res.message)
        table.ajax.reload()
      }
      fetchingData()
    })

    const table = $('#bankTable').DataTable({
      processing: true,
      serverSide: true,
      responsive: true,
      ajax: {
        url: "{{ route('admin.data-bank') }}"
      },
      columns: [{
          data: "kode_bank",
          name: "kode_bank"
        },
        {
          data: "nama_bank",
          name: "nama_bank"
        },
        {
          data: "aksi",
          name: "aksi",
          orderable: false,
          searchable: false
        }
      ],
      language: {
        paginate: {
          first: "Awal",
          last: "Akhir",
          next: "→",
          previous: "←",
        },
        zeroRecords: "Data tidak ditemukan",
        infoEmpty: "Tidak ada data tersedia",
        searchPlaceholder: "Cari data...",
        search: "Cari:"
      },
    });
    //alert
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

    const loadingAlert = () => {
      return Swal.fire({
        title: 'Mohon tunggu...',
        html: `
                        <div class="d-flex justify-content-center align-items-center flex-column">
                            <div class="spinner-border spinner-border-lg text-danger" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <div class="mt-2">Mohon tunggu <br> Sedang mengupdate data!</div>
                        </div>
                    `,
        showConfirmButton: false,
        allowOutsideClick: false,
        allowEscapeKey: false,
        backdrop: true
      });
    }

    // 🔹 Event Delegation untuk Edit
    $('#bankTable').on('click', '.edit-bank', function() {
      const bankKode = $(this).data('id');
      fetch(`/admin/data-bank/${bankKode}/edit`)
        .then(response => response.json())
        .then(data => {
          $('#modalBankTitle').text('Edit Data Bank');
          $('#bankId').val(data.kode_bank);
          $('#kode_bank').val(data.kode_bank);
          $('#nama_bank').val(data.nama_bank);
          $('#formMethod').val('PUT');
          $('#bankForm').attr('action', `/admin/data-bank/${data.kode_bank}`);
          $('#modalBankForm').modal('show'); // tampilkan modal
        });
    });

    // 🔹 Event Delegation untuk Delete
    $('#bankTable').on('click', '.delete-bank', function() {
      let form = $(this).closest('.delete-form')[0];
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
              method: "POST",
              headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
              },
              body: new FormData(form)
            })
            .then(res => res.json())
            .then(data => {
              Swal.close();
              if (!data.success) {
                alert(data.message, 'Gagal!', 'error',
                  'btn btn-danger');
              } else {
                alert(data.message);
              }
              table.ajax.reload();
            })
            .catch(err => console.error(err));
        }
      });
    });
  });
</script>
@endsection