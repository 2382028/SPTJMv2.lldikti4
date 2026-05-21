@extends('layouts/contentNavbarLayoutPic')

@section('title', 'SPTJM Online')

@section('content')

    <div class="card">
        <h5 class="card-header">Detail Data Dosen</h5>
        <div class="table-responsive text-nowrap">

            <div class="card-body">
                <form>
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="basic-default-name">NIDN</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" value="{{ $dosen->nidn }}" readonly
                                style="background-color: #eceef1;">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="basic-default-name">NIP</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" value="{{ $dosen->nik }}" readonly
                                style="background-color: #eceef1;">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="basic-default-name">Nama</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" value="{{ $dosen->nama }}" readonly
                                style="background-color: #eceef1;">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="basic-default-name">Tempat Lahir</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" value="{{ $dosen->ttl }}" readonly
                                style="background-color: #eceef1;">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="basic-default-name">Tanggal Lahir</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" value="{{ $dosen->tanggal_lahir }}" readonly
                                style="background-color: #eceef1;">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="basic-default-name">Usia</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" value="{{ $dosen->usia }}" readonly
                                style="background-color: #eceef1;">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="basic-default-name">PTS</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" value="{{ $dosen->pts }}" readonly
                                style="background-color: #eceef1;">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="basic-default-name">Jenis</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" value="{{ $dosen->jenis }}" readonly
                                style="background-color: #eceef1;">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="basic-default-name">Jabatan</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" value="{{ $dosen->jabatan }}" readonly
                                style="background-color: #eceef1;">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="basic-default-name">Golongan</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" value="{{ $dosen->gol }}" readonly
                                style="background-color: #eceef1;">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="basic-default-name">Masa Kerja</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" value="{{ $dosen->tahun }}" readonly
                                style="background-color: #eceef1;">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="basic-default-name">Biaya</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" value="{{ $dosen->gaji }}" readonly
                                style="background-color: #eceef1;">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="basic-default-name">Rekening</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" value="{{ $dosen->no_rekening }}" readonly
                                style="background-color: #eceef1;">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="basic-default-name">Bank</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" value="{{ $dosen->bank }}" readonly
                                style="background-color: #eceef1;">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="basic-default-name">Nama Rekening</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" value="{{ $dosen->nama_rekening }}" readonly
                                style="background-color: #eceef1;">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="basic-default-name">NPWP</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" value="{{ $dosen->npwp }}" readonly
                                style="background-color: #eceef1;">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="basic-default-name">Status</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control"
                                value="{{ $dosen->aktif == 1 ? 'Aktif' : 'Tidak Aktif' }}" readonly
                                style="background-color: #eceef1;">
                        </div>

                        <div class="demo-inline-spacing">
                            <div class="d-flex justify-content-center">
                                <a href="{{ route('pic.lihat-data-dosen') }}" class="btn btn-secondary mx-2">Kembali</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
