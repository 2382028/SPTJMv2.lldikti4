@extends('layouts/contentNavbarLayout')

@section('title', 'SPTJM Online')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@section('content')

<div class="card" style="width: 100%; padding: 10px;">
    <h5 class="card-header text-start p-2">Data Pajak</h5>
    <hr>
    <div class="table-responsive text-nowrap">
        <div class="d-flex justify-content-end align-items-center mb-3 px-3">
            <div class="input-group me-3" id="DataTables_Table_0_filter" style="max-width: 200px;">
                <span class="input-group-text"><i class="bx bx-search"></i></span>
                <input type="search" class="form-control" id="searchInput" placeholder="Search..."
                    aria-controls="DataTables_Table_0">
            </div>
            <div class="dropdown">
                <button class="btn btn-sm btn-primary dropdown-toggle" type="button" id="addDropdownBtn"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bx bx-plus bx-sm me-1"></i><span class="d-none d-sm-inline-block">Tambah</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="addDropdownBtn">
                    <li>
                        <button type="button" class="dropdown-item" id="addPajakMenu">Data Pajak</button>
                    </li>
                    <li>
                        <button type="button" class="dropdown-item" id="addPemotongMenu">Identitas Pemotong</button>
                    </li>
                </ul>
            </div>
        </div>

        <table class="table table-sm table-hover" id="pajakTable">
            <thead style="background-color: #dbdee0;">
                <tr>
                    <th>No</th>
                    <th>Status</th>
                    <th>Akumulasi</th>
                    <th>Tarif Pajak</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @foreach ($d_pajak as $index => $pajak)
                <tr>
                    <td>{{ $index + 1 }}</td> <!-- Menampilkan nomor urut -->
                    <td>{{ $pajak->status }}</td>
                    <td>{{ $pajak->akumulasi }}</td>
                    <td>{{ number_format($pajak->tarif_pajak, 2) }}%</td>
                    <td>
                        <button class="btn btn-sm btn-warning edit-pajak" data-id="{{ $pajak->no }}"
                            data-status="{{ $pajak->status }}" data-akumulasi="{{ $pajak->akumulasi }}"
                            data-tarif_pajak="{{ $pajak->tarif_pajak }}" data-bs-toggle="modal"
                            data-bs-target="#modalPajakForm">
                            <i class="bx bx-edit"></i>
                        </button>
                        <form action="{{ route('admin/data-pajak.destroy', $pajak->no) }}" method="POST"
                            class="d-inline delete-form">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-sm btn-danger delete-pajak">
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

<div class="card mt-3" style="width: 100%; padding: 10px;">
    <h5 class="card-header text-start p-2">Identitas Pemotong</h5>
    <hr>
    <div class="table-responsive text-nowrap">
        <table class="table table-sm table-hover" id="pemotongTable">
            <thead style="background-color: #dbdee0;">
                <tr>
                    <th>No</th>
                    <th>NPWP</th>
                    <th>Nama</th>
                    <th>Tanggal</th>
                    <th>Tanda Tangan</th>
                    <th>Cap</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @foreach (($identitas_pemotong ?? []) as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item['npwp'] ?? '-' }}</td>
                    <td>{{ $item['nama'] ?? '-' }}</td>
                    <td>{{ $item['tanggal'] ?? '-' }}</td>
                    <td>
                        @if(!empty($item['tanda_tangan_path']))
                        <img src="{{ asset('storage/' . $item['tanda_tangan_path']) }}" alt="Tanda Tangan"
                            style="height: 40px; width: 80px; object-fit: contain;" />
                        @else
                        -
                        @endif
                    </td>
                    <td>
                        @if(!empty($item['cap_path']))
                        <img src="{{ asset('storage/' . $item['cap_path']) }}" alt="Cap"
                            style="height: 40px; width: 40px; object-fit: contain;" />
                        @else
                        -
                        @endif
                    </td>
                    <td>
                        <button class="btn btn-sm btn-warning edit-pemotong" data-id="{{ $item['id'] ?? '' }}"
                            data-npwp="{{ $item['npwp'] ?? '' }}" data-nama="{{ $item['nama'] ?? '' }}"
                            data-tanggal="{{ $item['tanggal'] ?? '' }}"
                            data-ttd="{{ !empty($item['tanda_tangan_path']) ? asset('storage/' . $item['tanda_tangan_path']) : '' }}"
                            data-cap="{{ !empty($item['cap_path']) ? asset('storage/' . $item['cap_path']) : '' }}">
                            <i class="bx bx-edit"></i>
                        </button>
                        <form action="{{ route('admin/data-pajak.identitas-pemotong.destroy', $item['id'] ?? '') }}"
                            method="POST" class="d-inline delete-form-pemotong">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-sm btn-danger delete-pemotong">
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

<!-- Modal Tambah/Edit Pajak -->
<div class="modal fade" id="modalPajakForm" tabindex="-1" aria-labelledby="modalPajakFormLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalPajakTitle">Tambah Data Pajak</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="pajakForm" method="POST" action="{{ route('admin/data-pajak.store') }}">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <input type="hidden" id="pajakId" name="no">
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Status</label>
                        <input type="text" class="form-control" id="status" name="status" required>
                    </div>
                    <div class="mb-3">
                        <label>Akumulasi</label>
                        <input type="text" class="form-control" id="akumulasi" name="akumulasi" required>
                    </div>
                    <div class="mb-3">
                        <label>Tarif Pajak</label>
                        <input type="number" class="form-control" id="tarif_pajak" name="tarif_pajak" step="0.01"
                            required>
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

<!-- Modal Tambah/Edit Identitas Pemotong -->
<div class="modal fade" id="modalPemotongForm" tabindex="-1" aria-labelledby="modalPemotongFormLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalPemotongTitle">Tambah Identitas Pemotong</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="pemotongForm" method="POST" action="{{ route('admin/data-pajak.identitas-pemotong.store') }}"
                enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="_method" id="pemotongFormMethod" value="POST">
                <input type="hidden" id="pemotongId" name="id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label>NPWP</label>
                        <input type="text" class="form-control" id="pemotong_npwp" name="npwp" required>
                    </div>
                    <div class="mb-3">
                        <label>Nama</label>
                        <input type="text" class="form-control" id="pemotong_nama" name="nama" required>
                    </div>
                    <div class="mb-3">
                        <label>Tanggal</label>
                        <input type="date" class="form-control" id="pemotong_tanggal" name="tanggal">
                    </div>
                    <div class="mb-3">
                        <label>Tanda Tangan (PNG)</label>
                        <input type="file" class="form-control" id="pemotong_ttd" name="tanda_tangan" accept="image/png" required>
                        <div class="form-text" id="pemotong_ttd_help">Format PNG.</div>
                    </div>
                    <div class="mb-2" id="pemotong_ttd_preview_wrapper" style="display:none;">
                        <label class="form-label">Preview</label>
                        <div>
                            <img id="pemotong_ttd_preview" src="" alt="Preview" style="height: 60px; width: 120px; object-fit: contain;" />
                        </div>
                    </div>

                    <div class="mb-3">
                        <label>Cap (PNG)</label>
                        <input type="file" class="form-control" id="pemotong_cap" name="cap" accept="image/png" required>
                        <div class="form-text" id="pemotong_cap_help">Format PNG.</div>
                    </div>
                    <div class="mb-2" id="pemotong_cap_preview_wrapper" style="display:none;">
                        <label class="form-label">Preview</label>
                        <div>
                            <img id="pemotong_cap_preview" src="" alt="Preview" style="height: 60px; width: 60px; object-fit: contain;" />
                        </div>
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
    const pajakForm = document.getElementById('pajakForm');
    pajakForm.addEventListener('submit', function() {
        const method = document.getElementById('formMethod').value
        console.log(method);
        //ttup modal
        const modalSync = document.getElementById('modalPajakForm');
        const modalInstance = bootstrap.Modal.getInstance(modalSync);
        modalInstance.hide();
        method == "POST" ? loadingAlert("Sedang menyimpan data!") : loadingAlert(
            "Sedang mengupdate data!");
    });

    // SweetAlert untuk Notifikasi Sukses
    @if(session('success'))
    Swal.close()
    Swal.fire({
        title: 'Berhasil!',
        text: "{{ session('success') }}",
        icon: 'success',
    });
    @endif

    @if(session('error'))
    Swal.close()
    Swal.fire({
        title: 'Gagal!',
        text: "{{ session('error') }}",
        icon: 'error',
    });
    @endif

    const openModal = (modalId) => {
        const el = document.getElementById(modalId);
        const instance = new bootstrap.Modal(el);
        instance.show();
    };

    // Dropdown Tambah: Data Pajak
    document.getElementById('addPajakMenu').addEventListener('click', function() {
        document.getElementById('modalPajakTitle').innerText = 'Tambah Data Pajak';
        document.getElementById('pajakForm').reset();
        document.getElementById('formMethod').value = 'POST';
        document.getElementById('pajakForm').setAttribute('action',
            "{{ route('admin/data-pajak.store') }}");
        openModal('modalPajakForm');
    });

    // Dropdown Tambah: Identitas Pemotong
    document.getElementById('addPemotongMenu').addEventListener('click', function() {
        document.getElementById('modalPemotongTitle').innerText = 'Tambah Identitas Pemotong';
        document.getElementById('pemotongForm').reset();
        document.getElementById('pemotongFormMethod').value = 'POST';
        document.getElementById('pemotongForm').setAttribute('action',
            "{{ route('admin/data-pajak.identitas-pemotong.store') }}");

        document.getElementById('pemotong_ttd_help').innerText = 'Format PNG.';
        document.getElementById('pemotong_ttd_preview_wrapper').style.display = 'none';
        document.getElementById('pemotong_ttd_preview').src = '';

        document.getElementById('pemotong_cap_help').innerText = 'Format PNG.';
        document.getElementById('pemotong_cap_preview_wrapper').style.display = 'none';
        document.getElementById('pemotong_cap_preview').src = '';

        openModal('modalPemotongForm');
    });

    // Edit data
    document.body.addEventListener('click', function(event) {
        if (event.target.closest('.edit-pajak')) {
            let button = event.target.closest('.edit-pajak');
            document.getElementById('modalPajakTitle').innerText = 'Edit Data Pajak';
            document.getElementById('pajakId').value = button.dataset.id;
            document.getElementById('status').value = button.dataset.status;
            document.getElementById('akumulasi').value = button.dataset.akumulasi;
            document.getElementById('tarif_pajak').value = button.dataset.tarif_pajak;
            document.getElementById('formMethod').value = 'PUT';
            document.getElementById('pajakForm').setAttribute('action',
                `/admin/data-pajak/${button.dataset.id}`);
        }
    });

    // Submit form Identitas Pemotong
    const pemotongForm = document.getElementById('pemotongForm');
    pemotongForm.addEventListener('submit', function() {
        const method = document.getElementById('pemotongFormMethod').value;
        const modalEl = document.getElementById('modalPemotongForm');
        const modalInstance = bootstrap.Modal.getInstance(modalEl);
        if (modalInstance) {
            modalInstance.hide();
        }
        method === 'POST' ? loadingAlert('Sedang menyimpan data!') : loadingAlert('Sedang mengupdate data!');
    });

    // Edit Identitas Pemotong
    document.body.addEventListener('click', function(event) {
        if (event.target.closest('.edit-pemotong')) {
            const button = event.target.closest('.edit-pemotong');
            const id = button.dataset.id;

            document.getElementById('modalPemotongTitle').innerText = 'Edit Identitas Pemotong';
            document.getElementById('pemotongId').value = id;
            document.getElementById('pemotong_npwp').value = button.dataset.npwp || '';
            document.getElementById('pemotong_nama').value = button.dataset.nama || '';
            document.getElementById('pemotong_tanggal').value = button.dataset.tanggal || '';

            document.getElementById('pemotongFormMethod').value = 'PUT';
            document.getElementById('pemotongForm').setAttribute('action',
                `/admin/data-pajak/identitas-pemotong/${id}`);

            // tanda tangan opsional saat edit
            document.getElementById('pemotong_ttd_help').innerText = 'Kosongkan jika tidak diganti (PNG).';

            const ttdUrl = button.dataset.ttd || '';
            if (ttdUrl) {
                document.getElementById('pemotong_ttd_preview').src = ttdUrl;
                document.getElementById('pemotong_ttd_preview_wrapper').style.display = 'block';
            } else {
                document.getElementById('pemotong_ttd_preview_wrapper').style.display = 'none';
                document.getElementById('pemotong_ttd_preview').src = '';
            }

            // cap opsional saat edit
            document.getElementById('pemotong_cap_help').innerText = 'Kosongkan jika tidak diganti (PNG).';

            const capUrl = button.dataset.cap || '';
            if (capUrl) {
                document.getElementById('pemotong_cap_preview').src = capUrl;
                document.getElementById('pemotong_cap_preview_wrapper').style.display = 'block';
            } else {
                document.getElementById('pemotong_cap_preview_wrapper').style.display = 'none';
                document.getElementById('pemotong_cap_preview').src = '';
            }

            openModal('modalPemotongForm');
        }
    });

    // Preview tanda tangan saat pilih file
    document.getElementById('pemotong_ttd').addEventListener('change', function() {
        const file = this.files && this.files[0];
        if (!file) {
            return;
        }
        const url = URL.createObjectURL(file);
        document.getElementById('pemotong_ttd_preview').src = url;
        document.getElementById('pemotong_ttd_preview_wrapper').style.display = 'block';
    });

    // Preview cap saat pilih file
    document.getElementById('pemotong_cap').addEventListener('change', function() {
        const file = this.files && this.files[0];
        if (!file) {
            return;
        }
        const url = URL.createObjectURL(file);
        document.getElementById('pemotong_cap_preview').src = url;
        document.getElementById('pemotong_cap_preview_wrapper').style.display = 'block';
    });

    // Filter data pencarian
    document.getElementById("searchInput").addEventListener("keyup", function() {
        var filter = this.value.toLowerCase();
        var rows = document.querySelectorAll("#pajakTable tbody tr");
        rows.forEach(function(row) {
            var text = row.textContent.toLowerCase();
            row.style.display = text.includes(filter) ? "" : "none";
        });
    });

    // SweetAlert konfirmasi hapus
    document.querySelectorAll('.delete-pajak').forEach(button => {
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

    // SweetAlert konfirmasi hapus Identitas Pemotong
    document.querySelectorAll('.delete-pemotong').forEach(button => {
        button.addEventListener('click', function() {
            let form = this.closest('.delete-form-pemotong');
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
                    form.submit();
                }
            });
        });
    });
});
</script>
@endsection
