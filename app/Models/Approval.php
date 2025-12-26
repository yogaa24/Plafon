<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Approval extends Model
{
    protected $fillable = [
        'submission_id', 'approver_id', 'level', 'status', 'note'
    ];

    public function submission()
    {
        return $this->belongsTo(Submission::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}