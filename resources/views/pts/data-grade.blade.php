@extends('layouts/contentNavbarLayoutPts')

@section('title', 'SPTJM Online')

@section('content')

<div class="card" style="width: 100%; padding: 10px;">
  <h5 class="card-header text-start p-2">Data Grade</h5>
  <hr>

  <div class="table-responsive text-nowrap">
    <table class="table table-sm table-hover" id="gradeTable">
      <thead style="background-color: #dbdee0;">
        <tr>
          <th>Kode</th>
          <th>Golongan</th>
          <th>Masa Kerja</th>
          <th>Nominal</th>
        </tr>
      </thead>
    </table>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    if (typeof $ === 'undefined' || typeof $.fn.DataTable === 'undefined') {
      return;
    }

    $('#gradeTable').DataTable({
      processing: true,
      serverSide: true,
      responsive: true,
      ajax: {
        url: "{{ route('pts.data-grade') }}"
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
  });
</script>

@endsection
