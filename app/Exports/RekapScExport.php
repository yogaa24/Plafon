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
                    ->with(['sales', 'approvals'])
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

                // Section Title 1
                $sheet->setCellValue('B2', 'REKAP SC');
                $sheet->getStyle('B2')->getFont()->setBold(true)->setSize(12);
                $sheet->getRowDimension(2)->setRowHeight(22);

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

                // Helper to check Over/OD (menghitung pengajuan yang Masuk Target)
                $isOverOd = function($sub) {
                    if ($sub->target_status === 'masuk_target') {
                        return true;
                    }
                    $level3Approval = $sub->approvals ? $sub->approvals->where('level', 3)->first() : null;
                    return $level3Approval && $level3Approval->target_status === 'masuk_target';
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
                $scTotalRow = $currentRow;

                // Total Row at the bottom of Rekap SC
                $sheet->mergeCells("B{$scTotalRow}:C{$scTotalRow}");
                $sheet->setCellValue("B{$scTotalRow}", 'TOTAL');

                $cIdx = 4;
                foreach ($months as $mNum => $mInfo) {
                    $c1 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIdx);
                    $c2 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIdx + 1);
                    $c3 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIdx + 2);
                    
                    $sheet->setCellValue("{$c1}{$scTotalRow}", "=SUM({$c1}5:{$c1}{$lastDataRow})");
                    $sheet->setCellValue("{$c2}{$scTotalRow}", "=SUM({$c2}5:{$c2}{$lastDataRow})");
                    $sheet->setCellValue("{$c3}{$scTotalRow}", "=IF({$c1}{$scTotalRow}>0, {$c2}{$scTotalRow}/{$c1}{$scTotalRow}, 0)");
                    
                    $sheet->getStyle("{$c3}{$scTotalRow}")->getNumberFormat()->setFormatCode('0.00%');
                    
                    $cIdx += 3;
                }

                // Styling Total Row Rekap SC
                $sheet->getStyle("B{$scTotalRow}:{$lastCol}{$scTotalRow}")->applyFromArray([
                    'font' => ['name' => 'Calibri', 'size' => 11, 'bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2F2F2']],
                ]);

                // Apply borders Rekap SC
                $sheet->getStyle("B3:{$lastCol}{$scTotalRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                // Alignments Rekap SC
                $sheet->getStyle("B4:{$lastCol}{$scTotalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("B4:{$lastCol}{$scTotalRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle("C5:C{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                // Row Heights Rekap SC
                $sheet->getRowDimension(3)->setRowHeight(24);
                $sheet->getRowDimension(4)->setRowHeight(22);
                for ($r = 5; $r <= $scTotalRow; $r++) {
                    $sheet->getRowDimension($r)->setRowHeight(20);
                }

                // ----------------------------------------------------
                // REKAP COLLECTION (Di bawah Rekap SC)
                // ----------------------------------------------------
                // Helper to get Collection user ID from submission (Approval Level 2)
                $getCollectionId = function($sub) {
                    $lvl2 = $sub->approvals->where('level', 2)->where('status', 'approved')->last()
                        ?: $sub->approvals->where('level', 2)->last();
                    return $lvl2 ? $lvl2->approver_id : null;
                };

                // Get distinct collection users from submissions in this year or all approver2 users
                $collectionUserIds = $submissions->map(function($sub) use ($getCollectionId) {
                    return $getCollectionId($sub);
                })->unique()->filter();

                $collectionUsers = User::whereIn('id', $collectionUserIds)
                    ->orWhere('role', 'approver2')
                    ->orderBy('name')
                    ->get();

                $t2TitleRow = $scTotalRow + 3;
                $sheet->setCellValue("B{$t2TitleRow}", 'REKAP COLLECTION');
                $sheet->getStyle("B{$t2TitleRow}")->getFont()->setBold(true)->setSize(12);
                $sheet->getRowDimension($t2TitleRow)->setRowHeight(22);

                $t2MonthRow = $t2TitleRow + 1;
                $t2HeaderRow = $t2MonthRow + 1;

                // Month Headers for Collection
                $colIndex = 4;
                foreach ($months as $mNum => $mInfo) {
                    $col1 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
                    $col2 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
                    $col3 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 2);
                    
                    // Month Header (Merged 3 cols)
                    $sheet->mergeCells("{$col1}{$t2MonthRow}:{$col3}{$t2MonthRow}");
                    $sheet->setCellValue("{$col1}{$t2MonthRow}", $mInfo['code']);
                    $sheet->getStyle("{$col1}{$t2MonthRow}:{$col3}{$t2MonthRow}")->applyFromArray([
                        'font' => ['name' => 'Calibri', 'size' => 11, 'bold' => true, 'color' => ['rgb' => '000000']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $mInfo['fill']]],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    ]);
                    
                    // Total, Over/OD, %
                    $sheet->setCellValue("{$col1}{$t2HeaderRow}", 'Total');
                    $sheet->setCellValue("{$col2}{$t2HeaderRow}", 'Over/OD');
                    $sheet->setCellValue("{$col3}{$t2HeaderRow}", '%');
                    
                    $sheet->getStyle("{$col1}{$t2HeaderRow}")->applyFromArray([
                        'font' => ['name' => 'Calibri', 'size' => 11, 'bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '002060']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    ]);
                    
                    $sheet->getStyle("{$col2}{$t2HeaderRow}")->applyFromArray([
                        'font' => ['name' => 'Calibri', 'size' => 11, 'bold' => true, 'color' => ['rgb' => '000000']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFC000']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    ]);
                    
                    $sheet->getStyle("{$col3}{$t2HeaderRow}")->applyFromArray([
                        'font' => ['name' => 'Calibri', 'size' => 11, 'bold' => true, 'color' => ['rgb' => '000000']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '00B050']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    ]);
                    
                    $colIndex += 3;
                }

                // Sub-headers B & C for Collection
                $sheet->setCellValue("B{$t2HeaderRow}", 'No');
                $sheet->setCellValue("C{$t2HeaderRow}", 'COLLECTION');

                $sheet->getStyle("B{$t2HeaderRow}:C{$t2HeaderRow}")->applyFromArray([
                    'font' => ['name' => 'Calibri', 'size' => 11, 'bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '652523']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);

                // Populate Collection Data Rows
                $t2StartDataRow = $t2HeaderRow + 1;
                $t2CurrentRow = $t2StartDataRow;
                $noColl = 1;

                foreach ($collectionUsers as $collUser) {
                    $sheet->setCellValue("B{$t2CurrentRow}", $noColl++);
                    $sheet->setCellValue("C{$t2CurrentRow}", $collUser->name);
                    
                    $cIdx = 4;
                    foreach ($months as $mNum => $mInfo) {
                        $c1 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIdx);
                        $c2 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIdx + 1);
                        $c3 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIdx + 2);
                        
                        $collSubs = $submissions->filter(function($s) use ($collUser, $mNum, $getCollectionId) {
                            return (int)$s->created_at->format('n') === $mNum && $getCollectionId($s) == $collUser->id;
                        });
                            
                        $totalCount = $collSubs->count();
                        $overCount = $collSubs->filter($isOverOd)->count();
                        
                        if ($totalCount > 0) {
                            $sheet->setCellValue("{$c1}{$t2CurrentRow}", $totalCount);
                            $sheet->setCellValue("{$c2}{$t2CurrentRow}", $overCount);
                            $sheet->setCellValue("{$c3}{$t2CurrentRow}", "={$c2}{$t2CurrentRow}/{$c1}{$t2CurrentRow}");
                        } else {
                            $sheet->setCellValue("{$c1}{$t2CurrentRow}", '');
                            $sheet->setCellValue("{$c2}{$t2CurrentRow}", '');
                            $sheet->setCellValue("{$c3}{$t2CurrentRow}", "0.00%");
                        }
                        
                        $sheet->getStyle("{$c3}{$t2CurrentRow}")->getNumberFormat()->setFormatCode('0.00%');
                        $cIdx += 3;
                    }
                    
                    $t2CurrentRow++;
                }

                $t2LastDataRow = $t2CurrentRow - 1;
                $t2TotalRow = $t2CurrentRow;

                // Total Row for Collection
                $sheet->mergeCells("B{$t2TotalRow}:C{$t2TotalRow}");
                $sheet->setCellValue("B{$t2TotalRow}", 'TOTAL');

                $cIdx = 4;
                foreach ($months as $mNum => $mInfo) {
                    $c1 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIdx);
                    $c2 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIdx + 1);
                    $c3 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIdx + 2);
                    
                    $sheet->setCellValue("{$c1}{$t2TotalRow}", "=SUM({$c1}{$t2StartDataRow}:{$c1}{$t2LastDataRow})");
                    $sheet->setCellValue("{$c2}{$t2TotalRow}", "=SUM({$c2}{$t2StartDataRow}:{$c2}{$t2LastDataRow})");
                    $sheet->setCellValue("{$c3}{$t2TotalRow}", "=IF({$c1}{$t2TotalRow}>0, {$c2}{$t2TotalRow}/{$c1}{$t2TotalRow}, 0)");
                    
                    $sheet->getStyle("{$c3}{$t2TotalRow}")->getNumberFormat()->setFormatCode('0.00%');
                    
                    $cIdx += 3;
                }

                // Styling Total Row Collection
                $sheet->getStyle("B{$t2TotalRow}:{$lastCol}{$t2TotalRow}")->applyFromArray([
                    'font' => ['name' => 'Calibri', 'size' => 11, 'bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2F2F2']],
                ]);

                // Apply borders Collection
                $sheet->getStyle("B{$t2MonthRow}:{$lastCol}{$t2TotalRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                // Alignments Collection
                $sheet->getStyle("B{$t2HeaderRow}:{$lastCol}{$t2TotalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("B{$t2HeaderRow}:{$lastCol}{$t2TotalRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle("C{$t2StartDataRow}:C{$t2LastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                // Row Heights Collection
                $sheet->getRowDimension($t2MonthRow)->setRowHeight(24);
                $sheet->getRowDimension($t2HeaderRow)->setRowHeight(22);
                for ($r = $t2StartDataRow; $r <= $t2TotalRow; $r++) {
                    $sheet->getRowDimension($r)->setRowHeight(20);
                }

                // Column Widths
                $sheet->getColumnDimension('A')->setWidth(3);
                $sheet->getColumnDimension('B')->setWidth(7);
                $sheet->getColumnDimension('C')->setWidth(18);
                for ($i = 4; $i <= $colIndex - 1; $i++) {
                    $c = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
                    $sheet->getColumnDimension($c)->setWidth(10);
                }
            },
        ];
    }
}
