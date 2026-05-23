<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StampCorrectionRequestBreak extends Model
{
    use HasFactory;

    protected $fillable = [
        'stamp_correction_request_id',
        'requested_break_start_at',
        'requested_break_end_at',
    ];

    public function stampCorrectionRequest()
    {
        return $this->belongsTo(StampCorrectionRequest::class);
    }
}