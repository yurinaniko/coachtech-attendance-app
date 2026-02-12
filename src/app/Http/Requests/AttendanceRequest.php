<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'clock_in_at'  => ['required', 'date_format:H:i'],
            'clock_out_at' => ['required', 'date_format:H:i', 'after:clock_in_at'],

            'break_start_at' => ['nullable', 'date_format:H:i'],
            'break_end_at'   => ['nullable', 'date_format:H:i', 'after:break_start_at'],

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

           // 休憩どちらか片方だけ
            if (($breakStart && !$breakEnd) || (!$breakStart && $breakEnd)) {
                $validator->errors()->add(
                    'break_start_at',
                    '休憩時間が不適切な値です'
                );
            return;
            }

            if ($breakStart && $breakEnd) {
                // 休憩開始 < 休憩終了
            if ($breakStart >= $breakEnd) {
                $validator->errors()->add(
                    'break_start_at',
                    '休憩時間が不適切な値です'
                );
            }

            // 出勤 <= 休憩開始
            if ($clockIn && $breakStart < $clockIn) {
                $validator->errors()->add(
                    'break_start_at',
                    '休憩時間が不適切な値です'
                );
            }

            // 休憩終了 <= 退勤
            if ($clockOut && $breakEnd > $clockOut) {
                $validator->errors()->add(
                    'break_end_at',
                    '休憩時間もしくは退勤時間が不適切な値です'
                );
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
            'clock_out_at.after' => '出勤時間もしくは退勤時間が不適切な値です',

            'break_start_at.date_format' => '休憩時間が不適切な値です',
            'break_end_at.date_format'   => '休憩時間が不適切な値です',
            'break_end_at.after' => '休憩時間が不適切な値です',

            'note.required' => '備考を記入してください',
            'note.max' => '備考は255文字以内で入力してください',
        ];
    }
}