<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class AttendanceUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'clock_in_at'  => ['required', 'date_format:H:i'],
            'clock_out_at' => ['required', 'date_format:H:i'],
            'breaks' => ['nullable', 'array'],
            'breaks.*.break_start_at' => ['nullable', 'date_format:H:i'],
            'breaks.*.break_end_at'   => ['nullable', 'date_format:H:i'],

            'note' => ['required', 'string', 'max:255'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $clockIn  = $this->input('clock_in_at');
            $clockOut = $this->input('clock_out_at');
            $breakStart = $this->input('break_start_at');
            $breakEnd   = $this->input('break_end_at');

            $clockInTime  = $clockIn  ? Carbon::createFromFormat('H:i', $clockIn) : null;
            $clockOutTime = $clockOut ? Carbon::createFromFormat('H:i', $clockOut) : null;
            $breakStartTime = $breakStart ? Carbon::createFromFormat('H:i', $breakStart) : null;
            $breakEndTime   = $breakEnd   ? Carbon::createFromFormat('H:i', $breakEnd) : null;

            if ($clockInTime && $clockOutTime && $clockInTime >= $clockOutTime) {
                $validator->errors()->add(
                    'clock_in_at',
                    '出勤時間もしくは退勤時間が不適切な値です'
                );
            }

            foreach ($this->input('breaks', []) as $index => $break) {

                $breakStart = $break['break_start_at'] ?? null;
                $breakEnd   = $break['break_end_at'] ?? null;

                $breakStartTime = $breakStart ? Carbon::createFromFormat('H:i', $breakStart) : null;
                $breakEndTime   = $breakEnd   ? Carbon::createFromFormat('H:i', $breakEnd) : null;

                if (($breakStartTime && !$breakEndTime) || (!$breakStartTime && $breakEndTime)) {

                    $validator->errors()->add(
                        "breaks.$index.break_start_at",
                        '休憩時間が不適切な値です'
                    );

                    continue;
                }

                if ($breakStartTime && $breakEndTime) {

                    // 休憩開始 < 休憩終了
                    if ($breakStartTime >= $breakEndTime) {
                        $validator->errors()->add(
                            "breaks.$index.break_start_at",
                            '休憩時間が不適切な値です'
                        );
                    }

                    // 休憩開始 < 出勤
                    if ($clockInTime && $breakStartTime < $clockInTime) {
                        $validator->errors()->add(
                            "breaks.$index.break_start_at",
                            '休憩時間が不適切な値です'
                        );
                    }

                    // 休憩終了 > 退勤
                    if ($clockOutTime && $breakEndTime > $clockOutTime) {
                        $validator->errors()->add(
                            "breaks.$index.break_end_at",
                            '休憩時間もしくは退勤時間が不適切な値です'
                        );
                    }

                    if ($clockOutTime && $breakStartTime > $clockOutTime) {
                        $validator->errors()->add(
                            "breaks.$index.break_start_at",
                            '休憩時間が不適切な値です'
                        );
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
            'break_start_at.date_format' => '休憩時間が不適切な値です',
            'break_end_at.date_format'   => '休憩時間が不適切な値です',
            'note.required' => '備考を記入してください',
            'note.max' => '備考は255文字以内で入力してください',
        ];
    }
}
