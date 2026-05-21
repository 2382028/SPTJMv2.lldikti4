@extends('layouts/contentNavbarLayoutPic')

@section('title', 'SPTJM Online')

@section('content')

    <div class="content-wrapper">
        <div class="row">
            <div class="col-12">
                <form action="{{ route('pic.update-mk-gol', ['nidn' => ($data_dosen->NIDN ?? $data_dosen->nidn)]) }}" method="POST"
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
                                    <select class="form-select" aria-label="Default select example" name="Aktif"
                                        readonly="" required="">
                                        <option value="1" {{ ($data_dosen->Aktif ?? $data_dosen->aktif) == 1 ? 'selected' : '' }}>Aktif
                                        </option>
                                        <option value="0" {{ ($data_dosen->Aktif ?? $data_dosen->aktif) == 0 ? 'selected' : '' }}>Tidak Aktif
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
                                        <option selected="">-- Pilih Alasan --</option>
                                        <option value="Penambahan Data Baru">Penambahan Data Baru</option>
                                        <option value="Perubahan Golongan dan Masa Kerja">Perubahan Golongan dan Masa Kerja
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="basic-default-name">Dokumen</label>
                                <div class="col-sm-4">
                                    <input class="form-control" type="file" name="dokumen" accept=".pdf, .doc, .docx"
                                        required="">
                                </div>
                                <label class="col-sm-2 col-form-label" for="basic-default-name">Terhitung Mulai
                                    Tanggal</label>
                                <div class="col-sm-4">
                                    <input class="form-control" type="date" name="tanggal_update_terakhir"
                                        id="html5-date-input" required="">
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
                                        value="{{ $data_dosen->NIDN ?? $data_dosen->nidn ?? 'Data tidak tersedia' }}" readonly
                                        style="background-color: #eceef1;">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="basic-default-name">NIK</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="basic-default-name" name="nik"
                                        value="{{ $data_dosen->NIK ?? $data_dosen->nik ?? 'Data tidak tersedia' }}" readonly
                                        style="background-color: #eceef1;">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="basic-default-name">Nama</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="basic-default-name" name="nama"
                                        value="{{ $data_dosen->Nama ?? $data_dosen->nama ?? 'Data tidak tersedia' }}" readonly
                                        style="background-color: #eceef1;">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="basic-default-name">Tempat Lahir</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="basic-default-name" name="ttl"
                                        value="{{ $data_dosen->TTL ?? $data_dosen->ttl ?? 'Data tidak tersedia' }}" readonly
                                        style="background-color: #eceef1;">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="basic-default-name">Tanggal Lahir</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="basic-default-name-TTL"
                                        name="tanggal_lahir"
                                        value="{{ $data_dosen->Tanggal_Lahir ?? $data_dosen->tanggal_lahir ?? 'Data tidak tersedia' }}" readonly
                                        style="background-color: #eceef1;">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="basic-default-name">Usia</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="basic-default-name-Usia"
                                        name="usia" value="{{ $data_dosen->Usia ?? $data_dosen->usia ?? 'Data tidak tersedia' }}" readonly
                                        style="background-color: #eceef1;">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="basic-default-name">Kode PTS</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="basic-default-name" name="kode_pt"
                                        value="{{ $data_dosen->Kode_PT ?? $data_dosen->kode_pt ?? 'Data tidak tersedia' }}" readonly
                                        style="background-color: #eceef1;">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="basic-default-name">PTS</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="basic-default-name" name="pts"
                                        value="{{ $data_dosen->PTS ?? $data_dosen->pts ?? 'Data tidak tersedia' }}" readonly
                                        style="background-color: #eceef1;">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="basic-default-name">Jenis</label>
                                <div class="col-sm-10">
                                    <select class="form-select" aria-label="Default select example" name="jenis">
                                        <option value="NON PNS" {{ ($data_dosen->Jenis ?? $data_dosen->jenis) == 'NON PNS' ? 'selected' : '' }}>
                                            NON PNS</option>
                                        <option value="PNS" {{ ($data_dosen->Jenis ?? $data_dosen->jenis) == 'PNS' ? 'selected' : '' }}>PNS
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="basic-default-name">Jabatan</label>
                                <div class="col-sm-10">
                                    <select class="form-select" aria-label="Default select example" name="jabatan">
                                        <option value="Asisten Ahli"
                                            {{ trim($data_dosen->Jabatan12 ?? $data_dosen->jabatan12) == 'Asisten Ahli' ? 'selected' : '' }}>
                                            Asisten Ahli
                                        </option>
                                        <option value="Guru Besar"
                                            {{ trim($data_dosen->Jabatan12 ?? $data_dosen->jabatan12) == 'Guru Besar' ? 'selected' : '' }}>
                                            Guru Besar
                                        </option>
                                        <option value="Lektor"
                                            {{ trim($data_dosen->Jabatan12 ?? $data_dosen->jabatan12) == 'Lektor' ? 'selected' : '' }}>
                                            Lektor
                                        </option>
                                        <option value="Lektor Kepala"
                                            {{ trim($data_dosen->Jabatan12 ?? $data_dosen->jabatan12) == 'Lektor Kepala' ? 'selected' : '' }}>
                                            Lektor Kepala
                                        </option>
                                        <option value="Tenaga Pengajar"
                                            {{ trim($data_dosen->Jabatan12 ?? $data_dosen->jabatan12) == 'Tenaga Pengajar' ? 'selected' : '' }}>
                                            Tenaga Pengajar
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="golongan">Golongan</label>
                                <div class="col-sm-10">
                                    <select class="form-select" name="gol" id="golongan" required>
                                        <option value="III/a"
                                            {{ isset($data_dosen) && $data_dosen->gol12 == 'III/a' ? 'selected' : '' }}>
                                            III/a</option>
                                        <option value="III/b"
                                            {{ isset($data_dosen) && $data_dosen->gol12 == 'III/b' ? 'selected' : '' }}>
                                            III/b</option>
                                        <option value="III/c"
                                            {{ isset($data_dosen) && $data_dosen->gol12 == 'III/c' ? 'selected' : '' }}>
                                            III/c</option>
                                        <option value="III/d"
                                            {{ isset($data_dosen) && $data_dosen->gol12 == 'III/d' ? 'selected' : '' }}>
                                            III/d</option>
                                        <option value="IV/a"
                                            {{ isset($data_dosen) && $data_dosen->gol12 == 'IV/a' ? 'selected' : '' }}>
                                            IV/a</option>
                                        <option value="IV/b"
                                            {{ isset($data_dosen) && $data_dosen->gol12 == 'IV/b' ? 'selected' : '' }}>
                                            IV/b</option>
                                        <option value="IV/c"
                                            {{ isset($data_dosen) && $data_dosen->gol12 == 'IV/c' ? 'selected' : '' }}>
                                            IV/c</option>
                                        <option value="IV/d"
                                            {{ isset($data_dosen) && $data_dosen->gol12 == 'IV/d' ? 'selected' : '' }}>
                                            IV/d</option>
                                        <option value="IV/e"
                                            {{ isset($data_dosen) && $data_dosen->gol12 == 'IV/e' ? 'selected' : '' }}>
                                            IV/e</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="masa_kerja">Masa Kerja (tahun)</label>
                                <div class="col-sm-10">
                                    <input type="number" class="form-control" id="masa_kerja" name="tahun"
                                        min="0" placeholder="Masukkan masa kerja"
                                        value="{{ isset($data_dosen) ? $data_dosen->tahun12 : '' }}">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="gaji">Biaya</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="gaji" name="gaji"
                                        value="{{ isset($data_dosen) ? $data_dosen->gaji12 : '' }}" readonly
                                        style="background-color: #eceef1;">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="basic-default-name">Rekening</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="basic-default-name"
                                        name="no_rekening" value="{{ $data_dosen->no_rekening }}" readonly
                                        style="background-color: #eceef1;">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="basic-default-name">Bank</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="basic-default-name" name="bank"
                                        value="{{ $data_dosen->bank }}" readonly style="background-color: #eceef1;">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="basic-default-name">Nama Rekening</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="basic-default-name"
                                        name="nama_rekening" value="{{ $data_dosen->nama_rekening }}" readonly
                                        style="background-color: #eceef1;">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="basic-default-name">Nama Supplier</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="basic-default-name"
                                        name="nama_penerima" value="{{ $data_dosen->nama_penerima }}" readonly
                                        style="background-color: #eceef1;">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="basic-default-name">NPWP</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="basic-default-name" name="npwp"
                                        value="{{ $data_dosen->npwp }}" readonly style="background-color: #eceef1;">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="basic-default-name">Pemegang
                                    Wilayah</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="basic-default-name"
                                        name="pemegang_wilayah" value="{{ $data_dosen->pemegang_wilayah }}" readonly
                                        style="background-color: #eceef1;">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="basic-default-company">ELigible Span</label>
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const golonganInput = document.getElementById('golongan');
            const masaKerjaInput = document.getElementById('masa_kerja');
            const gajiInput = document.getElementById('gaji');

            function fetchGaji() {
                const golongan = golonganInput.value;
                const masaKerja = masaKerjaInput.value;

                if (golongan && masaKerja) {
                    fetch("{{ route('admin.ubah-mk-gol.get-biaya') }}", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": "{{ csrf_token() }}"
                            },
                            body: JSON.stringify({
                                golongan: golongan,
                                masa_kerja: masaKerja
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            gajiInput.value = data.gaji ?? "Data tidak ditemukan";
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            gajiInput.value = "Error mengambil data";
                        });
                }
            }

            golonganInput.addEventListener('change', fetchGaji);
            masaKerjaInput.addEventListener('input', fetchGaji);

            fetchGaji();
        });
    </script>

@endsection
