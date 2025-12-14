<?php

namespace App\Models;

use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode',
        'customer_id',
        'nama',
        'nama_kios',
        'alamat',
        'plafon',
        'plafon_type',
        'plafon_direction',
        'previous_submission_id',
        'jumlah_buka_faktur',
        'komitmen_pembayaran',
        'keterangan',
        'lampiran_path',
        'payment_type',
        'payment_data',
        'sales_id',
        'status',
        'current_level',
        'rejection_note',
    ];

    protected $casts = [
        'payment_data' => 'array',
        'current_level' => 'integer',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
    ];

    /**
     * Boot method untuk handle delete event
     */
    protected static function boot()
    {
        parent::boot();

        // Event ketika submission akan dihapus
        static::deleting(function ($submission) {
            // Hapus file lampiran jika ada
            if ($submission->lampiran_path) {
                Storage::disk('public')->delete($submission->lampiran_path);
            }
        });
    }

    /* ==========================
     |         RELASI
     ========================== */

    // Relasi Customer BARU
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    // Relasi Submission sebelumnya
    public function previousSubmission()
    {
        return $this->belongsTo(Submission::class, 'previous_submission_id');
    }

    // Relasi ke Sales (User)
    public function sales()
    {
        return $this->belongsTo(User::class, 'sales_id');
    }

    // Relasi Approval
    public function approvals()
    {
        return $this->hasMany(Approval::class);
    }

    /* ==========================
     |      ACCESSOR BADGE
     ========================== */

    public function getPlafonTypeBadgeAttribute()
    {
        return $this->plafon_type === 'open'
            ? '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Open Plafon</span>'
            : '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">Rubah Plafon</span>';
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending'               => '<span class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Menunggu Approval 1</span>',
            'approved_1'            => '<span class="px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Menunggu Approval 2</span>',
            'approved_2'            => '<span class="px-3 py-1 text-xs font-semibold rounded-full bg-indigo-100 text-indigo-800">Menunggu Approval 3</span>',
            'approved_3'            => '<span class="px-3 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">Menunggu Approval 4</span>',
            'approver_4'            => '<span class="px-3 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">Menunggu Approval 4</span>',
            'approver_5'            => '<span class="px-3 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">Menunggu Approval 5</span>',
            'approver_6'            => '<span class="px-3 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">Menunggu Approval 6</span>',
            'pending_viewer'        => '<span class="px-3 py-1 text-xs font-semibold rounded-full bg-cyan-100 text-cyan-800">Proses Input</span>',
            'done'                  => '<span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Selesai</span>',
            'rejected'              => '<span class="px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Ditolak</span>',
        ];

        return $badges[$this->status] ?? '<span class="px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">' . ucfirst($this->status) . '</span>';
    }

    public function getPlafonDirectionBadgeAttribute()
    {
        if (!$this->plafon_direction) {
            return '';
        }

        return $this->plafon_direction === 'naik'
            ? '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                    <svg class="w-3 h-3 inline-block -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                    </svg>
                    Naik
               </span>'
            : '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                    <svg class="w-3 h-3 inline-block -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                    </svg>
                    Turun
               </span>';
    }
}
