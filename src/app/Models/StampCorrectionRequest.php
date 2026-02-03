<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\StampCorrectionBreak;

class StampCorrectionRequest extends Model
{
    use HasFactory;
    protected $fillable = [
        'attendance_id',
        'user_id',
        'requested_clock_in_at',
        'requested_clock_out_at',
        'requested_note',
        'status',
    ];

    public function getStatusLabelAttribute()
    {
        return match ($this->status) {
            'pending' => '承認待ち',
            'approved' => '承認済み',
            default => '',
        };
    }

    public function stampCorrectionBreaks()
    {
        return $this->hasMany(StampCorrectionBreak::class);
    }
}
