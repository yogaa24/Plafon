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
            'Komitmen Pembayaran Awal',
            'Riwayat Komitmen Ditolak',
            'Komitmen Pembayaran Baru',
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

        // Parse Riwayat & Komitmen Pembayaran
        $rawHistoryList = [];
        if ($submission->komitmen_pembayaran_history) {
            $rawHistoryList = is_array($submission->komitmen_pembayaran_history) 
                ? $submission->komitmen_pembayaran_history 
                : (json_decode($submission->komitmen_pembayaran_history, true) ?: []);
        }

        // Normalisasi riwayat: gabungkan entri perbaikan duplikat terpisah dari TC versi lama
        $historyList = [];
        if (is_array($rawHistoryList)) {
            foreach ($rawHistoryList as $item) {
                $actionType = $item['action_type'] ?? '';
                $alasan = $item['alasan'] ?? '';
                $rejectedBy = $item['rejected_by_name'] ?? '';

                $isSeparateTcRevision = str_contains($alasan, 'diperbaiki oleh Collection pasca penolakan')
                    && str_contains($rejectedBy, 'Collection')
                    && empty($item['revised_at']);

                if ($isSeparateTcRevision && !empty($historyList)) {
                    $lastIdx = count($historyList) - 1;
                    if (($historyList[$lastIdx]['action_type'] ?? '') === 'returned_to_collection' || empty($historyList[$lastIdx]['revised_at'])) {
                        $historyList[$lastIdx]['komitmen_baru'] = $item['komitmen_baru'] ?? ($historyList[$lastIdx]['komitmen_baru'] ?? null);
                        $historyList[$lastIdx]['revised_by_name'] = $rejectedBy;
                        $historyList[$lastIdx]['revised_at'] = $item['created_at'] ?? now()->toDateTimeString();
                        $historyList[$lastIdx]['action_type'] = 'revised_by_collection';
                        continue;
                    }
                }

                $historyList[] = $item;
            }
        }

        $hasHistory = !empty($historyList);

        if (!$hasHistory) {
            $komitmenAwal = $submission->komitmen_pembayaran ?: '-';
            $riwayatKomitmenDitolak = '-';
            $komitmenBaru = '-';
        } else {
            // Komitmen awal sebelum adanya penolakan
            $komitmenAwal = $historyList[0]['komitmen_sebelumnya'] ?? ($submission->komitmen_pembayaran ?: '-');

            // Format Riwayat Komitmen Ditolak
            $rejectionEntries = [];
            $totalRejections = count($historyList);

            foreach ($historyList as $idx => $hist) {
                $num = $idx + 1;
                $dateStr = !empty($hist['created_at']) 
                    ? \Carbon\Carbon::parse($hist['created_at'])->format('d-m-Y H:i') 
                    : '';
                
                $headerTitle = $totalRejections > 1 ? "[Penolakan #{$num}]" : "Penolakan";
                if ($dateStr) {
                    $headerTitle .= " ({$dateStr})";
                }

                $entryLines = [];
                $entryLines[] = $headerTitle . ':';
                $entryLines[] = '• Komitmen Ditolak: "' . ($hist['komitmen_sebelumnya'] ?? '-') . '"';
                $entryLines[] = '• Alasan: ' . ($hist['alasan'] ?? '-');
                if (!empty($hist['rejected_by_name'])) {
                    $entryLines[] = '• Ditolak oleh: ' . $hist['rejected_by_name'];
                }

                $rejectionEntries[] = implode("\n", $entryLines);
            }

            $riwayatKomitmenDitolak = implode("\n\n", $rejectionEntries);

            // Format Komitmen Baru
            $latestHist = end($historyList);
            $latestAction = $latestHist['action_type'] ?? '';
            $isReturned = $latestAction === 'returned_to_collection';

            if ($isReturned && empty($latestHist['revised_at']) && (empty($latestHist['komitmen_baru']) || $latestHist['komitmen_baru'] === '(Menunggu revisi komitmen dari Collection)')) {
                $komitmenBaru = '⏳ Menunggu perbaikan komitmen oleh Collection';
            } else {
                $newVal = !empty($latestHist['komitmen_baru']) && $latestHist['komitmen_baru'] !== '(Menunggu revisi komitmen dari Collection)'
                    ? $latestHist['komitmen_baru']
                    : ($submission->komitmen_pembayaran ?: '-');

                $komitmenBaru = $newVal;

                if (!empty($latestHist['revised_by_name'])) {
                    $revisedDate = !empty($latestHist['revised_at']) ? ' - ' . \Carbon\Carbon::parse($latestHist['revised_at'])->format('d-m-Y H:i') : '';
                    $komitmenBaru .= "\n(Diperbaiki oleh: " . $latestHist['revised_by_name'] . $revisedDate . ')';
                } elseif (!empty($latestHist['rejected_by_name']) && $latestAction === 'updated_by_tc') {
                    $komitmenBaru .= "\n(Ditetapkan oleh: " . $latestHist['rejected_by_name'] . ')';
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
            $komitmenAwal,
            $riwayatKomitmenDitolak,
            $komitmenBaru,
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

        // Buat hyperlink untuk kolom lampiran (V, W, X = kolom 22, 23, 24)
        $lampiranCols = ['V', 'W', 'X'];
        $highestRow = $sheet->getHighestRow();

        // Wrap text & vertical alignment untuk kolom komitmen
        $sheet->getStyle("M2:O{$highestRow}")->getAlignment()->setWrapText(true);
        $sheet->getStyle("N2:O{$highestRow}")->getAlignment()->setVertical(Alignment::VERTICAL_TOP);

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
            'M' => 30,  // Komitmen Pembayaran Awal
            'N' => 45,  // Riwayat Komitmen Ditolak
            'O' => 35,  // Komitmen Pembayaran Baru
            'P' => 16,  // Jenis Pembayaran
            'Q' => 16,  // Piutang
            'R' => 16,  // Jml Over
            'S' => 16,  // Jml OD 30
            'T' => 16,  // Jml OD 60
            'U' => 16,  // Jml OD 90
            'V' => 18,  // Lampiran 1
            'W' => 18,  // Lampiran 2
            'X' => 18,  // Lampiran 3
            // Level 1
            'Y' => 20,  // L1 Nama
            'Z' => 15,  // L1 Status
            'AA' => 18, // L1 Tanggal
            'AB' => 40, // L1 Catatan
            // Level 2
            'AC' => 20, // L2 Nama
            'AD' => 15, // L2 Status
            'AE' => 18, // L2 Tanggal
            'AF' => 40, // L2 Catatan
            // Level 3
            'AG' => 20, // L3 Nama
            'AH' => 15, // L3 Status
            'AI' => 18, // L3 Tanggal
            'AJ' => 40, // L3 Catatan
            // Level 4
            'AK' => 20, // L4 Nama
            'AL' => 15, // L4 Status
            'AM' => 18, // L4 Tanggal
            'AN' => 40, // L4 Catatan
            // Level 5
            'AO' => 20, // L5 Nama
            'AP' => 15, // L5 Status
            'AQ' => 18, // L5 Tanggal
            'AR' => 40, // L5 Catatan
            // Level 6
            'AS' => 20, // L6 Nama
            'AT' => 15, // L6 Status
            'AU' => 18, // L6 Tanggal
            'AV' => 40, // L6 Catatan
            'AW' => 22, // Status Akhir
        ];
    }
}
