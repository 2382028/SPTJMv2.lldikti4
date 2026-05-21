@php
$transaksi = $transaksi ?? null;
$months = [
  'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember',
];
@endphp

@if (!$transaksi)
  <div class="alert alert-warning" role="alert">
    {{ $errorMessage ?? 'Data pembayaran tidak tersedia.' }}
  </div>
@else
  <div class="table-responsive text-nowrap mt-4" style="overflow:auto; padding-right:0;">
    <table class="table table-bordered table-hover" style="width:100%">
      <thead>
        <tr>
          <th rowspan="2" class="text-center">Tahun</th>
          <th rowspan="2" class="text-center">Bulan</th>
          <th rowspan="2" class="text-center">Jabatan</th>
          <th rowspan="2" class="text-center">Kode Usulan</th>
          <th rowspan="2" class="text-center">Pangkat Golongan</th>
          <th rowspan="2" class="text-center">Gaji</th>
          <th colspan="6" class="text-center">Nominal</th>
          <th rowspan="2" class="text-center">NO SP2D</th>
          <th rowspan="2" class="text-center">TGL SP2D</th>
        </tr>
        <tr>
          <th class="text-center">Kotor TPD</th>
          <th class="text-center">Kotor TKGB</th>
          <th class="text-center">Pajak TPD</th>
          <th class="text-center">Pajak TKGB</th>
          <th class="text-center">Bersih TPD</th>
          <th class="text-center">Bersih TKGB</th>
        </tr>
      </thead>
      <tbody>
        @php
          $totalGaji = array_sum($gajiBulanan ?? []);
          $totalKotorTpd = array_sum($kotorTpd ?? []);
          $totalKotorTkgb = array_sum($kotorTkgb ?? []);
          $totalPajakTpd = array_sum($pajakTpd ?? []);
          $totalPajakTkgb = array_sum($pajakTkgb ?? []);
          $totalBersihTpd = array_sum($bersihTpd ?? []);
          $totalBersihTkgb = array_sum($bersihTkgb ?? []);
        @endphp

        @foreach ($months as $index => $month)
          <tr>
            <td class="text-center">{{ $selectedYear ?? '-' }}</td>
            <td>{{ $month }}</td>
            <td class="text-center">{{ $jabatanBulanan[$index] ?? '-' }}</td>
            <td class="text-center">{{ $kodeUsulanBulanan[$index] ?? '-' }}</td>
            <td class="text-center">{{ $golonganBulanan[$index] ?? '-' }} - {{ $tahunBulanan[$index] ?? '-' }}</td>
            <td class="text-center">{{ number_format($gajiBulanan[$index] ?? 0, 0, ',', '.') }}</td>
            <td class="text-end">{{ number_format($kotorTpd[$index] ?? 0, 0, ',', '.') }}</td>
            <td class="text-end">{{ number_format($kotorTkgb[$index] ?? 0, 0, ',', '.') }}</td>
            <td class="text-end">{{ number_format($pajakTpd[$index] ?? 0, 0, ',', '.') }}</td>
            <td class="text-end">{{ number_format($pajakTkgb[$index] ?? 0, 0, ',', '.') }}</td>
            <td class="text-end">{{ number_format($bersihTpd[$index] ?? 0, 0, ',', '.') }}</td>
            <td class="text-end">{{ number_format($bersihTkgb[$index] ?? 0, 0, ',', '.') }}</td>
            <td class="text-center">{{ $noSp2d[$index] ?? '-' }}</td>
            <td class="text-center">{{ $tglSp2d[$index] ?? '-' }}</td>
          </tr>
        @endforeach

        <tr class="fw-bold">
          <td colspan="5" class="text-center">Jumlah</td>
          <td class="text-end">{{ number_format($totalGaji, 0, ',', '.') }}</td>
          <td class="text-end">{{ number_format($totalKotorTpd, 0, ',', '.') }}</td>
          <td class="text-end">{{ number_format($totalKotorTkgb, 0, ',', '.') }}</td>
          <td class="text-end">{{ number_format($totalPajakTpd, 0, ',', '.') }}</td>
          <td class="text-end">{{ number_format($totalPajakTkgb, 0, ',', '.') }}</td>
          <td class="text-end">{{ number_format($totalBersihTpd, 0, ',', '.') }}</td>
          <td class="text-end">{{ number_format($totalBersihTkgb, 0, ',', '.') }}</td>
          <td></td>
          <td></td>
        </tr>

        <tr class="fw-bold">
          <td colspan="6" class="text-center">Jumlah Selisih Bayar</td>
          <td class="text-end">{{ number_format((float)($selisihTotals['selisihTpd'] ?? 0), 0, ',', '.') }}</td>
          <td class="text-end">{{ number_format((float)($selisihTotals['selisihTkgb'] ?? 0), 0, ',', '.') }}</td>
          <td class="text-end">{{ number_format((float)($selisihTotals['selisihPajakTpd'] ?? 0), 0, ',', '.') }}</td>
          <td class="text-end">{{ number_format((float)($selisihTotals['selisihPajakTkgb'] ?? 0), 0, ',', '.') }}</td>
          <td class="text-end">{{ number_format((float)($selisihTotals['selisihBersihTpd'] ?? 0), 0, ',', '.') }}</td>
          <td class="text-end">{{ number_format((float)($selisihTotals['selisihBersihTkgb'] ?? 0), 0, ',', '.') }}</td>
          <td></td>
          <td></td>
        </tr>
      </tbody>
    </table>
  </div>
@endif
