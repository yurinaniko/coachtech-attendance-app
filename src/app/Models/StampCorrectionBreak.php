<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\StampCorrectionRequest;

class StampCorrectionBreak extends Model
{
    use HasFactory;
    protected $fillable = [
        'stamp_correction_request_id',
        'break_start_at',
        'break_end_at',
        'attendance_break_id',
    ];
    public function stampCorrectionRequest()
    {
        return $this->belongsTo(StampCorrectionRequest::class);
    }
}
