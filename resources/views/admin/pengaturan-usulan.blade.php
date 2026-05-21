@extends('layouts/contentNavbarLayout')

@section('title', 'SPTJM Online')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <h5 class="card-header">Pengaturan Usulan SPTJM</h5>
                <div class="card-body">
                    <form id="pengaturanUsulanForm" action="{{ route('pengaturan-usulan.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label" for="tahun">Jenis Usulan</label>
                                <select id="jenis_usulan" class="form-select" name="jenis_usulan" required>
                                    <option value="" selected disabled>--PILIH--</option>
                                    <option value="SPTJM">SPTJM</option>
                                    <option value="TUKIN">TUKIN</option>
                                </select>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label" for="tahun">Tahun</label>
                                <select id="tahun" class="form-select" name="tahun" required>
                                    <option value="{{ date('Y') }}" selected>{{ date('Y') }}</option>
                                </select>
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <label class="form-label" for="bulan">Bulan</label>
                                <select id="bulan" class="form-select" name="bulan" required>
                                    @foreach (['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli',
                                    'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $index => $bulan)
                                    <option value="{{ $bulan }}">
                                        [{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}] {{ $bulan }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <label class="form-label" for="pencairan_ke">Pencairan ke</label>
                                <select id="pencairan_ke" class="form-select" name="pencairan_ke" required>
                                    <option value="">-- Pilih pencairan --</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-lg-3">
                                <label class="form-label" for="tanggal_mulai">Tanggal Mulai</label>
                                <input class="form-control" type="date" name="tanggal_mulai" required>
                            </div>

                            <div class="col-lg-3">
                                <label class="form-label" for="tanggal_selesai">Tanggal Selesai</label>
                                <input class="form-control" type="date" name="tanggal_selesai" required>
                            </div>

                            <div class="col-lg-3">
                                <label class="form-label d-block">Status</label>
                                <div class="form-check">
                                    <input name="status" class="form-check-input" type="radio" value="Aktifkan"
                                        id="status_aktif" checked>
                                    <label class="form-check-label" for="status_aktif">Aktifkan</label>
                                </div>
                                <div class="form-check">
                                    <input name="status" class="form-check-input" type="radio" value="Nonaktifkan"
                                        id="status_nonaktif">
                                    <label class="form-check-label" for="status_nonaktif">Nonaktifkan</label>
                                </div>
                            </div>

                            <div class="col-lg-3 mt-4">
                                <button type="submit" class="btn btn-success ml-5">Simpan</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <hr class="my-4">

            <!-- TABEL & MODAL -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="table-responsive text-nowrap">
                        <table id="pengaturanUsulanTable" class="table table-sm table-hover">
                            <thead style="background-color: #dbdee0;">
                                <tr>
                                    <th>Jenis Usulan</th>
                                    <th>Tahun</th>
                                    <th>Bulan</th>
                                    <th>Pencairan ke</th>
                                    <th>Tanggal Mulai</th>
                                    <th>Tanggal Selesai</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="table-border-bottom-0">
                                @foreach ($pengaturanUsulan as $data)
                                <tr>
                                    <td>{{ $data->jenis_usulan ?? '-' }}</td>
                                    <td>{{ $data->tahun }}</td>
                                    <td>{{ $data->bulan }}</td>
                                    <td>{{ $data->pencairan_ke }}</td>
                                    <td>{{ $data->tanggal_mulai }}</td>
                                    <td>{{ $data->tanggal_selesai }}</td>
                                    <td>{{ $data->status }}</td>
                                    <td>
                                        @php
                                            $isChecked = false;
                                            if (is_array($configAktif) && isset($configAktif[$data->jenis_usulan])) {
                                                $entry = $configAktif[$data->jenis_usulan];
                                                // Wajib match pencairan_ke DAN tahun.
                                                // Tanpa cek tahun, jika ada pencairan_ke yang sama di tahun berbeda,
                                                // maka keduanya bisa terlihat aktif saat balik ke halaman ini.
                                                $isChecked = ((int) ($entry['pencairan_ke'] ?? 0) === (int) $data->pencairan_ke)
                                                  && ((string) ($entry['tahun'] ?? '') === (string) $data->tahun);
                                            }
                                        @endphp
                                        <button type="button" class="btn btn-icon btn-sm btn-warning edit-usulan"
                                            data-id="{{ $data->id }}" data-tahun="{{ $data->tahun }} "
                                            data-bulan="{{ $data->bulan }}"
                                            data-pencairan_ke="{{ $data->pencairan_ke }}"
                                            data-tanggal_mulai="{{ $data->tanggal_mulai }}"
                                            data-tanggal_selesai="{{ $data->tanggal_selesai }}"
                                            data-status="{{ $data->status }}" data-bs-toggle="modal"
                                            data-bs-target="#modalUsulanForm">
                                            <i class="bx bx-edit"></i>
                                        </button>

                                        @if ($isChecked)
                                            {{-- Aktif: tampil hijau ceklis, klik => noncek --}}
                                            <button type="button" class="toggle-ceklist btn btn-icon btn-sm btn-success ms-1" title="Non-Ceklis"
                                                data-action="noncek" data-id="{{ $data->id }}" data-jenis="{{ $data->jenis_usulan }}">
                                                <i class="bx bx-check"></i>
                                            </button>
                                        @else
                                            {{-- Non-aktif: tampil ikon larangan merah, klik => cek --}}
                                            <button type="button" class="toggle-ceklist btn btn-icon btn-sm btn-danger ms-1" title="Ceklis"
                                                data-action="cek" data-id="{{ $data->id }}" data-jenis="{{ $data->jenis_usulan }}">
                                                <i class="bx bx-block"></i>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        if (typeof $ !== 'undefined' && $.fn.DataTable) {
                            $('#pengaturanUsulanTable').DataTable({
                                paging: true,
                                searching: true,
                                ordering: true,
                                order: [[1, 'desc']], // default order by Tahun desc
                                columnDefs: [
                                    { orderable: false, searchable: false, targets: 7 } // disable sorting/search on Aksi column
                                ],
                                lengthChange: true,
                                pageLength: 100,
                                lengthMenu: [[25, 50, 100, 250], [25, 50, 100, 250]],
                                language: {
                                    paginate: { first: 'Awal', last: 'Akhir', next: '→', previous: '←' },
                                    zeroRecords: 'Data tidak ditemukan',
                                    infoEmpty: 'Tidak ada data tersedia',
                                    searchPlaceholder: 'Cari data...',
                                    search: 'Cari:'
                                }
                            });
                        }
                    });
                    </script>

                    <!-- Modal Edit -->
                    <div class="modal fade" id="modalUsulanForm" tabindex="-1" aria-labelledby="modalUsulanFormLabel"
                        aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form id="usulanForm" method="POST">
                                    @csrf
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalUsulanTitle">Edit Pengaturan Usulan</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <input type="hidden" name="_method" id="formMethod" value="PUT">
                                    <input type="hidden" id="usulanId" name="id">
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label for="modal_tahun" class="form-label">Tahun</label>
                                            <input type="text" class="form-control" id="modal_tahun" name="tahun"
                                                readonly style="background-color: #eceef1;">
                                        </div>
                                        <div class="mb-3">
                                            <label for="modal_bulan" class="form-label">Bulan</label>
                                            <input type="text" class="form-control" id="modal_bulan" name="bulan"
                                                readonly style="background-color: #eceef1;">
                                        </div>
                                        <div class="mb-3">
                                            <label for="modal_pencairan_ke" class="form-label">Pencairan ke</label>
                                            <input type="text" class="form-control" id="modal_pencairan_ke"
                                                name="pencairan_ke" readonly style="background-color: #eceef1;">
                                        </div>
                                        <div class="mb-3">
                                            <label for="modal_tanggal_mulai" class="form-label">Tanggal Mulai</label>
                                            <input type="date" class="form-control" id="modal_tanggal_mulai"
                                                name="tanggal_mulai" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="modal_tanggal_selesai" class="form-label">Tanggal
                                                Selesai</label>
                                            <input type="date" class="form-control" id="modal_tanggal_selesai"
                                                name="tanggal_selesai" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="modal_status" class="form-label">Status</label>
                                            <select class="form-select" id="modal_status" name="status" required>
                                                <option value="Aktifkan">Aktifkan</option>
                                                <option value="Nonaktifkan">Nonaktifkan</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <div id="modalSaveWarning" class="activation-warning d-none w-100 mb-2" role="alert"></div>
                                        <button type="submit" class="btn btn-primary">Simpan</button>
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Batal</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const usedSptjmPencairan = @json($existingSptjm);
const usedTukinPencairan = @json($existingTukin);
const maxSptjmPencairan = @json($maxSptjm);

function updatePencairanDropdown() {
    const jenis = document.getElementById('jenis_usulan').value;
    const year = document.getElementById('tahun').value;
    const dropdown = document.getElementById('pencairan_ke');

    dropdown.innerHTML = ''; // reset opsi

    let options = [];

    if (jenis === 'SPTJM') {
        const usedThisYear = (usedSptjmPencairan && usedSptjmPencairan[year]) ? usedSptjmPencairan[year] : [];
        const all = Array.from({
            length: 20
        }, (_, i) => i + 1);
        options = all.filter(num => !usedThisYear.includes(num));
    } else if (jenis === 'TUKIN') {
        const usedThisYear = (usedTukinPencairan && usedTukinPencairan[year]) ? usedTukinPencairan[year] : [];
        const maxAllowed = maxSptjmPencairan > 0 ? maxSptjmPencairan + 1 : 1;
        const all = Array.from({
            length: maxAllowed
        }, (_, i) => i + 1);
        options = all.filter(num => !usedThisYear.includes(num));
    }

    if (options.length) {
        options.forEach(num => {
            const opt = document.createElement('option');
            opt.value = num;
            opt.textContent = num;
            dropdown.appendChild(opt);
        });
    } else {
        const opt = document.createElement('option');
        opt.value = '';
        opt.textContent = 'Tidak ada opsi tersedia';
        dropdown.appendChild(opt);
    }
}

document.getElementById('jenis_usulan').addEventListener('change', updatePencairanDropdown);
document.getElementById('tahun').addEventListener('change', updatePencairanDropdown);

// initialize on load
updatePencairanDropdown();
</script>


<script>
document.addEventListener('DOMContentLoaded', function() {
    // SweetAlert untuk Notifikasi Sukses
    @if(session('add-success'))
    Swal.fire({
        title: 'Berhasil!',
        text: '{{ session('
        add - success ') }}',
        icon: 'success',
        customClass: {
            confirmButton: 'btn btn-primary'
        },
        buttonsStyling: false
    });
    @endif

    @if(session('edit-success'))
    Swal.fire({
        title: 'Berhasil!',
        text: '{{ session('
        edit - success ') }}',
        icon: 'success',
        customClass: {
            confirmButton: 'btn btn-primary'
        },
        buttonsStyling: false
    });
    @endif

    document.body.addEventListener('click', function(event) {
        if (event.target.closest('.edit-usulan')) {
            let button = event.target.closest('.edit-usulan');
            document.getElementById('modalUsulanTitle').innerText = 'Edit Pengaturan Usulan';
            document.getElementById('usulanId').value = button.dataset.id;
            document.getElementById('modal_tahun').value = button.dataset.tahun;
            document.getElementById('modal_bulan').value = button.dataset.bulan;
            document.getElementById('modal_pencairan_ke').value = button.dataset.pencairan_ke;
            document.getElementById('modal_tanggal_mulai').value = button.dataset.tanggal_mulai;
            document.getElementById('modal_tanggal_selesai').value = button.dataset.tanggal_selesai;
            document.getElementById('modal_status').value = button.dataset.status;
            document.getElementById('formMethod').value = 'PUT';
            document.getElementById('usulanForm').setAttribute('action',
                `/admin/pengaturan-usulan/${button.dataset.id}`);
        }
    });
});
</script>
<script>
// Prevent activating if tanggal_selesai is in the past — show modal warning via SweetAlert
function isDateBeforeToday(dateStr) {
    if (!dateStr) return false;
    const d = new Date(dateStr);
    const today = new Date();
    // zero time for comparison
    today.setHours(0,0,0,0);
    d.setHours(0,0,0,0);
    return d < today;
}

// Handler for forms: main create form and modal edit form
function attachActivateValidation(formSelector) {
    const form = document.querySelector(formSelector);
    if (!form) return;
    form.addEventListener('submit', function(e) {
        // find status value: support both radio inputs and select elements
        const statusRadio = form.querySelector('input[name="status"]:checked');
        const statusSelect = form.querySelector('select[name="status"]');
        const tanggalSelesaiInput = form.querySelector('input[name="tanggal_selesai"]');
        const statusVal = statusRadio ? statusRadio.value : (statusSelect ? statusSelect.value : null);
        const tanggalVal = tanggalSelesaiInput ? tanggalSelesaiInput.value : null;

        if (statusVal === 'Aktifkan' && isDateBeforeToday(tanggalVal)) {
            e.preventDefault();
            // Jika form berada dalam modal, tutup modal terlebih dahulu
            const modalEl = form.closest('.modal');
            if (modalEl) {
                const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                try { modalInstance.hide(); } catch (err) { /* ignore */ }
                // beri jeda kecil agar modal tertutup sebelum menampilkan SweetAlert
                setTimeout(() => {
                    Swal.fire({
                        title: 'Tidak bisa diaktifkan',
                        html: 'Tanggal selesai telah lewat sehingga pengaturan tidak dapat diaktifkan. Ubah tanggal selesai atau pilih status "Nonaktifkan".',
                        icon: 'warning',
                        customClass: { confirmButton: 'btn btn-primary' },
                        buttonsStyling: false
                    });
                }, 250);
            } else {
                Swal.fire({
                    title: 'Tidak bisa diaktifkan',
                    html: 'Tanggal selesai telah lewat sehingga pengaturan tidak dapat diaktifkan. Ubah tanggal selesai atau pilih status "Nonaktifkan".',
                    icon: 'warning',
                    customClass: { confirmButton: 'btn btn-primary' },
                    buttonsStyling: false
                });
            }
            return false;
        }
        // allow submit
    });
}

// Attach to both forms
attachActivateValidation('#pengaturanUsulanForm');
attachActivateValidation('#usulanForm');
</script>
<!-- Tampilkan modal dan label peringatan jika server mengembalikan error aktivasi -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    @if(session('activate-error'))
        const msg = "{{ session('activate-error') }}";
        // tampilkan SweetAlert sebagai modal peringatan gagal simpan
        Swal.fire({
            title: 'Gagal menyimpan',
            text: msg,
            icon: 'error',
            customClass: { confirmButton: 'btn btn-primary' },
            buttonsStyling: false
        });

        // tunjukkan di modal edit jika ada
        const modalSaveWarning = document.getElementById('modalSaveWarning');
        if (modalSaveWarning) {
            modalSaveWarning.textContent = msg;
            modalSaveWarning.classList.remove('d-none');
        }
    @endif
});
</script>
<script>
// Handle ceklis/non-ceklist via AJAX to avoid page reload
document.addEventListener('click', async function(e) {
    const btn = e.target.closest('.toggle-ceklist');
    if (!btn) return;
    e.preventDefault();
    e.stopPropagation();
    const action = btn.dataset.action; // 'cek' or 'noncek'
    const id = btn.dataset.id;
    const jenis = btn.dataset.jenis;
    btn.disabled = true;
    try {
        let res;
        if (action === 'cek') {
            res = await fetch(`/admin/pengaturan-usulan/${id}/ceklist`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            });
        } else {
            res = await fetch('/admin/pengaturan-usulan/non-ceklist', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify({ jenis: jenis })
            });
        }

        if (res.ok) {
            // Ensure only one checked per jenis: when checking, mark clicked as active (green) and others as non-active (red)
            if (action === 'cek') {
                const related = document.querySelectorAll(`.toggle-ceklist[data-jenis="${jenis}"]`);
                related.forEach(el => {
                    if (el === btn) {
                        // this becomes active (green)
                        el.classList.remove('btn-danger');
                        el.classList.add('btn-success');
                        el.dataset.action = 'noncek';
                        el.title = 'Non-Ceklis';
                        el.innerHTML = '<i class="bx bx-check"></i>';
                    } else {
                        // others become non-active (red)
                        el.classList.remove('btn-success');
                        el.classList.add('btn-danger');
                        el.dataset.action = 'cek';
                        el.title = 'Ceklis';
                        el.innerHTML = '<i class="bx bx-block"></i>';
                    }
                });
            } else {
                // clicking noncek => set this button back to non-active (red)
                btn.classList.remove('btn-success');
                btn.classList.add('btn-danger');
                btn.dataset.action = 'cek';
                btn.title = 'Ceklis';
                btn.innerHTML = '<i class="bx bx-block"></i>';
            }
        } else {
            console.error('Request failed', res.status);
            const errText = await res.text().catch(() => '');
            console.error(errText);
        }
    } catch (err) {
        console.error(err);
    } finally {
        btn.disabled = false;
    }
});
</script>
<style>
/* Styling khusus untuk label peringatan aktivasi (menyerupai screenshot) */
.activation-warning {
    background-color: #fdecea; /* very light red/pink */
    color: #c0392b; /* darker red text */
    border-radius: 10px;
    padding: 18px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.04);
    font-size: 14px;
    line-height: 1.5;
}

/* Letakkan box peringatan ke kanan dan batasi lebar agar tidak menumpuk */
/* main card warning removed; no styles needed */

/* Modal warning full width inside modal footer */
#modalSaveWarning {
    background-color: #fdecea;
    color: #c0392b;
}
</style>
@endsection