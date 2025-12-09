<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class Level3DoneExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $submissions;
    protected $level3Approvers;
    
    public function __construct($submissions, $level3Approvers)
    {
        $this->submissions = $submissions;
        $this->level3Approvers = $level3Approvers;
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
        'Nama',
        'Nama Kios',
        'Alamat',
        'Jenis Pengajuan',
        'Plafon Aktif',      // ← GANTI dari "Plafon Sebelumnya"
        'Plafon Baru',       // ← Hanya untuk Rubah Plafon
        'Jumlah Buka Faktur',
        'Sales',
        'Komitmen Pembayaran',
        'Jenis Pembayaran (OD/Over)',
        'Piutang',
        'Jml Over',
        'Jml OD 30',
        'Jml OD 60',
        'Jml OD 90',
    ];
    
    // Tambahkan kolom approver
    foreach ($this->level3Approvers as $approver) {
        $header[] = $approver->approver_name . ' - Status';
        $header[] = $approver->approver_name . ' - Tanggal';
        $header[] = $approver->approver_name . ' - Catatan';
    }
    
    $header[] = 'Status Akhir';
    
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
        // RUBAH PLAFON: Ada plafon aktif (lama) dan plafon baru
        if ($submission->customer) {
            $plafonAktif = $submission->customer->plafon_aktif;
        }
        $plafonBaru = $submission->plafon;
        
    } elseif ($submission->plafon_type === 'open') {
        // OPEN PLAFON: Hanya plafon aktif (yang diajukan), tidak ada plafon baru
        $plafonAktif = $submission->plafon;
        $plafonBaru = '-'; // Kosongkan kolom plafon baru
    }
    
    // Jumlah Buka Faktur (hanya untuk open plafon)
    $jumlahBukaFaktur = 0;
    if ($submission->plafon_type === 'open' && $submission->jumlah_buka_faktur) {
        $jumlahBukaFaktur = $submission->jumlah_buka_faktur;
    }
    
    // Parse payment data (OD/Over)
    $paymentData = null;
    if ($submission->payment_data) {
        $paymentData = is_array($submission->payment_data) 
            ? $submission->payment_data 
            : json_decode($submission->payment_data, true);
    }
    
    // Jenis Pembayaran
    $jenisPembayaran = $submission->payment_type ? strtoupper($submission->payment_type) : '-';
    
    // Data OD/Over (ANGKA MENTAH, tanpa number_format)
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
        $plafonAktif,  // ← Plafon Aktif (untuk Open) atau Plafon Lama (untuk Rubah)
        $plafonBaru,   // ← Plafon Baru (hanya untuk Rubah), kosong untuk Open
        $jumlahBukaFaktur,
        $submission->sales->name ?? '-',
        $submission->komitmen_pembayaran ?? '-',
        // Data OD/Over
        $jenisPembayaran,
        $piutang,
        $jmlOver,
        $jmlOd30,
        $jmlOd60,
        $jmlOd90,
    ];
    
    // Data approval per approver
    foreach ($this->level3Approvers as $approver) {
        $approval = $submission->approvals->where('approver_id', $approver->id)->first();
        
        if ($approval) {
            $row[] = $approval->status === 'approved' ? 'Disetujui' : 'Ditolak';
            $row[] = $approval->created_at->format('d-m-Y H:i');
            $row[] = $approval->note ?? '-';
        } else {
            $row[] = 'Belum Vote';
            $row[] = '-';
            $row[] = '-';
        }
    }
    
    $row[] = $submission->status === 'done' ? 'Selesai' : 'Menunggu Input';
    
    return $row;
}

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1 => ['font' => ['bold' => true]],
        ];
    }
}