<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;
use App\Models\Attendance;

class StoreStampCorrectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'clock_in_at'    => ['required', 'date_format:H:i'],
            'clock_out_at'   => ['required', 'date_format:H:i'],

            'breaks' => ['nullable', 'array'],
            'breaks.*.break_start_at' => ['nullable', 'date_format:H:i'],
            'breaks.*.break_end_at'   => ['nullable', 'date_format:H:i'],
            'note' => ['required', 'string', 'max:255'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $attendance = \App\Models\Attendance::find($this->attendance_id);
            if ($attendance && \Carbon\Carbon::parse($attendance->work_date)->isFuture()) {
                $validator->errors()->add(
                    'attendance_id',
                    '未来日の勤怠は修正申請できません。'
                );
                return;
            }

            $clockIn  = $this->input('clock_in_at');
            $clockOut = $this->input('clock_out_at');
            $clockInTime  = $clockIn  ? Carbon::createFromFormat('H:i', $clockIn) : null;
            $clockOutTime = $clockOut ? Carbon::createFromFormat('H:i', $clockOut) : null;

            if ($clockInTime && $clockOutTime && $clockInTime >= $clockOutTime) {
                $validator->errors()->add(
                    'clock_in_at',
                    '出勤時間が不適切な値です'
                );
                return;
            }

            foreach ($this->input('breaks', []) as $index => $break) {
                $breakStart = $break['break_start_at'] ?? null;
                $breakEnd   = $break['break_end_at'] ?? null;
                $breakStartTime = $breakStart ? Carbon::createFromFormat('H:i', $breakStart) : null;
                $breakEndTime   = $breakEnd   ? Carbon::createFromFormat('H:i', $breakEnd) : null;
                $breakError = '休憩時間が不適切な値です';
                $breakOutError = '休憩時間もしくは退勤時間が不適切な値です';

                if (($breakStartTime && !$breakEndTime) || (!$breakStartTime && $breakEndTime)) {

                    if ($breakStartTime && !$breakEndTime) {
                        $validator->errors()->add(
                            "breaks.$index.break_end_at",
                            '終了時間を入力してください'
                        );
                    }

                    if (!$breakStartTime && $breakEndTime) {
                        $validator->errors()->add(
                            "breaks.$index.break_start_at",
                            '開始時間を入力してください'
                        );
                    }
                    continue;
                }

                if ($breakStartTime && $breakEndTime) {
                    if (
                        $breakStartTime >= $breakEndTime ||
                        ($clockInTime && $breakStartTime < $clockInTime) ||
                        ($clockOutTime && $breakStartTime > $clockOutTime)
                    ) {
                        $validator->errors()->add(
                            "breaks.$index.break_start_at",
                            $breakError
                        );
                    }
                    // 休憩終了 > 退勤
                    if ($clockOutTime && $breakEndTime > $clockOutTime) {
                        $validator->errors()->add(
                            "breaks.$index.break_end_at",
                            $breakOutError
                        );
                    }
                }
            }

            $breakRanges = [];
            foreach ($this->input('breaks', []) as $index => $break) {
                $start = $break['break_start_at'] ?? null;
                $end   = $break['break_end_at'] ?? null;
                if (!$start || !$end) {
                    continue;
                }
                $breakRanges[] = [
                    'index' => $index,
                    'start' => Carbon::createFromFormat('H:i', $start),
                    'end'   => Carbon::createFromFormat('H:i', $end),
                ];
            }

            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            for ($i = 0; $i < count($breakRanges); $i++) {
                for ($j = $i + 1; $j < count($breakRanges); $j++) {
                    $break1 = $breakRanges[$i];
                    $break2 = $breakRanges[$j];
                    if (
                        $break1['start'] < $break2['end'] &&
                        $break1['end'] > $break2['start']
                    ) {
                        $validator->errors()->add(
                            "breaks.".$break2['index'].".break_start_at",
                            '他の休憩時間と重複しています'
                        );
                        return;
                    }
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'clock_in_at.required'  => '出勤時間を入力してください',
            'clock_in_at.date_format' => '出勤時間は正しい形式で入力してください',
            'clock_out_at.required' => '退勤時間を入力してください',
            'clock_out_at.date_format' => '退勤時間は正しい形式で入力してください',
            'breaks.*.break_start_at.date_format' => '休憩時間が不適切な値です',
            'breaks.*.break_end_at.date_format'   => '休憩時間が不適切な値です',
            'note.required' => '備考を記入してください',
            'note.max' => '備考は255文字以内で入力してください',
        ];
    }
}