@php
  $months = [
    1 => 'Januari',
    2 => 'Februari',
    3 => 'Maret',
    4 => 'April',
    5 => 'Mei',
    6 => 'Juni',
    7 => 'Juli',
    8 => 'Agustus',
    9 => 'September',
    10 => 'Oktober',
    11 => 'November',
    12 => 'Desember',
  ];

  $fmt = function ($v) {
    return number_format((float) ($v ?? 0), 0, ',', '.');
  };

  $totals = [
    'gaji' => 0,
    'kotorTpd' => 0,
    'kotorTkgb' => 0,
    'pajakTpd' => 0,
    'pajakTkgb' => 0,
    'bersihTpd' => 0,
    'bersihTkgb' => 0,
  ];
@endphp

@if(!empty($transaksi))
  <div class="table-responsive">
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>Tahun</th>
          <th>Bulan</th>
          <th>Jabatan</th>
          <th>Kode Usulan</th>
          <th>Gol - Masa Kerja</th>
          <th>Gaji Kotor</th>
          <th>TPD Kotor</th>
          <th>TKGB Kotor</th>
          <th>Pajak TPD</th>
          <th>Pajak TKGB</th>
          <th>TPD Bersih</th>
          <th>TKGB Bersih</th>
          <th>No SP2D</th>
          <th>Tgl SP2D</th>
        </tr>
      </thead>
      <tbody>
        @for($i = 1; $i <= 12; $i++)
          @php
            $gaji = $gajiBulanan[$i-1] ?? 0;
            $tpd = $kotorTpd[$i-1] ?? 0;
            $tkgb = $kotorTkgb[$i-1] ?? 0;
            $ptpd = $pajakTpd[$i-1] ?? 0;
            $ptkgb = $pajakTkgb[$i-1] ?? 0;
            $btpd = $bersihTpd[$i-1] ?? 0;
            $btkgb = $bersihTkgb[$i-1] ?? 0;

            $totals['gaji'] += (float) $gaji;
            $totals['kotorTpd'] += (float) $tpd;
            $totals['kotorTkgb'] += (float) $tkgb;
            $totals['pajakTpd'] += (float) $ptpd;
            $totals['pajakTkgb'] += (float) $ptkgb;
            $totals['bersihTpd'] += (float) $btpd;
            $totals['bersihTkgb'] += (float) $btkgb;
          @endphp
          <tr>
            <td>{{ $selectedYear ?? '-' }}</td>
            <td>{{ $months[$i] }}</td>
            <td>{{ $jabatanBulanan[$i-1] ?? '-' }}</td>
            <td>{{ $kodeUsulanBulanan[$i-1] ?? '-' }}</td>
            <td>{{ ($golonganBulanan[$i-1] ?? '-') }} - {{ ($tahunBulanan[$i-1] ?? '-') }}</td>
            <td class="text-end">{{ $fmt($gaji) }}</td>
            <td class="text-end">{{ $fmt($tpd) }}</td>
            <td class="text-end">{{ $fmt($tkgb) }}</td>
            <td class="text-end">{{ $fmt($ptpd) }}</td>
            <td class="text-end">{{ $fmt($ptkgb) }}</td>
            <td class="text-end">{{ $fmt($btpd) }}</td>
            <td class="text-end">{{ $fmt($btkgb) }}</td>
            <td>{{ $noSp2d[$i-1] ?? '-' }}</td>
            <td>{{ $tglSp2d[$i-1] ?? '-' }}</td>
          </tr>
        @endfor

        <tr class="fw-bold">
          <td colspan="5" class="text-center">Jumlah</td>
          <td class="text-end">{{ $fmt($totals['gaji']) }}</td>
          <td class="text-end">{{ $fmt($totals['kotorTpd']) }}</td>
          <td class="text-end">{{ $fmt($totals['kotorTkgb']) }}</td>
          <td class="text-end">{{ $fmt($totals['pajakTpd']) }}</td>
          <td class="text-end">{{ $fmt($totals['pajakTkgb']) }}</td>
          <td class="text-end">{{ $fmt($totals['bersihTpd']) }}</td>
          <td class="text-end">{{ $fmt($totals['bersihTkgb']) }}</td>
          <td>-</td>
          <td>-</td>
        </tr>
        <tr class="fw-bold">
          <td colspan="6" class="text-center">Jumlah Selisih Bayar</td>
          <td class="text-end">{{ $fmt(($selisihTotals['selisihTpd'] ?? 0)) }}</td>
          <td class="text-end">{{ $fmt(($selisihTotals['selisihTkgb'] ?? 0)) }}</td>
          <td class="text-end">{{ $fmt(($selisihTotals['selisihPajakTpd'] ?? 0)) }}</td>
          <td class="text-end">{{ $fmt(($selisihTotals['selisihPajakTkgb'] ?? 0)) }}</td>
          <td class="text-end">{{ $fmt(($selisihTotals['selisihBersihTpd'] ?? 0)) }}</td>
          <td class="text-end">{{ $fmt(($selisihTotals['selisihBersihTkgb'] ?? 0)) }}</td>
          <td>-</td>
          <td>-</td>
        </tr>
      </tbody>
    </table>
  </div>
@endif
