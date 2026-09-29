<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class CustomerPiutangExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $customers;
    private $rowNumber = 0;

    public function __construct($customers)
    {
        $this->customers = $customers;
    }

    public function collection()
    {
        return $this->customers;
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Customer',
            'Nama Customer',
            'Nama Kios',
            'Alamat',
            'Nama Sales',
            'Plafon Aktif',
            'Piutang',
            'Sisa Plafon',
            'Status',
        ];
    }

    public function map($customer): array
    {
        $this->rowNumber++;

        $plafon = (float)($customer->plafon_aktif ?? 0);
        $piutang = (float)($customer->piutang ?? 0);
        $sisaPlafon = $plafon - $piutang;

        return [
            $this->rowNumber,
            $customer->kode_customer,
            $customer->nama,
            $customer->nama_kios ?? '-',
            $customer->alamat ?? '-',
            $customer->sales->name ?? ($customer->nama_sales ?? '-'),
            $plafon,
            $piutang,
            $sisaPlafon,
            $customer->status === 'active' ? 'Aktif' : 'Nonaktif',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();

        // Style header row
        $sheet->getStyle('A1:J1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E3A8A'] // Deep Blue
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(26);
        $sheet->freezePane('A2');

        if ($highestRow >= 2) {
            // Number formatting for Plafon, Piutang, Sisa Plafon (Columns G, H, I)
            $sheet->getStyle("G2:I{$highestRow}")->getNumberFormat()->setFormatCode('#,##0');

            // Alignment
            $sheet->getStyle("A2:A{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B2:B{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("J2:J{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G2:I{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("C2:F{$highestRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

            // Borders
            $sheet->getStyle("A1:J{$highestRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'D1D5DB'],
                    ],
                ],
            ]);

            // Status color highlight
            for ($row = 2; $row <= $highestRow; $row++) {
                $statusVal = $sheet->getCell("J{$row}")->getValue();
                if ($statusVal === 'Aktif') {
                    $sheet->getStyle("J{$row}")->applyFromArray([
                        'font' => ['color' => ['rgb' => '065F46'], 'bold' => true],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'D1FAE5'],
                        ],
                    ]);
                } else {
                    $sheet->getStyle("J{$row}")->applyFromArray([
                        'font' => ['color' => ['rgb' => '4B5563']],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'F3F4F6'],
                        ],
                    ]);
                }
            }
        }

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,   // No
            'B' => 16,  // Kode Customer
            'C' => 30,  // Nama Customer
            'D' => 25,  // Nama Kios
            'E' => 35,  // Alamat
            'F' => 22,  // Nama Sales
            'G' => 18,  // Plafon Aktif
            'H' => 18,  // Piutang
            'I' => 18,  // Sisa Plafon
            'J' => 14,  // Status
        ];
    }
}
