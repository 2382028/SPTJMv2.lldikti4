@extends('layouts/contentNavbarLayoutPic')

@section('title', 'SPTJM Online')

@section('content')

    <div class="card" style="width: 100%; padding: 10px;">
        <h5 class="card-header text-start p-2">Lihat Histori Data Dosen</h5>
        <hr>
        <div class="d-flex justify-content-end align-items-center mb-3 px-3">
            <div class="input-group me-3" id="DataTables_Table_0_filter" style="max-width: 200px;">
                <span class="input-group-text"><i class="bx bx-search"></i></span>
                <input type="search" class="form-control" id="searchInput" placeholder="Search..."
                    aria-controls="DataTables_Table_0">
            </div>
        </div>

        <div class="table-responsive text-nowrap">

            <table class="table table-sm table-hover" id="dosenTable">
                <thead style="background-color: #dbdee0;">
                    <tr>
                        <th>No Dokumen</th>
                        <th>Tanggal Dokumen</th>
                        <th>Dokumen</th>
                        <th>NIDN</th>
                        <th>Nama</th>
                        <th>Alasan Perubahan</th>
                        <th>Keterangan</th>
                        <th>Pengguna</th>
                        <th>Terhitung Mulai Tanggal (TMT)</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @foreach ($dosen as $dosen)
                        <tr>
                            <td>{{ $dosen->no_dokumen_ubah }}</td>
                            <td>{{ $dosen->tgl_dokumen_ubah }}</td>
                            <td>
                                <a href="{{ asset('storage/Dokumen_Histori_Dosen2/' . $dosen->dokumen) }}" target="_blank">
                                    <i class="bx bx-file"></i> Lihat Dokumen
                                </a>
                            </td>
                            <td>{{ Str::mask($dosen->nidn, '*', 0, 0) }}</td>
                            <td>{{ $dosen->nama }}</td>
                            <td>{{ $dosen->alasan_perubahan }}</td>
                            <td>{{ $dosen->keterangan }}</td>
                            <td>{{ $dosen->pengguna }}</td>
                            <td>{{ $dosen->tanggal_update_terakhir }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <script>
        document.getElementById("searchInput").addEventListener("keyup", function() {
            var filter = this.value.toLowerCase();
            var rows = document.querySelectorAll("#dosenTable tbody tr");
            rows.forEach(function(row) {
                var text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? "" : "none";
            });
        });
    </script>
@endsection
