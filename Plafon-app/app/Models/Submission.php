<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    protected $fillable = [
        'kode', 'nama', 'nama_kios', 'alamat', 'plafon', 
        'jumlah_buka_faktur', 'komitmen_pembayaran', 'sales_id',
        'status', 'current_level', 'revision_note', 'rejection_note'
    ];

    public function sales()
    {
        return $this->belongsTo(User::class, 'sales_id');
    }

    public function approvals()
    {
        return $this->hasMany(Approval::class);
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => '<span class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Menunggu Approval 1</span>',
            'approved_1' => '<span class="px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Menunggu Approval 2</span>',
            'approved_2' => '<span class="px-3 py-1 text-xs font-semibold rounded-full bg-indigo-100 text-indigo-800">Menunggu Approval 3</span>',
            'approved_3' => '<span class="px-3 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">Proses Input</span>',
            'done' => '<span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Selesai</span>',
            'rejected' => '<span class="px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Ditolak</span>',
            'revision' => '<span class="px-3 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">Perlu Revisi</span>',
        ];

        return $badges[$this->status] ?? '';
    }
}
