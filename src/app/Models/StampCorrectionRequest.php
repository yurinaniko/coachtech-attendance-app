<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\StampCorrectionBreak;

class StampCorrectionRequest extends Model
{
    use HasFactory;
    public const STATUS_PENDING  = 'pending';
    public const STATUS_APPROVED = 'approved';
    const TYPE_USER  = 'user';
    const TYPE_ADMIN = 'admin';
    protected $fillable = [
        'attendance_id',
        'user_id',
        'requested_clock_in_at',
        'requested_clock_out_at',
        'requested_note',
        'status',
        'type',
    ];

    protected $casts = [
        'requested_clock_in_at'  => 'datetime',
        'requested_clock_out_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function getStatusLabelAttribute()
    {
        return match ($this->status) {
            'pending' => '承認待ち',
            'approved' => '承認済み',
        };
    }

    public function stampCorrectionBreaks()
    {
        return $this->hasMany(StampCorrectionBreak::class);
    }
}
