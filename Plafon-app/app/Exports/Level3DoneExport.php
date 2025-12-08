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
            'Plafon Sebelumnya',
            'Plafon Baru',
            'Sales',
            'Komitmen Pembayaran'
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
        
        $plafonSebelumnya = '';
        if ($submission->plafon_type === 'rubah' && $submission->customer) {
            $plafonSebelumnya = number_format($submission->customer->plafon_aktif, 0, ',', '.');
        }
        
        $row = [
            $index,
            $submission->created_at->format('d-m-Y H:i'),
            $submission->kode,
            $submission->nama,
            $submission->nama_kios,
            $submission->alamat,
            $submission->plafon_type === 'open' ? 'Open Plafon' : 'Rubah Plafon',
            $plafonSebelumnya,
            number_format($submission->plafon, 0, ',', '.'),
            $submission->sales->name ?? '-',
            $submission->komitmen_pembayaran ?? '-'
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