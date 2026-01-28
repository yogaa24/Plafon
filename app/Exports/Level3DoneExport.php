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
        ];
        
        // Tambahkan kolom untuk setiap level (1, 2, 3, 4, 5, 6)
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
        $plafonBaru = '';
        
        if ($submission->plafon_type === 'rubah') {
            if ($submission->customer) {
                $plafonAktif = $submission->plafon_sebelumnya;
            }
            $plafonBaru = $submission->plafon;
        } elseif ($submission->plafon_type === 'open') {
            $plafonAktif = $submission->plafon;
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
        
        $row = [
            $index,
            $submission->created_at->format('d-m-Y H:i'),
            $submission->kode,
            $submission->nama,
            $submission->nama_kios,
            $submission->alamat,
            $submission->plafon_type === 'open' ? 'Open Plafon' : 'Rubah Plafon',
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
        ];
        
        // Data approval per level (1, 2, 3, 4, 5, 6)
        foreach ([1, 2, 3, 4, 5, 6] as $level) {
            $approval = $submission->approvals->where('level', $level)->first();
            
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
            'approved_3'            => 'Menunggu Level 4',
            'pending_approver4'     => 'Menunggu Level 4',
            'pending_approver5'     => 'Menunggu Level 5',
            'pending_approver6'     => 'Menunggu Level 6',
            'pending_viewer'        => 'Proses Input Viewer',
            'done'                  => 'Selesai',
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
            'G' => 15,  // Jenis Pengajuan
            'H' => 25,  // Plafon Aktif
            'I' => 15,  // Plafon Baru
            'J' => 18,  // Jumlah Value Faktur
            'K' => 20,  // Sales
            'L' => 30,  // Komitmen Pembayaran
            'M' => 15,  // Jenis Pembayaran
            'N' => 15,  // Piutang
            'O' => 15,  // Jml Over
            'P' => 15,  // Jml OD 30
            'Q' => 15,  // Jml OD 60
            'R' => 15,  // Jml OD 90
            // Level 1
            'S' => 20,  // L1 Nama
            'T' => 15,  // L1 Status
            'U' => 18,  // L1 Tanggal
            'V' => 40,  // L1 Catatan
            // Level 2
            'W' => 20,  // L2 Nama
            'X' => 15,  // L2 Status
            'Y' => 18,  // L2 Tanggal
            'Z' => 40,  // L2 Catatan
            // Level 3
            'AA' => 20, // L3 Nama
            'AB' => 15, // L3 Status
            'AC' => 18, // L3 Tanggal
            'AD' => 40, // L3 Catatan
            // Level 4
            'AE' => 20, // L4 Nama
            'AF' => 15, // L4 Status
            'AG' => 18, // L4 Tanggal
            'AH' => 40, // L4 Catatan
            // Level 5
            'AI' => 20, // L5 Nama
            'AJ' => 15, // L5 Status
            'AK' => 18, // L5 Tanggal
            'AL' => 40, // L5 Catatan
            // Level 6
            'AM' => 20, // L6 Nama
            'AN' => 15, // L6 Status
            'AO' => 18, // L6 Tanggal
            'AP' => 40, // L6 Catatan
            'AQ' => 20, // Status Akhir
        ];
    }
}
