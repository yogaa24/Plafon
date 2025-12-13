<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable, HasFactory;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_level3_approver',
        'approver_name'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_level3_approver' => 'boolean',
    ];

    // Relationships
    public function submissions()
    {
        return $this->hasMany(Submission::class, 'sales_id');
    }

    public function approvals()
    {
        return $this->hasMany(Approval::class, 'approver_id');
    }

    // Helper methods
    public function isSales()
    {
        return $this->role === 'sales';
    }

    public function isApprover()
    {
        return in_array($this->role, ['approver1', 'approver2', 'approver3', 'approver4', 'approver5', 'approver6']);
    }

    public function isLevel3Approver()
    {
        return $this->role === 'approver3' && $this->is_level3_approver;
    }

    public function getApproverLevel()
    {
        return match($this->role) {
            'approver1' => 1,
            'approver2' => 2,
            'approver3' => 3,
            'approver4' => 4,
            'approver5' => 5,
            'approver6' => 6,
            default => 0
        };
    }

    public function isViewer()
    {
        return $this->role === 'viewer';
    }
}