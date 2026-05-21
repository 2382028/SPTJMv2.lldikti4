@extends('layouts/contentNavbarLayout')

@section('title', 'SPTJM Online')

@section('content')
@php
    $rekapKurangRows = collect($rekapKurang ?? []);
    $rekapLebihRows = collect($rekapLebih ?? []);
@endphp

<div class="content-wrapper">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="mb-0">Hapus Rekap Terpilih</h5>
                        <div class="small text-muted">Tahun {{ $versi }}</div>
                    </div>
                    <a href="{{ route('admin.kekurangan-bayar') }}" class="btn btn-outline-secondary">Kembali</a>
                </div>
                <div class="card-body bg-white">

                    <div class="alert alert-warning mb-3">
                        Yang dihapus hanya <b>rekap</b> (Kurang/Lebih Bayar), tidak menghapus data detail pada tab Data.
                    </div>

                    <form action="{{ route('admin.kekurangan-bayar.destroy-rekap') }}" method="POST" id="formDestroyRekapSelected">
                @csrf
                @method('DELETE')

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <h6 class="mb-2">Rekap Kurang Bayar</h6>
                        @if($rekapKurangRows->isEmpty())
                            <div class="text-muted">Belum ada rekap kurang bayar.</div>
                        @else
                            <div class="list-group">
                                @foreach($rekapKurangRows as $rekap)
                                    <label class="list-group-item d-flex gap-2 align-items-start">
                                        <input class="form-check-input mt-1" type="checkbox" name="ids[]" value="{{ (int) $rekap->id }}">
                                        <div class="w-100">
                                            <div class="fw-semibold">{{ $rekap->periode }}</div>
                                            <div class="small text-muted">
                                                ID: {{ $rekap->id }} | Pegawai: {{ $rekap->pegawai }} | Tipe: {{ $rekap->tipe }} | Bank: {{ $rekap->bank }}
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="col-md-6 mb-3">
                        <h6 class="mb-2">Rekap Lebih Bayar</h6>
                        @if($rekapLebihRows->isEmpty())
                            <div class="text-muted">Belum ada rekap lebih bayar.</div>
                        @else
                            <div class="list-group">
                                @foreach($rekapLebihRows as $rekap)
                                    <label class="list-group-item d-flex gap-2 align-items-start">
                                        <input class="form-check-input mt-1" type="checkbox" name="ids[]" value="{{ (int) $rekap->id }}">
                                        <div class="w-100">
                                            <div class="fw-semibold">{{ $rekap->periode }}</div>
                                            <div class="small text-muted">
                                                ID: {{ $rekap->id }} | Pegawai: {{ $rekap->pegawai }} | Tipe: {{ $rekap->tipe }} | Bank: {{ $rekap->bank }}
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                        <div class="d-flex justify-content-end gap-2 mt-2">
                            <a href="{{ route('admin.kekurangan-bayar') }}" class="btn btn-outline-secondary">Batal</a>
                            <button type="submit" class="btn btn-danger">Hapus yang Dipilih</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap confirm modal (fallback when SweetAlert not available) -->
<div class="modal fade" id="bsConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bsConfirmModalTitle">Konfirmasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="bsConfirmModalBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" id="bsConfirmCancel">Batal</button>
                <button type="button" class="btn btn-danger" id="bsConfirmOk">OK</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('formDestroyRekapSelected');

        // Prefer SptjmAlert (SweetAlert wrapper), then legacy alertApi, else null
        const alertApi = (typeof window !== 'undefined') ? (window.SptjmAlert || window.alertApi || null) : null;

        // Bootstrap modal based confirm implementation (returns Promise<boolean>)
        const bsConfirm = (title, html, confirmText = 'OK') => {
            return new Promise((resolve) => {
                const modalEl = document.getElementById('bsConfirmModal');
                if (!modalEl) return resolve(false);
                const modalTitle = modalEl.querySelector('#bsConfirmModalTitle');
                const modalBody = modalEl.querySelector('#bsConfirmModalBody');
                const btnOk = modalEl.querySelector('#bsConfirmOk');
                const bsModal = new bootstrap.Modal(modalEl, { backdrop: 'static' });

                modalTitle.innerText = title || 'Konfirmasi';
                modalBody.innerHTML = html || '';
                btnOk.innerText = confirmText || 'OK';

                const cleanup = () => {
                    btnOk.removeEventListener('click', onOk);
                    modalEl.removeEventListener('hidden.bs.modal', onHidden);
                };

                const onOk = () => {
                    cleanup();
                    bsModal.hide();
                    resolve(true);
                };
                const onHidden = () => {
                    cleanup();
                    resolve(false);
                };

                btnOk.addEventListener('click', onOk);
                modalEl.addEventListener('hidden.bs.modal', onHidden);
                bsModal.show();
            });
        };

        if (!form) return;

        form.addEventListener('submit', async function (e) {
            e.preventDefault();

            const checked = form.querySelectorAll('input[name="ids[]"]:checked');
            if (!checked || checked.length === 0) {
                if (alertApi) {
                    await alertApi.warning('Peringatan', 'Pilih minimal 1 rekap untuk dihapus.');
                    return;
                }
                await bsConfirm('Peringatan', '<p>Pilih minimal 1 rekap untuk dihapus.</p>', 'OK');
                return;
            }

            const count = checked.length;
                        const html = `
                                <div class="text-start">
                                    <p>Anda akan menghapus <b>${count}</b> rekap terpilih untuk tahun <b>{{ $versi }}</b>.</p>
                                </div>
                        `;

            if (alertApi) {
                const r = await alertApi.question('Konfirmasi Hapus', html, { confirmButtonText: 'Ya, Hapus' });
                if (r && r.isConfirmed) form.submit();
                return;
            }

            const ok = await bsConfirm('Konfirmasi Hapus', html, 'Ya, Hapus');
            if (ok) form.submit();
        });
    });
</script>
@endpush
