<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'sales_id',
        'kode_customer',
        'nama',
        'nama_kios',
        'alamat',
        'plafon_aktif',
        'status',
    ];

    protected $casts = [
        'plafon_aktif' => 'decimal:2',
    ];

    /**
     * Relasi ke sales/user
     */
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
     * Get pending submissions
     */
    public function pendingSubmissions()
    {
        return $this->hasMany(Submission::class)
            ->whereIn('status', ['pending', 'approved_1', 'approved_2', 'approved_3', 'revision']);
    }

    /**
     * Check if customer has pending submission
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