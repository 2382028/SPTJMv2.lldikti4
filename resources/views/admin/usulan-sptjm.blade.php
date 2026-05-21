@extends('layouts/contentNavbarLayout')

@section('title', 'SPTJM Online')

@section('content')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="card" style="width: 100%; padding: 10px;">
    <h5 class="card-header text-start p-2">Usulan SPTJM</h5>
    <hr>

    <form id="filterForm" method="POST">
        @csrf
        <div class="row align-items-center" style="padding: 20px;">
            <!-- Pilih Tipe SPTJM -->
            <div class="col-lg-3 col-md-4 mb-2 mb-md-0">
                <label class="form-label" for="pilihsptjm">Pilih Tipe SPTJM</label>
                <select id="pilihsptjm" class="form-select" name="pilihsptjm">
                    <option value="SPTJM Berjalan">SPTJM Berjalan</option>
                    <option value="SPTJM Susulan">SPTJM Susulan</option>
                    <option value="TUKIN Berjalan">TUKIN Berjalan</option>
                    <option value="TUKIN Susulan">TUKIN Susulan</option>
                </select>
            </div>

            <!-- Pilih Bulan -->
            <div class="col-lg-2 col-md-3 mb-2 mb-md-0">
                <label class="form-label" for="selectTypeOptBulan">Bulan</label>
                <select name="bulan" id="selectTypeOptBulan" class="form-select">
                    <option value="All">All</option>
                    <option value="Januari">Januari</option>
                    <option value="Februari">Februari</option>
                    <option value="Maret">Maret</option>
                    <option value="April">April</option>
                    <option value="Mei">Mei</option>
                    <option value="Juni">Juni</option>
                    <option value="Juli">Juli</option>
                    <option value="Agustus">Agustus</option>
                    <option value="September">September</option>
                    <option value="Oktober">Oktober</option>
                    <option value="November">November</option>
                    <option value="Desember">Desember</option>
                </select>
            </div>

            <!-- Search -->
            <div class="col-lg-7 col-md-5 d-flex justify-content-end mt-4">
                <div class="input-group" style="max-width: 250px;">
                    <span class="input-group-text"><i class="bx bx-search"></i></span>
                    <input type="search" class="form-control" id="searchInput" placeholder="Search...">
                </div>
            </div>
        </div>


        <!-- Button Filter Status -->
        <div class="demo-inline-spacing mb-4"
            style="display: flex; gap: 11px; justify-content: start; margin-left: 20px;">
            <button type="button" class="btn btn-outline-dark status-btn" data-status="Usulan">Usulan</button>
            <button type="button" class="btn btn-outline-primary status-btn" data-status="Validasi">Validasi</button>
            <button type="button" class="btn btn-outline-warning status-btn" data-status="Proses">Proses</button>
            <button type="button" class="btn btn-outline-success status-btn" data-status="Selesai">Selesai</button>
            <button type="button" class="btn btn-outline-danger status-btn" data-status="Tolak">Tolak</button>
        </div>
    </form>

    <hr>

    <div class="table-responsive text-nowrap mt-4">
        <table class="table table-sm table-bordered table-hover" id="dataTable" style="width:100%">
            <thead style="background-color: #dbdee0;">
                <tr>
                    <th>ID Usulan</th>
                    <th>Tahun</th>
                    <th>Kode PT</th>
                    <th>Nama PT</th>
                    <th>Bulan</th>
                    <th>Nama Penandatangan</th>
                    <th>Jabatan</th>
                    <th>Wilayah</th>
                    <th>File</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const pilihSptjm = document.getElementById('pilihsptjm');
    const bulanSelect = document.getElementById('selectTypeOptBulan');
    const statusButtons = document.querySelectorAll('.status-btn');

    // Init DataTable (client-side) with pagination
    const dt = $('#dataTable').DataTable({
        processing: true,
        serverSide: false,
        paging: true,
        pageLength: 10,
        lengthChange: true,
        searching: true,
        responsive: true,
        scrollX: true,
        scrollCollapse: true,
        order: [[0, 'desc']],
        columns: [
            { data: 'id_usulan' },
            { data: 'tahun' },
            { data: 'kode_pts' },
            { data: 'nama_pts' },
            { data: 'bulan' },
            { data: 'nama' },
            { data: 'jabatan' },
            { data: 'wilayah' },
            { data: 'file', render: function(d, type, row){
                if(!d) return '-';
                var idStr = (row.id_usulan || '').toString().trim();
                var up = idStr.toUpperCase();
                var prefix = '';
                if(up.startsWith('BT')) prefix = 'BT';
                else if(up.startsWith('ST')) prefix = 'ST';
                else if(up.startsWith('B')) prefix = 'B';
                else if(up.startsWith('S')) prefix = 'S';

                var folder = '';
                if(prefix === 'B') folder = 'uploadFile_SPTJM_B';
                else if(prefix === 'S') folder = 'uploadFile_SPTJM_S';
                else if(prefix === 'BT') folder = 'uploadFile_TUKIN_B';
                else if(prefix === 'ST') folder = 'uploadFile_TUKIN_S';

                var filePath = d.toString();
                // normalize: remove leading storage/ or /storage/
                filePath = filePath.replace(/^\/storage\//i, '').replace(/^storage\//i, '').replace(/^\//, '');
                // remove unwanted internal folder 'sptjm_susulan/' if present
                filePath = filePath.replace(/^sptjm_susulan\//i, '');

                var lower = filePath.toLowerCase();
                var folderLower = (folder || '').toLowerCase();
                var href = '';
                if(folder && lower.startsWith(folderLower + '/')) {
                    href = '/storage/' + filePath; // already contains folder
                } else if(folder) {
                    href = '/storage/' + folder + '/' + filePath; // prepend folder
                } else {
                    href = '/storage/' + filePath; // no folder determined
                }

                return `<a href="${href}" target="_blank"><i class="bx bx-file"></i> Lihat Dokumen</a>`;
            }},
            { data: 'status', defaultContent: 'N/A' }
        ],
        data: []
    });

    statusButtons.forEach(button => {
        button.addEventListener('click', function() {
            const status = this.getAttribute('data-status');
            const tipeSptjm = pilihSptjm.value;
            const bulan = bulanSelect.value;

            // ganti class button
            statusButtons.forEach(btn => {
                // reset semua ke outline
                btn.classList.remove('btn-dark', 'btn-primary', 'btn-warning',
                    'btn-success', 'btn-danger');
                btn.classList.add(
                    btn.dataset.status === "Usulan" ? 'btn-outline-dark' :
                    btn.dataset.status === "Validasi" ? 'btn-outline-primary' :
                    btn.dataset.status === "Proses" ? 'btn-outline-warning' :
                    btn.dataset.status === "Selesai" ? 'btn-outline-success' :
                    'btn-outline-danger'
                );
            });

            // ganti button yang diklik jadi solid
            this.classList.remove(
                'btn-outline-dark', 'btn-outline-primary', 'btn-outline-warning',
                'btn-outline-success',
                'btn-outline-danger'
            );
            this.classList.add(
                this.dataset.status === "Usulan" ? 'btn-dark' : this.dataset.status ===
                "Validasi" ? 'btn-primary' :
                this.dataset.status === "Proses" ? 'btn-warning' :
                this.dataset.status === "Selesai" ? 'btn-success' :
                'btn-danger'
            );

            Swal.fire({
                title: 'Mohon tunggu...',
                html: `
                      <div class="d-flex justify-content-center align-items-center flex-column">
                          <div class="spinner-border spinner-border-lg text-primary" role="status">
                              <span class="visually-hidden">Loading...</span>
                          </div>
                          <div class="mt-2">Sedang mencari data</div>
                      </div>
                  `,
                showConfirmButton: false,
                allowOutsideClick: false,
                allowEscapeKey: false,
                backdrop: true
            });

            fetch("{{ route('admin.usulan-sptjm.data') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        pilihsptjm: tipeSptjm,
                        bulan: bulan,
                        status: status
                    })
                })
                .then(response => response.json())
                .then(data => {
                    Swal.close();
                    // Populate DataTable with the returned data
                    dt.clear();
                    if (data.success && Array.isArray(data.data)) {
                        dt.rows.add(data.data);
                    }
                    dt.draw();
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: 'Terjadi kesalahan saat mengambil data.',
                    });
                });
        });
    });

    // Fitur Pencarian
    document.getElementById("searchInput").addEventListener("keyup", function() {
        $('#dataTable').DataTable().search(this.value).draw();
    });
});
</script>
@endsection
