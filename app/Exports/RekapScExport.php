<?php

namespace App\Exports;

use App\Models\Submission;
use App\Models\User;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class RekapScExport implements WithEvents, WithTitle
{
    protected $year;

    public function __construct($year = null)
    {
        $this->year = $year ?: (int)date('Y');
    }

    public function title(): string
    {
        return 'REKAP SC';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Months definition matching rekap sc.xlsx template
                $months = [
                    1  => ['code' => 'JAN',       'fill' => 'C7DAF1'],
                    2  => ['code' => 'FEB',       'fill' => 'DDD9C3'],
                    3  => ['code' => 'MARET',     'fill' => 'F2DBDB'],
                    4  => ['code' => 'APRIL',     'fill' => 'EBF1DE'],
                    5  => ['code' => 'MEI',       'fill' => 'E6E0ED'],
                    6  => ['code' => 'JUNI',      'fill' => 'DBEFF4'],
                    7  => ['code' => 'JULI',      'fill' => 'FDEADA'],
                    8  => ['code' => 'AGUSTUS',   'fill' => 'CCC1DA'],
                    9  => ['code' => 'SEPTEMBER', 'fill' => 'E6B9B8'],
                    10 => ['code' => 'OKTOBER',   'fill' => '8DB4E3'],
                    11 => ['code' => 'NOVEMBER',  'fill' => 'D7E4BD'],
                    12 => ['code' => 'DESEMBER',  'fill' => 'FCD5B6'],
                ];

                // Fetch submissions for the selected year (hanya Open Plafon yang disetujui / tidak rejected)
                $submissions = Submission::whereYear('created_at', $this->year)
                    ->where('plafon_type', 'open')
                    ->whereIn('status', [
                        'approved_1',
                        'approved_2',
                        'approved_3',
                        'approved_4',
                        'approved_5',
                        'approved_6',
                        'pending_viewer',
                        'done'
                    ])
                    ->with('sales')
                    ->get();

                // Get distinct sales IDs that have submissions in this year or all sales users
                $salesWithSubs = $submissions->pluck('sales_id')->unique()->filter();
                
                $salesUsers = User::whereIn('id', $salesWithSubs)
                    ->orWhere(function($q) {
                        $q->whereIn('role', ['sales', 'sales_executive'])
                          ->whereNotIn('name', ['Others', 'sales executive']);
                    })
                    ->orderBy('name')
                    ->get();

                // Sub-headers B4, C4
                $sheet->setCellValue('B4', 'No');
                $sheet->setCellValue('C4', 'SC');

                $sheet->getStyle('B4:C4')->applyFromArray([
                    'font' => ['name' => 'Calibri', 'size' => 11, 'bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '652523']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);

                $colIndex = 4; // Start at Column D
                foreach ($months as $mNum => $mInfo) {
                    $col1 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
                    $col2 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
                    $col3 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 2);
                    
                    // Row 3: Month Header (Merged 3 cols)
                    $sheet->mergeCells("{$col1}3:{$col3}3");
                    $sheet->setCellValue("{$col1}3", $mInfo['code']);
                    $sheet->getStyle("{$col1}3:{$col3}3")->applyFromArray([
                        'font' => ['name' => 'Calibri', 'size' => 11, 'bold' => true, 'color' => ['rgb' => '000000']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $mInfo['fill']]],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    ]);
                    
                    // Row 4: Total, Over/OD, %
                    $sheet->setCellValue("{$col1}4", 'Total');
                    $sheet->setCellValue("{$col2}4", 'Over/OD');
                    $sheet->setCellValue("{$col3}4", '%');
                    
                    $sheet->getStyle("{$col1}4")->applyFromArray([
                        'font' => ['name' => 'Calibri', 'size' => 11, 'bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '002060']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    ]);
                    
                    $sheet->getStyle("{$col2}4")->applyFromArray([
                        'font' => ['name' => 'Calibri', 'size' => 11, 'bold' => true, 'color' => ['rgb' => '000000']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFC000']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    ]);
                    
                    $sheet->getStyle("{$col3}4")->applyFromArray([
                        'font' => ['name' => 'Calibri', 'size' => 11, 'bold' => true, 'color' => ['rgb' => '000000']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '00B050']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    ]);
                    
                    $colIndex += 3;
                }

                $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex - 1); // AM

                // Helper to check Over/OD (menghitung pengajuan yang Plafon Aktif / Sebelumnya bernilai 1.000)
                $isOverOd = function($sub) {
                    $plafon = (float)$sub->plafon;
                    $plafonSebelumnya = (float)$sub->plafon_sebelumnya;
                    return (round($plafon) == 1000 || round($plafonSebelumnya) == 1000);
                };

                // Populate Data Rows
                $currentRow = 5;
                $no = 1;

                foreach ($salesUsers as $sales) {
                    $sheet->setCellValue("B{$currentRow}", $no++);
                    $sheet->setCellValue("C{$currentRow}", $sales->name);
                    
                    $cIdx = 4;
                    foreach ($months as $mNum => $mInfo) {
                        $c1 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIdx);
                        $c2 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIdx + 1);
                        $c3 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIdx + 2);
                        
                        $salesSubs = $submissions->where('sales_id', $sales->id)
                            ->filter(function($s) use ($mNum) {
                                return (int)$s->created_at->format('n') === $mNum;
                            });
                            
                        $totalCount = $salesSubs->count();
                        $overCount = $salesSubs->filter($isOverOd)->count();
                        
                        if ($totalCount > 0) {
                            $sheet->setCellValue("{$c1}{$currentRow}", $totalCount);
                            $sheet->setCellValue("{$c2}{$currentRow}", $overCount);
                            $sheet->setCellValue("{$c3}{$currentRow}", "={$c2}{$currentRow}/{$c1}{$currentRow}");
                        } else {
                            $sheet->setCellValue("{$c1}{$currentRow}", '');
                            $sheet->setCellValue("{$c2}{$currentRow}", '');
                            $sheet->setCellValue("{$c3}{$currentRow}", "0.00%");
                        }
                        
                        $sheet->getStyle("{$c3}{$currentRow}")->getNumberFormat()->setFormatCode('0.00%');
                        $cIdx += 3;
                    }
                    
                    $currentRow++;
                }

                $lastDataRow = $currentRow - 1;

                // Total Row at the bottom
                $sheet->mergeCells("B{$currentRow}:C{$currentRow}");
                $sheet->setCellValue("B{$currentRow}", 'TOTAL');

                $cIdx = 4;
                foreach ($months as $mNum => $mInfo) {
                    $c1 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIdx);
                    $c2 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIdx + 1);
                    $c3 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIdx + 2);
                    
                    $sheet->setCellValue("{$c1}{$currentRow}", "=SUM({$c1}5:{$c1}{$lastDataRow})");
                    $sheet->setCellValue("{$c2}{$currentRow}", "=SUM({$c2}5:{$c2}{$lastDataRow})");
                    $sheet->setCellValue("{$c3}{$currentRow}", "=IF({$c1}{$currentRow}>0, {$c2}{$currentRow}/{$c1}{$currentRow}, 0)");
                    
                    $sheet->getStyle("{$c3}{$currentRow}")->getNumberFormat()->setFormatCode('0.00%');
                    
                    $cIdx += 3;
                }

                // Styling Total Row
                $sheet->getStyle("B{$currentRow}:{$lastCol}{$currentRow}")->applyFromArray([
                    'font' => ['name' => 'Calibri', 'size' => 11, 'bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2F2F2']],
                ]);

                // Apply borders
                $sheet->getStyle("B3:{$lastCol}{$currentRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                // Alignments
                $sheet->getStyle("B4:{$lastCol}{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("B4:{$lastCol}{$currentRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle("C5:C{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                // Column Widths
                $sheet->getColumnDimension('A')->setWidth(3);
                $sheet->getColumnDimension('B')->setWidth(7);
                $sheet->getColumnDimension('C')->setWidth(16);
                for ($i = 4; $i <= $colIndex - 1; $i++) {
                    $c = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
                    $sheet->getColumnDimension($c)->setWidth(10);
                }

                // Row Heights
                $sheet->getRowDimension(3)->setRowHeight(24);
                $sheet->getRowDimension(4)->setRowHeight(22);
                for ($r = 5; $r <= $currentRow; $r++) {
                    $sheet->getRowDimension($r)->setRowHeight(20);
                }
            },
        ];
    }
}
