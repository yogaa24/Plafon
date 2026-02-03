<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'sales_id',
        'nama_sales',
        'kode_customer',
        'nama',
        'nama_kios',
        'alamat',
        'plafon_aktif',
        'piutang', // TAMBAH INI
        'status',
    ];

    protected $casts = [
        'plafon_aktif' => 'decimal:2',
        'piutang' => 'decimal:2', // TAMBAH INI
    ];

    public function sales()
    {
        return $this->belongsTo(User::class, 'sales_id');
    }

    /**
     * Relasi ke submissions
     */
    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }

    /**
     * Get submission terakhir yang sudah done
     */
    public function latestApprovedSubmission()
    {
        return $this->hasOne(Submission::class)
            ->where('status', 'done')
            ->latest();
    }

    /**
     * Get pending submissions (UPDATED - tambah status baru)
     */
    public function pendingSubmissions()
    {
        return $this->hasMany(Submission::class)
            ->whereIn('status', [
                'pending',              // Menunggu Approval 1
                'approved_1',           // Menunggu Approval 2
                'approved_2',           // Menunggu Approval 3
                'approved_3',           // Menunggu Approval 4 (status lama)
                'approver_4',    // Menunggu Approval 4
                'approver_5',    // Menunggu Approval 5
                'approver_6',    // Menunggu Approval 6
                'pending_viewer',       // Proses Input Viewer
                'revision'              // Perlu Revisi
            ]);
    }

    /**
     * Check if customer has pending submission (UPDATED)
     */
    public function hasPendingSubmission()
    {
        return $this->pendingSubmissions()->exists();
    }

    /**
     * Scope untuk filter active customers
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope untuk filter by sales
     */
    public function scopeBySales($query, $salesId)
    {
        return $query->where('sales_id', $salesId);
    }

    /**
     * Scope untuk search
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('kode_customer', 'like', "%{$search}%")
              ->orWhere('nama', 'like', "%{$search}%")
              ->orWhere('nama_kios', 'like', "%{$search}%");
        });
    }
}