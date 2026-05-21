@extends('layouts/contentNavbarLayout')

@section('title', 'SPTJM Online')

@section('content')
<div class="card px-3 py-2">
  <h5 class="card-header">Histori Data Dosen</h5>
  <hr>

  <div class="table-responsive text-nowrap">
    <table class="table table-sm table-hover" id="dosenTable">
      <thead style="background-color: #dbdee0;">
        <tr>
          <th>NIDN</th>
          <th>NUPTK</th>
          <th>Nama Dosen</th>
          <th>Nama PTS</th>
          <th>Status</th>
          <th>Pengguna</th>
          <th>TMT</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>
</div>
<script>
  // DataTables server-side pagination + search via AJAX
  (function() {
    document.addEventListener('DOMContentLoaded', function() {
      const $ = window.jQuery;
      if (!$) {
        console.error('jQuery is not loaded. DataTables cannot initialize.');
        return;
      }
      if (!$.fn || !$.fn.DataTable) {
        console.error('DataTables is not loaded. Please ensure DataTables scripts are included in the layout.');
        return;
      }

      $('#dosenTable').DataTable({
        processing: true,
        serverSide: true,
        scrollX: true,
        scrollCollapse: true,
        ajax: {
          url: '{{ route("admin.histori-dosen.data") }}',
          error: function(xhr) {
            console.error('Histori Dosen AJAX error:', xhr.status, xhr.responseText);
          }
        },
        pageLength: 15,
        lengthMenu: [[15, 25, 50, 100], [15, 25, 50, 100]],
        order: [[6, 'desc']],
        columns: [
          { data: 'nidn', name: 'nidn', className: 'text-start' },
          { data: 'nuptk', name: 'nuptk', className: 'text-start' },
          { data: 'nama', name: 'nama' },
          { data: 'pts', name: 'pts' },
          { data: 'aktif', name: 'aktif', orderable: false, searchable: false },
          { data: 'pengguna', name: 'pengguna' },
          { data: 'tgl_dokumen_ubah', name: 'tgl_dokumen_ubah' },
          { data: 'aksi', name: 'aksi', orderable: false, searchable: false },
        ],
        dom: "<'row align-items-center mb-3'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6 d-flex justify-content-md-end justify-content-start mt-2 mt-md-0'f>>" +
             "rt<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        language: {
          paginate: {
            first: "Awal",
            last: "Akhir",
            next: "→",
            previous: "←",
          },
          zeroRecords: "Data tidak ditemukan",
          infoEmpty: "Tidak ada data tersedia",
          searchPlaceholder: "Cari histori...",
          search: "Cari Data:",
        },
      });
    });
  })();
</script>
@endsection
