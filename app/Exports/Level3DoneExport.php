<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class Level3DoneExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $submissions;
    
    public function __construct($submissions)
    {
        $this->submissions = $submissions;
    }

    public function collection()
    {
        return $this->submissions;
    }

    public function headings(): array
    {
        $header = [
            'No',
            'Tanggal Pengajuan',
            'Kode',
            'Sales',
            'Kode Kios',
            'Nama Kios',
            'Nama Customer',
            'Kota',
            'Status Target',
            'Jenis Pengajuan',
            'Plafon Aktif/Sebelumnya',
            'Plafon Baru',
            'Jumlah Value Faktur',
            'Komitmen Pembayaran',
            'Jenis Pembayaran',
            'Piutang',
            'Jml Over',
            'Jml OD 30',
            'Jml OD 60',
            'Jml OD 90',
            'Lampiran 1',
            'Lampiran 2',
            'Lampiran 3',
        ];
        
        // Kolom untuk setiap level (1 sampai 6)
        foreach ([1, 2, 3, 4, 5, 6] as $level) {
            $header[] = "Level {$level} - Nama Approver";
            $header[] = "Level {$level} - Status";
            $header[] = "Level {$level} - Tanggal";
            $header[] = "Level {$level} - Catatan";
        }
        
        $header[] = 'Status Akhir Pengajuan';
        
        return $header;
    }

    public function map($submission): array
    {
        static $index = 0;
        $index++;
        
        // Logic berdasarkan jenis pengajuan
        $plafonAktif = 0;
        if ($submission->plafon_type === 'rubah') {
            $plafonAktif = $submission->plafon_sebelumnya ?? ($submission->customer->plafon_aktif ?? 0);
        } elseif ($submission->plafon_type === 'open') {
            $plafonAktif = $submission->plafon ?? ($submission->customer->plafon_aktif ?? 0);
        }
        
        $plafonBaru = $submission->plafon ?? 0;
        
        // Jumlah Value Faktur (hanya untuk open plafon)
        $jumlahValueFaktur = 0;
        if ($submission->plafon_type === 'open' && $submission->jumlah_buka_faktur) {
            $jumlahValueFaktur = $submission->jumlah_buka_faktur;
        }
        
        // Status Target
        $targetStatus = '-';
        if ($submission->target_status === 'masuk_target') {
            $targetStatus = 'Masuk Target';
        } elseif ($submission->target_status === 'tidak_masuk_target') {
            $targetStatus = 'Tidak Masuk Target';
        } else {
            $level3Approval = $submission->approvals ? $submission->approvals->where('level', 3)->first() : null;
            if ($level3Approval && $level3Approval->target_status === 'masuk_target') {
                $targetStatus = 'Masuk Target';
            } elseif ($level3Approval && $level3Approval->target_status === 'tidak_masuk_target') {
                $targetStatus = 'Tidak Masuk Target';
            }
        }
        
        // Parse payment data
        $paymentData = null;
        if ($submission->payment_data) {
            $paymentData = is_array($submission->payment_data) 
                ? $submission->payment_data 
                : json_decode($submission->payment_data, true);
        }
        
        // Jenis Pembayaran
        $paymentType = strtolower(trim((string) $submission->payment_type));
        $jenisPembayaran = match ($paymentType) {
            'od' => 'OD',
            'over' => 'Over',
            default => $paymentType !== '' ? strtoupper($paymentType) : '-',
        };
        
        // Data OD/Over
        $piutang = 0;
        $jmlOver = 0;
        $jmlOd30 = 0;
        $jmlOd60 = 0;
        $jmlOd90 = 0;
        
        if ($paymentData && is_array($paymentData)) {
            $piutang = isset($paymentData['piutang']) ? (float)$paymentData['piutang'] : 0;
            $jmlOver = isset($paymentData['jml_over']) ? (float)$paymentData['jml_over'] : 0;
            $jmlOd30 = isset($paymentData['od_30']) ? (float)$paymentData['od_30'] : 0;
            $jmlOd60 = isset($paymentData['od_60']) ? (float)$paymentData['od_60'] : 0;
            $jmlOd90 = isset($paymentData['od_90']) ? (float)$paymentData['od_90'] : 0;
        }

        if ($piutang == 0 && isset($submission->customer->piutang)) {
            $piutang = (float) $submission->customer->piutang;
        }

        // Komitmen Pembayaran
        $komitmenPembayaran = $submission->komitmen_pembayaran ?: '-';

        // Parse lampiran paths
        $lampiranUrls = [];
        if ($submission->lampiran_path) {
            $paths = is_array($submission->lampiran_path) 
                ? $submission->lampiran_path 
                : json_decode($submission->lampiran_path, true);
            
            if (is_array($paths)) {
                foreach ($paths as $path) {
                    $lampiranUrls[] = 'https://plafon.kiu.co.id/' . ltrim($path, '/');
                }
            }
        }
        
        $row = [
            $index,
            $submission->created_at ? $submission->created_at->format('d-m-Y H:i') : '-',
            $submission->kode ?? '-',
            $submission->sales?->name ?? '-',
            $submission->customer?->kode_customer ?? '-',
            $submission->nama_kios ?? ($submission->customer?->nama_kios ?? '-'),
            $submission->nama ?? ($submission->customer?->nama ?? '-'),
            $submission->customer?->kota ?? '-',
            $targetStatus,
            $submission->plafon_type === 'open' ? 'Open Plafon' : 'Rubah Plafon',
            $plafonAktif,
            $plafonBaru,
            $jumlahValueFaktur,
            $komitmenPembayaran,
            $jenisPembayaran,
            $piutang,
            $jmlOver,
            $jmlOd30,
            $jmlOd60,
            $jmlOd90,
            $lampiranUrls[0] ?? '-',
            $lampiranUrls[1] ?? '-',
            $lampiranUrls[2] ?? '-',
        ];
        
        // Data approval per level (1, 2, 3, 4, 5, 6)
        foreach ([1, 2, 3, 4, 5, 6] as $level) {
            $approval = $submission->approvals ? $submission->approvals->where('level', $level)->first() : null;
            
            if ($approval) {
                $row[] = $approval->approver->name ?? '-';
                
                // Status approval
                if ($approval->status === 'approved') {
                    $row[] = 'Disetujui';
                } elseif ($approval->status === 'rejected') {
                    $row[] = 'Ditolak';
                } else {
                    $row[] = ucfirst($approval->status);
                }
                
                $row[] = $approval->created_at ? $approval->created_at->format('d-m-Y H:i') : '-';
                $row[] = $approval->note ?? '-';
            } else {
                $row[] = '-';
                $row[] = 'Belum Approve';
                $row[] = '-';
                $row[] = '-';
            }
        }
        
        // Status Akhir
        $statusMap = [
            'pending'               => 'Menunggu Manager SC',
            'approved_1'            => 'Menunggu Collection',
            'approved_2'            => 'Menunggu Manager Keuangan',
            'approved_3'            => 'Menunggu Kadep Keu & Sales',
            'approved_4'            => 'Menunggu Direktur Operasional',
            'approved_5'            => 'Menunggu Direktur Operasional',
            'pending_approver4'     => 'Menunggu Kadep Keu & Sales',
            'pending_approver5'     => 'Menunggu Direktur Operasional',
            'pending_approver6'     => 'Menunggu Direktur Operasional',
            'pending_viewer'        => 'Proses Input Viewer',
            'done'                  => 'Selesai',
            'rejected'              => 'Ditolak',
            'revision'              => 'Perlu Revisi',
        ];
        
        $row[] = $statusMap[$submission->status] ?? ucfirst($submission->status);
        
        return $row;
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();

        // Style header row
        $sheet->getStyle('1:1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Auto-fit row height
        $sheet->getDefaultRowDimension()->setRowHeight(18);
        $sheet->getRowDimension(1)->setRowHeight(26);

        // Freeze first row
        $sheet->freezePane('A2');

        if ($highestRow >= 2) {
            // Number formatting untuk kolom angka:
            // K: Plafon Aktif/Sebelumnya, L: Plafon Baru, M: Jumlah Value Faktur
            // P: Piutang, Q: Jml Over, R: Jml OD 30, S: Jml OD 60, T: Jml OD 90
            $sheet->getStyle("K2:M{$highestRow}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("P2:T{$highestRow}")->getNumberFormat()->setFormatCode('#,##0');

            // Alignment data
            $sheet->getStyle("A2:A{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B2:B{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C2:C{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("E2:E{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("H2:H{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("I2:I{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("J2:J{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("O2:O{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Styling kolom Status Target (Kolom I)
            for ($row = 2; $row <= $highestRow; $row++) {
                $targetVal = $sheet->getCell("I{$row}")->getValue();
                if ($targetVal === 'Masuk Target') {
                    $sheet->getStyle("I{$row}")->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'color' => ['rgb' => '065F46'], // Dark Green
                        ],
                        'fill' => [
                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'D1FAE5'], // Light Green background
                        ],
                    ]);
                } elseif ($targetVal === 'Tidak Masuk Target') {
                    $sheet->getStyle("I{$row}")->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'color' => ['rgb' => '991B1B'], // Dark Red
                        ],
                        'fill' => [
                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'FEE2E2'], // Light Red background
                        ],
                    ]);
                }
            }

            // Wrap text & vertical top untuk kolom Komitmen Pembayaran (Kolom N)
            $sheet->getStyle("N2:N{$highestRow}")->getAlignment()->setWrapText(true);
            $sheet->getStyle("N2:N{$highestRow}")->getAlignment()->setVertical(Alignment::VERTICAL_TOP);

            // Kolom catatan approver: AA (L1), AE (L2), AI (L3), AM (L4), AQ (L5), AU (L6)
            $catatanCols = ['AA', 'AE', 'AI', 'AM', 'AQ', 'AU'];
            foreach ($catatanCols as $cCol) {
                $sheet->getStyle("{$cCol}2:{$cCol}{$highestRow}")->getAlignment()->setWrapText(true);
                $sheet->getStyle("{$cCol}2:{$cCol}{$highestRow}")->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
            }

            // Buat hyperlink untuk kolom lampiran (U, V, W)
            $lampiranCols = ['U', 'V', 'W'];
            for ($row = 2; $row <= $highestRow; $row++) {
                foreach ($lampiranCols as $col) {
                    $cellValue = $sheet->getCell("{$col}{$row}")->getValue();
                    if ($cellValue && $cellValue !== '-' && str_starts_with($cellValue, 'http')) {
                        $sheet->getCell("{$col}{$row}")->getHyperlink()->setUrl($cellValue);
                        $sheet->getCell("{$col}{$row}")->setValue('Lihat Lampiran');
                        $sheet->getStyle("{$col}{$row}")->applyFromArray([
                            'font' => [
                                'color' => ['rgb' => '0563C1'],
                                'underline' => true,
                            ]
                        ]);
                    }
                }
            }
        }

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,   // No
            'B' => 18,  // Tanggal Pengajuan
            'C' => 20,  // KODE
            'D' => 20,  // Sales
            'E' => 18,  // KODE KIOS
            'F' => 25,  // Nama Kios
            'G' => 25,  // Nama Customer
            'H' => 18,  // KOTA
            'I' => 20,  // Status Target
            'J' => 18,  // Jenis Pengajuan
            'K' => 22,  // Plafon Aktif/Sebelumnya
            'L' => 20,  // Plafon Baru
            'M' => 20,  // Jumlah Value Faktur
            'N' => 35,  // Komitmen Pembayaran
            'O' => 18,  // Jenis Pembayaran
            'P' => 18,  // Piutang
            'Q' => 18,  // Jml Over
            'R' => 16,  // Jml OD 30
            'S' => 16,  // Jml OD 60
            'T' => 16,  // Jml OD 90
            'U' => 16,  // Lampiran 1
            'V' => 16,  // Lampiran 2
            'W' => 16,  // Lampiran 3
            // Level 1
            'X' => 22,  // Level 1 - Nama Approver
            'Y' => 15,  // Level 1 - Status
            'Z' => 18,  // Level 1 - Tanggal
            'AA' => 35, // Level 1 - Catatan
            // Level 2
            'AB' => 22, // Level 2 - Nama Approver
            'AC' => 15, // Level 2 - Status
            'AD' => 18, // Level 2 - Tanggal
            'AE' => 35, // Level 2 - Catatan
            // Level 3
            'AF' => 22, // Level 3 - Nama Approver
            'AG' => 15, // Level 3 - Status
            'AH' => 18, // Level 3 - Tanggal
            'AI' => 35, // Level 3 - Catatan
            // Level 4
            'AJ' => 22, // Level 4 - Nama Approver
            'AK' => 15, // Level 4 - Status
            'AL' => 18, // Level 4 - Tanggal
            'AM' => 35, // Level 4 - Catatan
            // Level 5
            'AN' => 22, // Level 5 - Nama Approver
            'AO' => 15, // Level 5 - Status
            'AP' => 18, // Level 5 - Tanggal
            'AQ' => 35, // Level 5 - Catatan
            // Level 6
            'AR' => 22, // Level 6 - Nama Approver
            'AS' => 15, // Level 6 - Status
            'AT' => 18, // Level 6 - Tanggal
            'AU' => 35, // Level 6 - Catatan
            // Status Akhir
            'AV' => 25, // Status Akhir Pengajuan
        ];
    }
}
