<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\AttendanceBreak;
use App\Models\StampCorrectionRequest;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'work_date',
        'clock_in_at',
        'clock_out_at',
        'note',
        'status',
    ];

    protected $casts = [
        'work_date'    => 'date',
        'clock_in_at'  => 'datetime',
        'clock_out_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breaks()
    {
        return $this->hasMany(AttendanceBreak::class)
            ->orderBy('break_start_at');
    }

    public function stampCorrectionRequests()
    {
        return $this->hasMany(StampCorrectionRequest::class);
    }

    public function hasPendingRequest(): bool
    {
        return $this->stampCorrectionRequests()
            ->where('status', StampCorrectionRequest::STATUS_PENDING)
            ->exists();
    }

    public function getBreakAt(int $index)
    {
        return $this->breaks->get($index);
    }

    public function getBreakSecondsAttribute(): int
    {
        return $this->breaks->sum(function ($b) {
            if (!$b->break_start_at || !$b->break_end_at) return 0;
            return Carbon::parse($b->break_end_at)->diffInSeconds(Carbon::parse($b->break_start_at));
        });
    }

    public function getWorkSecondsAttribute(): int
    {
        if (!$this->clock_in_at || !$this->clock_out_at) return 0;

        $work = Carbon::parse($this->clock_out_at)->diffInSeconds(Carbon::parse($this->clock_in_at));
        $work -= $this->break_seconds;

        return max(0, $work);
    }

    public function getWorkTimeHhmmAttribute(): ?string
    {
        if (!$this->clock_in_at || !$this->clock_out_at) {
            return null;
        }

        $workMinutes = $this->clock_out_at->diffInMinutes($this->clock_in_at);

        $breakMinutes = $this->breaks->reduce(function ($carry, $break) {
            if ($break->break_start_at && $break->break_end_at) {
                return $carry + $break->break_end_at->diffInMinutes($break->break_start_at);
            }
                return $carry;
        }, 0);

        $net = $workMinutes - $breakMinutes;

        return sprintf('%02d:%02d', intdiv($net, 60), $net % 60);
    }

    public function getBreakTimeHhmmAttribute(): ?string
    {
        $totalMinutes = $this->breaks->reduce(function ($carry, $break) {
            if ($break->break_start_at && $break->break_end_at) {
                return $carry + $break->break_end_at->diffInMinutes($break->break_start_at);
            }
            return $carry;
        }, 0);

        if ($totalMinutes === 0) {
            return null;
        }

        return sprintf('%02d:%02d', intdiv($totalMinutes, 60), $totalMinutes % 60);
    }

    public function getClockInTimeAttribute(): ?string
    {
        return $this->clock_in_at
            ? $this->clock_in_at->format('H:i')
            : null;
    }

    public function getClockOutTimeAttribute(): ?string
    {
        return $this->clock_out_at
            ? $this->clock_out_at->format('H:i')
            : null;
    }

    public function latestStampRequest(): ?StampCorrectionRequest
    {
        return $this->stampCorrectionRequests()
            ->latest()
            ->first();
    }
}