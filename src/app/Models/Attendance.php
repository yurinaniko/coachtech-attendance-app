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
            ->where('status', 'pending')
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

    public function getWorkTimeHhmmAttribute(): string
    {
        $sec = $this->work_seconds;
        $h = intdiv($sec, 3600);
        $m = intdiv($sec % 3600, 60);
        return sprintf('%02d:%02d', $h, $m);
    }

    public function getBreakTimeHhmmAttribute(): string
    {
        $sec = $this->break_seconds;
        $h = intdiv($sec, 3600);
        $m = intdiv($sec % 3600, 60);
        return sprintf('%02d:%02d', $h, $m);
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
}