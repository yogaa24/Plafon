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
            'Nama Customer',
            'Nama Kios',
            'Alamat',
            'Jenis Pengajuan',
            'Status Target',
            'Plafon Aktif/Sebelumnya',
            'Plafon Baru',
            'Jumlah Value Faktur',
            'Sales',
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
        
        // Tambahkan kolom untuk setiap level (1, 2, 3, 4, 5, 6)
        $levelNames = [
            1 => 'Manager SC',
            2 => 'Collection',
            3 => 'Manager Keuangan',
            4 => 'Kadep Keu & Sales',
            5 => 'Direktur Operasional',
            6 => 'Direktur Operasional',
        ];

        foreach ([1, 2, 3, 4, 5, 6] as $level) {
            $role = $levelNames[$level] ?? "Level {$level}";
            $header[] = "{$role} - Nama Approver";
            $header[] = "{$role} - Status";
            $header[] = "{$role} - Tanggal";
            $header[] = "{$role} - Catatan";
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
        $plafonBaru = '';
        
        if ($submission->plafon_type === 'rubah') {
            $plafonAktif = $submission->plafon_sebelumnya ?? ($submission->customer->plafon_aktif ?? 0);
            $plafonBaru = $submission->plafon;
        } elseif ($submission->plafon_type === 'open') {
            $plafonAktif = $submission->plafon ?? 0;
            $plafonBaru = '-';
        }
        
        // Jumlah Value Faktur (hanya untuk open plafon)
        $jumlahValueFaktur = 0;
        if ($submission->plafon_type === 'open' && $submission->jumlah_buka_faktur) {
            $jumlahValueFaktur = $submission->jumlah_buka_faktur;
        }
        
        // Parse payment data
        $paymentData = null;
        if ($submission->payment_data) {
            $paymentData = is_array($submission->payment_data) 
                ? $submission->payment_data 
                : json_decode($submission->payment_data, true);
        }
        
        // Jenis Pembayaran
        $jenisPembayaran = $submission->payment_type ? strtoupper($submission->payment_type) : '-';
        
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

        // Status Target
        $targetStatus = '-';
        if ($submission->target_status === 'masuk_target') {
            $targetStatus = 'Masuk Target';
        } elseif ($submission->target_status === 'tidak_masuk_target') {
            $targetStatus = 'Tidak Masuk Target';
        } else {
            // Cek di approval level 3 jika ada
            $level3Approval = $submission->approvals ? $submission->approvals->where('level', 3)->first() : null;
            if ($level3Approval && $level3Approval->target_status === 'masuk_target') {
                $targetStatus = 'Masuk Target';
            } elseif ($level3Approval && $level3Approval->target_status === 'tidak_masuk_target') {
                $targetStatus = 'Tidak Masuk Target';
            }
        }

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
            $submission->created_at->format('d-m-Y H:i'),
            $submission->kode,
            $submission->nama,
            $submission->nama_kios,
            $submission->alamat,
            $submission->plafon_type === 'open' ? 'Open Plafon' : 'Rubah Plafon',
            $targetStatus,
            $plafonAktif,
            $plafonBaru,
            $jumlahValueFaktur,
            $submission->sales->name ?? '-',
            $submission->komitmen_pembayaran ?? '-',
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
                
                $row[] = $approval->created_at->format('d-m-Y H:i');
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
        $sheet->getDefaultRowDimension()->setRowHeight(15);
        $sheet->getRowDimension(1)->setRowHeight(25);

        // Freeze first row
        $sheet->freezePane('A2');

        // Buat hyperlink untuk kolom lampiran (T, U, V = kolom 20, 21, 22)
        $lampiranCols = ['T', 'U', 'V'];
        $highestRow = $sheet->getHighestRow();

        for ($row = 2; $row <= $highestRow; $row++) {
            // Styling kolom Status Target (Kolom H)
            $targetVal = $sheet->getCell("H{$row}")->getValue();
            if ($targetVal === 'Masuk Target') {
                $sheet->getStyle("H{$row}")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => '065F46'], // Dark Green
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'D1FAE5'], // Light Green background
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
            } elseif ($targetVal === 'Tidak Masuk Target') {
                $sheet->getStyle("H{$row}")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => '991B1B'], // Dark Red
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FEE2E2'], // Light Red background
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
            } else {
                $sheet->getStyle("H{$row}")->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
            }

            // Hyperlink lampiran
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

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,   // No
            'B' => 18,  // Tanggal Pengajuan
            'C' => 20,  // Kode
            'D' => 25,  // Nama
            'E' => 25,  // Nama Kios
            'F' => 35,  // Alamat
            'G' => 16,  // Jenis Pengajuan
            'H' => 20,  // Status Target
            'I' => 25,  // Plafon Aktif
            'J' => 18,  // Plafon Baru
            'K' => 20,  // Jumlah Value Faktur
            'L' => 20,  // Sales
            'M' => 30,  // Komitmen Pembayaran
            'N' => 16,  // Jenis Pembayaran
            'O' => 16,  // Piutang
            'P' => 16,  // Jml Over
            'Q' => 16,  // Jml OD 30
            'R' => 16,  // Jml OD 60
            'S' => 16,  // Jml OD 90
            'T' => 18,  // Lampiran 1
            'U' => 18,  // Lampiran 2
            'V' => 18,  // Lampiran 3
            // Level 1
            'W' => 20,  // L1 Nama
            'X' => 15,  // L1 Status
            'Y' => 18,  // L1 Tanggal
            'Z' => 40,  // L1 Catatan
            // Level 2
            'AA' => 20, // L2 Nama
            'AB' => 15, // L2 Status
            'AC' => 18, // L2 Tanggal
            'AD' => 40, // L2 Catatan
            // Level 3
            'AE' => 20, // L3 Nama
            'AF' => 15, // L3 Status
            'AG' => 18, // L3 Tanggal
            'AH' => 40, // L3 Catatan
            // Level 4
            'AI' => 20, // L4 Nama
            'AJ' => 15, // L4 Status
            'AK' => 18, // L4 Tanggal
            'AL' => 40, // L4 Catatan
            // Level 5
            'AM' => 20, // L5 Nama
            'AN' => 15, // L5 Status
            'AO' => 18, // L5 Tanggal
            'AP' => 40, // L5 Catatan
            // Level 6
            'AQ' => 20, // L6 Nama
            'AR' => 15, // L6 Status
            'AS' => 18, // L6 Tanggal
            'AT' => 40, // L6 Catatan
            'AU' => 22, // Status Akhir
        ];
    }
}
