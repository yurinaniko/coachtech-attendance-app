<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminAttendanceUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'clock_in_at' => ['nullable', 'date_format:H:i'],
            'clock_out_at' => ['nullable', 'date_format:H:i', 'after:clock_in_at'],
            'note' => ['nullable', 'string', 'max:255'],

            'breaks' => ['array'],
            'breaks.*.attendance_break_id' => ['nullable', 'integer'],
            'breaks.*.break_start_at' => ['nullable', 'date_format:H:i'],
            'breaks.*.break_end_at' => ['nullable', 'date_format:H:i', 'after:breaks.*.break_start_at'],
        ];
    }
}
