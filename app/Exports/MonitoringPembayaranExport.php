<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MonitoringPembayaranExport implements FromArray, WithHeadings, WithStyles, WithColumnFormatting, ShouldAutoSize
{
    /** @var array<int, array<int, mixed>> */
    private array $rows;

    private int $rowCount;

    /** @var array<int, string> */
    private array $headings;

    /** @var array<string, string> */
    private array $mergeEndByLabel;

    private string $mergeStartColumn;

    /**
     * @param array<int, array<int, mixed>> $rows
     * @param array<int, string>|null $headings
     * @param array<string, string>|null $mergeEndByLabel map: label in column B => end column (e.g., 'Jumlah' => 'D')
     */
    public function __construct(array $rows, ?array $headings = null, ?array $mergeEndByLabel = null, string $mergeStartColumn = 'B')
    {
        $this->rows = $rows;
        $this->rowCount = count($rows);

        $this->headings = $headings ?: [
            'Tahun',
            'Bulan',
            'Kode Usulan',
            'Pangkat Golongan',
            'Gaji',
            'Kotor TPD',
            'Kotor TKGB',
            'Pajak TPD',
            'Pajak TKGB',
            'Bersih TPD',
            'Bersih TKGB',
            'NO SP2D',
            'TGL SP2D',
        ];

        // Default behavior matches admin monitoring layout:
        // - 'Jumlah' merges Bulan..Pangkat Golongan (B:D)
        // - 'Jumlah Selisih Bayar' merges Bulan..Gaji (B:E)
        $this->mergeEndByLabel = $mergeEndByLabel ?: [
            'Jumlah' => 'D',
            'Jumlah Selisih Bayar' => 'E',
        ];

        $this->mergeStartColumn = $mergeStartColumn;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function columnFormats(): array
    {
        // Money columns depend on whether there is an extra "Jabatan" column.
        $hasJabatan = in_array('Jabatan', $this->headings, true);
        $start = $hasJabatan ? 'F' : 'E';
        $cols = [$start, chr(ord($start) + 1), chr(ord($start) + 2), chr(ord($start) + 3), chr(ord($start) + 4), chr(ord($start) + 5), chr(ord($start) + 6)];

        $out = [];
        foreach ($cols as $c) {
            $out[$c] = '#,##0';
        }
        return $out;
    }

    public function styles(Worksheet $sheet)
    {
        $lastCol = $this->columnLetterFromIndex(count($this->headings));

        // Header style
        $sheet->getStyle('A1:' . $lastCol . '1')->getFont()->setBold(true);
        $sheet->getStyle('A1:' . $lastCol . '1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE5E7EB');

        // Borders for table range (header + rows)
        $lastRow = 1 + $this->rowCount;
        if ($lastRow < 1) {
            $lastRow = 1;
        }
        $range = 'A1:' . $lastCol . $lastRow;
        $sheet->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Merge totals/selisih rows based on label value in column "Bulan" (col B)
        if ($this->rowCount > 0) {
            for ($i = 0; $i < $this->rowCount; $i++) {
                $label = (string) ($this->rows[$i][1] ?? '');
                if ($label === '') {
                    continue;
                }
                if (!isset($this->mergeEndByLabel[$label])) {
                    continue;
                }

                $rowIndex = 2 + $i; // row 1 is header
                $endCol = $this->mergeEndByLabel[$label];

                // bold the special rows
                $sheet->getStyle('A' . $rowIndex . ':' . $lastCol . $rowIndex)->getFont()->setBold(true);

                try {
                    $sheet->mergeCells($this->mergeStartColumn . $rowIndex . ':' . $endCol . $rowIndex);
                    $sheet->getStyle($this->mergeStartColumn . $rowIndex . ':' . $endCol . $rowIndex)
                        ->getAlignment()
                        ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                        ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                } catch (\Exception $e) {
                    // ignore merge errors
                }
            }
        }

        return [];
    }

    private function columnLetterFromIndex(int $index): string
    {
        // 1 => A, 26 => Z, 27 => AA
        $index = max(1, $index);
        $letters = '';
        while ($index > 0) {
            $mod = ($index - 1) % 26;
            $letters = chr(65 + $mod) . $letters;
            $index = (int) floor(($index - 1) / 26);
        }
        return $letters;
    }
}
