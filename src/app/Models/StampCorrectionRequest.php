<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StampCorrectionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'user_id',
        'requested_clock_in_at',
        'requested_clock_out_at',
        'requested_note',
        'reason',
        'status',
        'requested_at',
        'processed_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function stampCorrectionRequestBreaks()
    {
        return $this->hasMany(StampCorrectionRequestBreak::class);
    }

}