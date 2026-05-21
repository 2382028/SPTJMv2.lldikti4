@extends('layouts/contentNavbarLayoutPic')

@section('title', 'SPTJM Online')

@section('content')

    <div class="card w-100 p-3">
        <h5 class="card-header text-start p-2">Tabel Riwayat Pengajuan</h5>
        <hr>

        <div class="card-body">
            <div class="table-responsive">
                <table id="riwayat-table" class="table table-sm table-bordered" style="width:100%">
                    <thead class="text-nowrap" style="background-color: #dbdee0;">
                        <tr>
                            <th>ID Usulan</th>
                            <th>Tanggal Usulan</th>
                            <th>Bulan</th>
                            <th>Nama PTS</th>
                            <th>Progres</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($riwayats ?? [] as $item)
                        <tr>
                            <td>{{ $item->id_usulan ?? '-' }}</td>
                            <td>{{ $item->tanggal_usulan ?? '-' }}</td>
                            <td>{{ $item->bulan ?? '-' }}</td>
                            <td>{{ $item->nama_pts ?? '-' }}</td>
                            <td>{{ $item->status ?? '-' }}</td>
                            <td>
                                @php $storageBase = asset('storage'); @endphp
                                {{-- File button (icon only). If no file, show disabled icon button --}}
                                @php
                                    $idUs = $item->id_usulan ?? '';
                                    $prefix2 = strtoupper(substr(trim($idUs), 0, 2));
                                    $prefix1 = strtoupper(substr(trim($idUs), 0, 1));
                                    // determine folder based on prefix
                                    if (in_array($prefix2, ['TB', 'TS'])) {
                                        $folder = $prefix2 === 'TB' ? 'uploadFile_TUKIN_B' : 'uploadFile_TUKIN_S';
                                    } else {
                                        $folder = $prefix1 === 'B' ? 'uploadFile_SPTJM_B' : ($prefix1 === 'S' ? 'uploadFile_SPTJM_S' : '');
                                    }
                                    $filePath = $item->file ?? '';
                                    if ($filePath && strpos($filePath, '/') === false && $folder) {
                                        $filePath = trim($folder, '/') . '/' . ltrim($filePath, '/');
                                    }
                                @endphp
                                @if(!empty($item->file))
                                    <a href="{{ $storageBase . '/' . $filePath }}" target="_blank" class="btn btn-primary btn-sm rounded-3 shadow me-1 p-2 text-white d-inline-flex align-items-center justify-content-center" title="Lihat Dokumen">
                                        <i class="bx bx-file"></i>
                                    </a>
                                @else
                                    <button type="button" class="btn btn-primary btn-sm rounded-3 shadow me-1 p-2 text-white" disabled title="Tidak ada dokumen">
                                        <i class="bx bx-file"></i>
                                    </button>
                                @endif

                                {{-- Detail button (icon only) always shown if `no` exists --}}
                                @if(!empty($item->no))
                                    <a href="{{ url('pic/detail-riwayat-pengajuan/' . $item->no) }}" class="btn btn-info btn-sm rounded-3 shadow p-2 text-white d-inline-flex align-items-center justify-content-center" title="Detail Pengajuan">
                                        <i class="bx bx-show "></i>
                                    </a>
                                @else
                                    <button type="button" class="btn btn-info btn-sm rounded-3 shadow p-2 text-white" disabled title="Detail tidak tersedia">
                                        <i class="bx bx-detail"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection

@section('page-script')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        $('#riwayat-table').DataTable({
            responsive: true,
            fixedHeader: true,
            processing: false,
            serverSide: false,
            order: [[1, 'desc']],
            columnDefs: [
                { targets: 1, type: 'date' }
            ],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
            }
        });
    });
</script>
@endsection

@section('page-style')
<!-- Using Bootstrap utilities only for button styling (no custom CSS) -->
@endsection
