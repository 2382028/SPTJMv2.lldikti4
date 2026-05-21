@extends('layouts/contentNavbarLayout')

@section('title', 'SPTJM Online')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="card" style="width: 100%; padding: 10px;">
    <h5 class="card-header text-start p-2">Data Dosen Tidak Aktif</h5>
    <hr>
    <div class="d-flex justify-content-end align-items-center mb-3 px-1">
        <label class="me-2">Filter Keterangan:</label>
        <select id="filterKeterangan" class="form-select form-select-sm" style="max-width: 250px;">
            <option value="all">-- Semua Keterangan --</option>
            @isset($keteranganOptions)
            @foreach ($keteranganOptions as $opt)
            <option value="{{ $opt }}">{{ $opt }}</option>
            @endforeach
            @endisset
        </select>
    </div>

    <div class="table-responsive text-nowrap">
        {{ auth()->user()->email }}
        <table id="dosenTable" class="table table-sm table-hover">
            <thead style="background-color: #dbdee0;">
                <tr>
                    <th>NIDN</th>
                    <th>NUPTK</th>
                    <th>Nama Dosen</th>
                    <th>Kode PTS</th>
                    <th>Nama PTS</th>
                    <th>Status</th>
                    <th>Keterangan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                {{-- Data di load pakai ajax --}}
            </tbody>
        </table>
    </div>
</div>
<script>
$(document).ready(function() {
    @if(session('success'))
    Swal.fire({
        title: 'Berhasil!',
        text: @json(session('success')),
        icon: 'success',
        customClass: {
            confirmButton: 'btn btn-primary'
        },
        buttonsStyling: false
    });
    @endif
    @if(session('error'))
    Swal.fire({
        title: 'Gagal!',
        text: @json(session('error')),
        icon: 'error',
        customClass: {
            confirmButton: 'btn btn-primary'
        },
        buttonsStyling: false
    });
    @endif

    $('#dosenTable').DataTable({
        serverSide: true,
        processing: true,
        responsive: true,
        scrollX: true,
        scrollcollapse: true,
        pageLength: 15,
        lengthMenu: [15, 25, 75, 100],
        ajax: {
            url: "{{ route('admin.data-dosen.tidak-aktif.data') }}",
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: function(d) {
                d.keterangan = $('#filterKeterangan').val();
            }
        },
        columns: [{
                data: 'nidn',
                name: 'nidn'
            },
            {
                data: 'nuptk',
                name: 'nuptk'
            },
            {
                data: 'Nama',
                name: 'nama'
            },
            {
                data: 'Kode_PT',
                name: 'kode_pt'
            },
            {
                data: 'PTS',
                name: 'pts'
            },
            {
                data: 'status',
                name: 'status',
                orderable: false,
                searchable: false
            },
            {
                data: 'Keterangan',
                name: 'keterangan'
            },
            {
                data: null,
                name: 'aksi',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    const identifier = row.nidn || row.NIDN || row.nuptk || row.NUPTK;
                    if (!identifier) {
                        return '';
                    }

                    // Jika can_delete == 1 (tidak ada kode usulan/kode cair) → tampilkan tombol hapus
                    if (parseInt(row.can_delete ?? 0) === 1) {
                        let url = "{{ route('admin.data-dosen.tidak-aktif.hapus', ':id') }}";
                        url = url.replace(':id', identifier);

                        return `
                            <form action="${url}" method="POST" class="form-delete-dosen">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="button" class="btn btn-icon btn-sm btn-danger btn-can-delete" data-id="${identifier}">
                                    <span class="tf-icons bx bx-trash"></span>
                                </button>
                            </form>
                        `;
                    }

                    // Jika punya kode usulan/kode cair → tidak boleh dihapus, tampilkan tombol info yang memunculkan modal
                    return `
                        <button type="button" class="btn btn-icon btn-sm btn-secondary btn-cannot-delete" data-id="${identifier}">
                            <span class="tf-icons bx bx-info-circle"></span>
                        </button>
                    `;
                }
            }
        ],
        language: {
            searchPlaceholder: "Cari disini...",
            paginate: {
                "first": "Awal",
                "last": "Akhir",
                "next": "→",
                "previous": "←"
            },
            zeroRecords: "Data tidak ditemukan",
            infoEmpty: "Tidak ada data tersedia",
        }
    });

    // reload ketika filter berubah
    $('#filterKeterangan').on('change', function() {
        $('#dosenTable').DataTable().ajax.reload();
    });

    // Modal (SweetAlert) ketika baris tidak dapat dihapus karena masih punya kode usulan / kode cair
    $('#dosenTable').on('click', '.btn-cannot-delete', function() {
        const id = $(this).data('id') || '-';
        Swal.fire({
            title: 'Tidak dapat dihapus',
            html: `Data dosen dengan NIDN/NUPTK <b>${id}</b> masih memiliki <br>kode cair sehingga tidak dapat dihapus.`,
            icon: 'info',
            confirmButtonText: 'OK',
            customClass: {
                confirmButton: 'btn btn-primary'
            },
            buttonsStyling: false
        });
    });

    // Modal konfirmasi hapus ketika data boleh dihapus (can_delete == 1)
    $('#dosenTable').on('click', '.btn-can-delete', function(e) {
        e.preventDefault();
        const id = $(this).data('id') || '-';
        const form = $(this).closest('form');

        Swal.fire({
            title: 'Yakin hapus data?',
            html: `Data dosen dengan NIDN/NUPTK <b>${id}</b> akan dihapus dan tidak dapat dikembalikan.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: 'btn btn-danger me-2',
                cancelButton: 'btn btn-secondary'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                form.trigger('submit');
            }
        });
    });
});
</script>
@endsection
