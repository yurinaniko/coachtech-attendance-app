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

    public function totalBreakMinutes(): int
    {
        return $this->breaks
            ->whereNotNull('break_end_at')
            ->sum(function ($break) {
                return Carbon::parse($break->break_end_at)
                ->diffInMinutes(Carbon::parse($break->break_start_at));
            });
    }

    public function totalWorkingMinutes(): int
    {
        if (!$this->clock_in_at || !$this->clock_out_at) {
        return 0;
        }

        $workMinutes =
            Carbon::parse($this->clock_out_at)
                ->diffInMinutes(Carbon::parse($this->clock_in_at));

        return $workMinutes - $this->totalBreakMinutes();
    }
}