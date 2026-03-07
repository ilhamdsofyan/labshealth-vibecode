<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MonthlyReportExport implements FromArray, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    public function __construct(
        protected array $report,
        protected string $title
    ) {}

    public function array(): array
    {
        $rows = [];
        $no = 1;

        foreach ($this->report['data'] as $item) {
            $rows[] = [
                $no++,
                $item['disease_name'],
                $item['SMA'],
                $item['GURU'],
                $item['KARYAWAN'],
                $item['UMUM'],
                $item['total'],
                $item['notes'],
            ];
        }

        // Totals row
        $rows[] = [
            '',
            'TOTAL',
            $this->report['totals']['SMA'],
            $this->report['totals']['GURU'],
            $this->report['totals']['KARYAWAN'],
            $this->report['totals']['UMUM'],
            $this->report['totals']['total'],
            '',
        ];

        return $rows;
    }

    public function headings(): array
    {
        return ['No', 'Nama Penyakit', 'SMA', 'GURU', 'KARYAWAN', 'UMUM', 'Total', 'Keterangan'];
    }

    public function title(): string
    {
        return $this->title;
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = count($this->report['data']) + 2; // +1 heading, +1 total

        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5'],
                ],
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            ],
            $lastRow => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E5E7EB'],
                ],
            ],
        ];
    }
}
