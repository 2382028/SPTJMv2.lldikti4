<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>SPT 1721-VII (PNS)</title>
    <style>
        @page { margin: 18px 22px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #000; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .mt-8 { margin-top: 8px; }
        .mt-12 { margin-top: 12px; }
        .mt-16 { margin-top: 16px; }
        .box { border: 1px solid #000; padding: 8px; }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { border: 1px solid #000; padding: 6px 6px; vertical-align: top; }
        .label { width: 26%; }
        .value { width: 74%; }
        .small { font-size: 10px; }
        .signature { height: 70px; }
    </style>
</head>
<body>
    <div class="text-center">
        <div><strong>BUKTI PEMOTONGAN PAJAK PENGHASILAN PASAL 21</strong></div>
        <div class="small">BAGI PEGAWAI NEGERI SIPIL / ANGGOTA TNI / POLRI / PEJABAT NEGARA / PENSIUNAN</div>
        <div class="mt-8"><strong>FORMULIR 1721 - VII</strong></div>
    </div>

    <div class="mt-12 box">
        <div><strong>A. IDENTITAS PENERIMA PENGHASILAN YANG DIPOTONG</strong></div>
        <table class="table mt-8">
            <tr>
                <td class="label">1. NPWP</td>
                <td class="value">{{ $transaksi->npwp ?? $transaksi->NPWP ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">2. NIP / NRP</td>
                <td class="value">{{ $transaksi->nip ?? $transaksi->NIP ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">3. NAMA</td>
                <td class="value">{{ $transaksi->nama ?? $transaksi->NAMA ?? $transaksi->nama_dosen ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">4. ALAMAT</td>
                <td class="value">{{ $transaksi->alamat ?? $transaksi->ALAMAT ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">5. NIDN / NUPTK</td>
                <td class="value">{{ $transaksi->nidn ?? $transaksi->NUPTK ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">6. TAHUN</td>
                <td class="value">{{ $selectedYear ?: ($transaksi->tahun_versi ?? '-') }}</td>
            </tr>
        </table>
    </div>

    <div class="mt-12 box">
        <div><strong>B. RINCIAN PENGHASILAN DAN PENGHITUNGAN PPh PASAL 21</strong></div>
        <div class="small mt-8">(Template ini disiapkan sebagai layout. Mapping nilai dari tabel <em>s_transaksi_2</em> bisa diisi sesuai kolom yang tersedia.)</div>
        <table class="table mt-8">
            <tr>
                <th style="width:70%">Uraian</th>
                <th style="width:30%" class="text-right">Jumlah (Rp)</th>
            </tr>
            <tr>
                <td>Penghasilan Bruto</td>
                <td class="text-right">{{ $transaksi->penghasilan_bruto ?? $transaksi->bruto ?? '-' }}</td>
            </tr>
            <tr>
                <td>PPh Pasal 21 Dipotong</td>
                <td class="text-right">{{ $transaksi->pph21 ?? $transaksi->pph_21 ?? '-' }}</td>
            </tr>
        </table>
    </div>

    <div class="mt-12 box">
        <div><strong>C. IDENTITAS PEMOTONG</strong></div>
        <table class="table mt-8">
            <tr>
                <td class="label">1. NPWP</td>
                <td class="value">{{ $pemotong['npwp'] ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">2. NAMA</td>
                <td class="value">{{ $pemotong['nama'] ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">3. TANGGAL</td>
                <td class="value">{{ $pemotong['tanggal'] ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">4. TANDA TANGAN</td>
                <td class="value">
                    @if(!empty($pemotong_signature_path) && file_exists($pemotong_signature_path))
                        <img class="signature" src="{{ $pemotong_signature_path }}" alt="Tanda Tangan">
                    @else
                        -
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <div class="mt-16 small">
        <div>Catatan: Jika field penerima/penghasilan belum tampil, sesuaikan mapping kolom di template ini dengan struktur aktual di <em>s_transaksi_2</em>.</div>
    </div>
</body>
</html>
