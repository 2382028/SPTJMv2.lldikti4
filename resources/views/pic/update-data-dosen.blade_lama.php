@extends('layouts/contentNavbarLayoutPic')

@section('title', 'SPTJM Online')

@section('content')

    <div class="content-wrapper">
        <div class="row">
            <div class="col-12">
                <form action="{{ route('pic.update-data', ['nidn' => $data_dosen->nidn]) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="card mb-4">
                        <div class="card-header">Informasi Perubahan</div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="basic-default-name">No Dokumen</label>
                                <div class="col-sm-4">
                                    <input type="text" class="form-control" name="no_dokumen_ubah"
                                        id="basic-default-name" required="">
                                </div>
                                <label class="col-sm-2 col-form-label" for="basic-default-company">Status</label>
                                <div class="col-sm-4">
                                    <select class="form-select" aria-label="Default select example" name="Aktif" disabled
                                        readonly="">
                                        <option value="1" {{ $data_dosen->aktif == 1 ? 'selected' : '' }}>Aktif
                                        </option>
                                        <option value="0" {{ $data_dosen->aktif == 0 ? 'selected' : '' }}>Tidak Aktif
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="formFile">Tanggal Dokumen</label>
                                <div class="col-sm-4">
                                    <input class="form-control" type="date" name="tgl_dokumen_ubah" id="html5-date-input"
                                        required="">
                                </div>
                                <label class="col-sm-2 col-form-label" for="basic-default-name">Alasan Perubahan</label>
                                <div class="col-sm-4">
                                    <select class="form-select" id="exampleFormControlSelect1" name="alasan_perubahan"
                                        aria-label="Default select example" required="">
                                        <option value="Penambahan Data (Gelar/PT/Dokumen)" selected="">Penambahan Data
                                            (Gelar/PT/Dokumen)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="basic-default-name">Dokumen</label>
                                <div class="col-sm-4">
                                    <input class="form-control" type="file" name="dokumen" accept=".pdf, .doc, .docx"
                                        required="">
                                </div>
                            </div>

                            <div class="row mb-3 ">
                                <label class="col-sm-2 col-form-label" for="basic-default-name">Keterangan</label>
                                <div class="col-sm-4">
                                    <input type="text" class="form-control" name="keterangan" id="basic-default-name"
                                        required="">
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-3">

                    <div class="card mb-4">
                        <div class="card-header">Data Dosen</div>
                        <div class="card-body">
                            <input type="hidden" name="pk" value="">
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="basic-default-name">NIDN</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="basic-default-name" name="nidn"
                                        value="{{ $data_dosen->nidn ?? 'Data tidak tersedia' }}" readonly
                                        style="background-color: #eceef1;">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="basic-default-name">NIK</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="basic-default-name" name="nik"
                                        value="{{ $data_dosen->nik ?? 'Data tidak tersedia' }}" readonly
                                        style="background-color: #eceef1;">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="basic-default-name">Nama</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="basic-default-name" name="nama"
                                        value="{{ $data_dosen->nama ?? 'Data tidak tersedia' }}">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="basic-default-name">Tempat Lahir</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="basic-default-name" name="ttl"
                                        value="{{ $data_dosen->ttl ?? 'Data tidak tersedia' }}" readonly
                                        style="background-color: #eceef1;">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="basic-default-name">Tanggal Lahir</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="basic-default-name-TTL"
                                        name="tanggal_lahir"
                                        value="{{ $data_dosen->tanggal_lahir ?? 'Data tidak tersedia' }}" readonly
                                        style="background-color: #eceef1;">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="basic-default-name">Usia</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="basic-default-name-Usia"
                                        name="usia" value="{{ $data_dosen->usia ?? 'Data tidak tersedia' }}" readonly
                                        style="background-color: #eceef1;">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="basic-default-name">Kode PTS</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="basic-default-name" name="kode_pt"
                                        value="{{ $data_dosen->kode_pt ?? 'Data tidak tersedia' }}">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="basic-default-name">PTS</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="basic-default-name" name="pts"
                                        value="{{ $data_dosen->pts ?? 'Data tidak tersedia' }}">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="basic-default-name">Pemegang
                                    Wilayah</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="basic-default-name"
                                        name="pemegang_wilayah" value="{{ $data_dosen->pemegang_wilayah }}">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="basic-default-name">Jenis</label>
                                <div class="col-sm-10">
                                    <select class="form-select" aria-label="Default select example" name="jenis"
                                        disabled>
                                        <option value="NON PNS" {{ $data_dosen->jenis == 'NON PNS' ? 'selected' : '' }}>
                                            NON PNS</option>
                                        <option value="PNS" {{ $data_dosen->jenis == 'PNS' ? 'selected' : '' }}>PNS
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="basic-default-name">Jabatan</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="basic-default-name" name="jabatan"
                                        value="{{ $data_dosen->jabatan12 }}" readonly style="background-color: #eceef1;">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="basic-default-name">Golongan</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="golongan" name="gol"
                                        value="{{ $data_dosen->gol12 }}" readonly style="background-color: #eceef1;">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="basic-default-name">Masa Kerja</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="masa_kerja" name="tahun"
                                        value="{{ $data_dosen->tahun12 }}" readonly style="background-color: #eceef1;">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="basic-default-name">Biaya</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="biaya_per_bulan" name="gaji" .=""
                                        value="{{ $data_dosen->gaji12 }}" readonly style="background-color: #eceef1;">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="basic-default-name">Rekening</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="basic-default-name"
                                        name="no_rekening" value="{{ $data_dosen->no_rekening }}">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="basic-default-name">Bank</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="basic-default-name" name="bank"
                                        value="{{ $data_dosen->bank }}">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="basic-default-name">Nama Rekening</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="basic-default-name"
                                        name="nama_rekening" value="{{ $data_dosen->nama_rekening }}">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="basic-default-name">Nama Supplier</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="basic-default-name"
                                        name="nama_penerima" value="{{ $data_dosen->nama_penerima }}">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="basic-default-name">NPWP</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="basic-default-name" name="npwp"
                                        value="{{ $data_dosen->npwp }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="basic-default-company">Eligible Span</label>
                                <div class="col-sm-10">
                                    <select class="form-select" aria-label="Default select example" name="eligible_span"
                                        readonly="">
                                        <option value="YA" {{ $data_dosen->eligible_span == 'YA' ? 'selected' : '' }}>
                                            YA</option>
                                        <option value="TIDAK"
                                            {{ $data_dosen->eligible_span == 'TIDAK' ? 'selected' : '' }}>TIDAK
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="row justify-content-end mt-3">
                                <div class="col-sm-10">
                                    <div class="d-flex justify-content-center">
                                        <button type="submit" class="btn btn-success mx-2">Simpan</button>
                                        <a
                                            href="{{ route('pic.lihat-data-dosen') }}"class="btn btn-secondary mx-2">Batal</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
