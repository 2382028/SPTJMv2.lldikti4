@extends('layouts/contentNavbarLayoutPic')

@section('title', 'SPTJM Online')

@section('content')

<div class="card">
  <h5 class="card-header">Detail Data Dosen</h5>
  <div class="table-responsive text-nowrap">
    <div class="card-body">
      <form>
        <div class="row mb-3">
          <label class="col-sm-2 col-form-label">NIDN</label>
          <div class="col-sm-10">
            <input type="text" class="form-control" value="{{ $dosen->NIDN ?? '-' }}" readonly
              style="background-color: #eceef1;">
          </div>
        </div>

        <div class="row mb-3">
          <label class="col-sm-2 col-form-label">NUPTK</label>
          <div class="col-sm-10">
            <input type="text" class="form-control" value="{{ $dosen->NUPTK ?? '-' }}" readonly
              style="background-color: #eceef1;">
          </div>
        </div>

        <div class="row mb-3">
          <label class="col-sm-2 col-form-label">Nama</label>
          <div class="col-sm-10">
            <input type="text" class="form-control" value="{{ $dosen->Nama ?? '-' }}" readonly
              style="background-color: #eceef1;">
          </div>
        </div>

        <div class="row mb-3">
          <label class="col-sm-2 col-form-label">Tempat Lahir</label>
          <div class="col-sm-10">
            <input type="text" class="form-control" value="{{ $dosen->TTL ?? '-' }}" readonly
              style="background-color: #eceef1;">
          </div>
        </div>

        <div class="row mb-3">
          <label class="col-sm-2 col-form-label">Tanggal Lahir</label>
          <div class="col-sm-10">
            <input type="text" class="form-control" value="{{ $dosen->Tanggal_Lahir ?? '-' }}" readonly
              style="background-color: #eceef1;">
          </div>
        </div>

        <div class="row mb-3">
          <label class="col-sm-2 col-form-label">Usia</label>
          <div class="col-sm-10">
            <input type="text" class="form-control" value="{{ $dosen->Usia ?? '-' }}" readonly
              style="background-color: #eceef1;">
          </div>
        </div>

        <div class="row mb-3">
          <label class="col-sm-2 col-form-label">Kode PTS</label>
          <div class="col-sm-10">
            <input type="text" class="form-control" value="{{ $dosen->Kode_PT ?? '-' }}" readonly
              style="background-color: #eceef1;">
          </div>
        </div>

        <div class="row mb-3">
          <label class="col-sm-2 col-form-label">PTS</label>
          <div class="col-sm-10">
            <input type="text" class="form-control" value="{{ $dosen->PTS ?? '-' }}" readonly
              style="background-color: #eceef1;">
          </div>
        </div>

        <div class="row mb-3">
          <label class="col-sm-2 col-form-label">Jenis</label>
          <div class="col-sm-10">
            <input type="text" class="form-control" value="{{ $dosen->Jenis ?? '-' }}" readonly
              style="background-color: #eceef1;">
          </div>
        </div>

        <div class="row mb-3">
          @php
            $__formatTmt = function($v) {
              if (empty($v)) return '-';
              try {
                if (strpos($v, '/') !== false) {
                  $d = \Carbon\Carbon::createFromFormat('d/m/Y', $v);
                } else {
                  $d = \Carbon\Carbon::parse($v);
                }
                return $d->format('d/m/Y');
              } catch (\Exception $e) {
                return $v;
              }
            };
          @endphp

          <label class="col-sm-2 col-form-label">TMT JAD Pertama</label>
          <div class="col-sm-10">
            <input type="text" class="form-control" value="{{ call_user_func($__formatTmt, $dosen->TMT_JAD_Pertama ?? null) }}" readonly
              style="background-color: #eceef1;">
          </div>
        </div>

        <div class="row mb-3">
          <label class="col-sm-2 col-form-label">TMT JAD Akhir</label>
          <div class="col-sm-10">
            <input type="text" class="form-control" value="{{ call_user_func($__formatTmt, $dosen->TMT_JAD_Akhir ?? null) }}" readonly
              style="background-color: #eceef1;">
          </div>
        </div>

        <div class="row mb-3">
          <label class="col-sm-2 col-form-label">TMT Inpassing Akhir</label>
          <div class="col-sm-10">
            <input type="text" class="form-control" value="{{ call_user_func($__formatTmt, $dosen->TMT_Inpassing_Akhir ?? null) }}" readonly
              style="background-color: #eceef1;">
          </div>
        </div>

        <div class="row mb-3">
          <label class="col-sm-2 col-form-label">Jabatan</label>
          <div class="col-sm-10">
            <input type="text" class="form-control" value="{{ $dosen->jabatan ?? '-' }}" readonly
              style="background-color: #eceef1;">
          </div>
        </div>

        <div class="row mb-3">
          <label class="col-sm-2 col-form-label">Golongan</label>
          <div class="col-sm-10">
            <input type="text" class="form-control" value="{{ $dosen->gol ?? '-' }}" readonly
              style="background-color: #eceef1;">
          </div>
        </div>

        <div class="row mb-3">
          <label class="col-sm-2 col-form-label">Masa Kerja</label>
          <div class="col-sm-10">
            <input type="text" class="form-control" value="{{ $dosen->masa_kerja ?? '-' }}" readonly
              style="background-color: #eceef1;">
          </div>
        </div>

        <div class="row mb-3">
          <label class="col-sm-2 col-form-label">Biaya</label>
          <div class="col-sm-10">
            <input type="text" class="form-control" value="{{ $dosen->gaji ?? '-' }}" readonly
              style="background-color: #eceef1;">
          </div>
        </div>

        <div class="row mb-3">
          <label class="col-sm-2 col-form-label">Rekening</label>
          <div class="col-sm-10">
            <input type="text" class="form-control" value="{{ $dosen->No_Rekening ?? '-' }}" readonly
              style="background-color: #eceef1;">
          </div>
        </div>

        <div class="row mb-3">
          <label class="col-sm-2 col-form-label">Bank</label>
          <div class="col-sm-10">
            <input type="text" class="form-control" value="{{ $dosen->Bank ?? '-' }}" readonly
              style="background-color: #eceef1;">
          </div>
        </div>

        <div class="row mb-3">
          <label class="col-sm-2 col-form-label">Nama Rekening</label>
          <div class="col-sm-10">
            <input type="text" class="form-control" value="{{ $dosen->Nama_Rekening ?? '-' }}" readonly
              style="background-color: #eceef1;">
          </div>
        </div>

        <div class="row mb-3">
          <label class="col-sm-2 col-form-label">Nama Supplier</label>
          <div class="col-sm-10">
            <input type="text" class="form-control" value="{{ $dosen->Nama_Penerima ?? '-' }}" readonly
              style="background-color: #eceef1;">
          </div>
        </div>

        <div class="row mb-3">
          <label class="col-sm-2 col-form-label">NPWP</label>
          <div class="col-sm-10">
            <input type="text" class="form-control" value="{{ $dosen->NPWP ?? '-' }}" readonly
              style="background-color: #eceef1;">
          </div>
        </div>

        <div class="row mb-3">
          <label class="col-sm-2 col-form-label">Pemegang Wilayah</label>
          <div class="col-sm-10">
            <input type="text" class="form-control" value="{{ $dosen->Pemegang_Wilayah ?? '-' }}" readonly
              style="background-color: #eceef1;">
          </div>
        </div>

        <div class="row mb-3">
          <label class="col-sm-2 col-form-label">Eligible Span</label>
          <div class="col-sm-10">
            <input type="text" class="form-control" value="{{ $dosen->Eligible_span ?? '-' }}" readonly
              style="background-color: #eceef1;">
          </div>
        </div>

        <div class="row mb-3">
          <label class="col-sm-2 col-form-label">Status</label>
          <div class="col-sm-10">
            <input type="text" class="form-control"
              value="{{ ($dosen->aktif ?? 0) == 1 ? 'Aktif' : 'Tidak Aktif' }}" readonly
              style="background-color: #eceef1;">
          </div>
        </div>

        <div class="demo-inline-spacing mb-2">
          <div class="d-flex justify-content-center">
            <div class="btn-group dropup" role="group">
              <button type="button" class="btn btn-warning dropdown-toggle btn-sm" data-bs-toggle="dropdown"
                aria-expanded="false">
                <span class="tf-icons bx bx-edit"></span>&nbsp;Ubah
              </button>
              <ul class="dropdown-menu">
                <li>
                  <a class="dropdown-item" href="{{ route('pic.edit-data-dosen', $dosen->NIDN ?? $dosen->nidn) }}">
                    Perubahan Lainnya
                  </a>
                </li>
                <li>
                  <a class="dropdown-item" href="{{ route('pic.edit-mk-gol', $dosen->NIDN ?? $dosen->nidn) }}">
                    Perubahan Golongan dan Masa Kerja
                  </a>
                </li>
                <li>
                  <a class="dropdown-item" href="{{ route('pic.update-data-dosen', $dosen->NIDN ?? $dosen->nidn) }}">
                    Update Data (Gelar, Kode PT, Dokumen)
                  </a>
                </li>
              </ul>
            </div>
            @php
              $identifier = $dosen->NIDN ?? $dosen->NUPTK ?? '';
              $backUrl = !empty($identifier) ? route('dosen.showData', ['nidn' => $identifier]) : route('pic.lihat-data-dosen');
            @endphp
            <button type="button" id="btnBack" class="btn btn-secondary btn-sm mx-2">Kembali</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const btnBack = document.getElementById('btnBack');
    const fallbackBackUrl = @json($backUrl);
    if (btnBack) {
      btnBack.addEventListener('click', function(evt) {
        evt.preventDefault();
        try {
          const ref = document.referrer || '';
          const sameOrigin = ref ? (new URL(ref)).origin === window.location.origin : false;
          if (sameOrigin && ref !== '') {
            history.back();
          } else {
            window.location.href = fallbackBackUrl;
          }
        } catch (err) {
          window.location.href = fallbackBackUrl;
        }
      });
    }
  });
</script>

@endsection
