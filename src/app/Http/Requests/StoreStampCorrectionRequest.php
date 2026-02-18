<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStampCorrectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'attendance_id' => ['required', 'integer', 'exists:attendances,id'],
            'clock_in_at'  => ['required', 'date_format:H:i'],
            'clock_out_at' => ['required', 'date_format:H:i', 'after:clock_in_at'],
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

            foreach ($this->input('breaks', []) as $index => $break) {

                $breakStart = $break['break_start_at'] ?? null;
                $breakEnd   = $break['break_end_at'] ?? null;

                if (($breakStart && !$breakEnd) || (!$breakStart && $breakEnd)) {

                    if ($breakStart && !$breakEnd) {
                        $validator->errors()->add(
                            "breaks.$index.break_end_at",
                            '終了時間を入力してください'
                        );
                    }

                    if (!$breakStart && $breakEnd) {
                        $validator->errors()->add(
                            "breaks.$index.break_start_at",
                            '開始時間を入力してください'
                        );
                    }

                    continue;
                }

                if ($breakStart && $breakEnd) {

                    if ($breakStart >= $breakEnd) {
                        $validator->errors()->add(
                            "breaks.$index.break_start_at",
                            '休憩時間が不適切な値です'
                        );
                    }

                    if ($clockIn && $breakStart < $clockIn) {
                        $validator->errors()->add(
                            "breaks.$index.break_start_at",
                            '休憩時間が不適切な値です'
                        );
                    }

                    if ($clockOut && $breakEnd > $clockOut) {
                        $validator->errors()->add(
                            "breaks.$index.break_end_at",
                            '休憩時間もしくは退勤時間が不適切な値です'
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
            'clock_out_at.after' => '出勤時間もしくは退勤時間が不適切な値です',

            'break_start_at.date_format' => '休憩時間が不適切な値です',
            'break_end_at.date_format'   => '休憩時間が不適切な値です',
            'break_end_at.after' => '休憩時間が不適切な値です',

            'note.required' => '備考を記入してください',
            'note.max' => '備考は255文字以内で入力してください',
        ];
    }
}
