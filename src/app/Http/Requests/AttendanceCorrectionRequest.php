<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceCorrectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'requested_clock_in_at' => ['required', 'date_format:H:i'],
            'requested_clock_out_at' => ['required', 'date_format:H:i', 'after:requested_clock_in_at'],
            'break_start_at.*' => [
                'nullable',
                'date_format:H:i',
                'after_or_equal:requested_clock_in_at',
                'before_or_equal:requested_clock_out_at',
            ],
            'break_end_at.*' => [
                'nullable',
                'date_format:H:i',
                'before_or_equal:requested_clock_out_at', ],

            'reason' => ['required', 'string']
        ];
    }

    public function messages(): array
    {
        return [
            'requested_clock_in_at.required' => '出勤時刻を入力してください',
            'requested_clock_in_at.date_format' => '出勤時間が不適切な値です',
            'requested_clock_out_at.required' => '退勤時刻を入力してください',
            'requested_clock_out_at.date_format' => '退勤時間が不適切な値です',
            'requested_clock_out_at.after' => '出勤時間もしくは退勤時間が不適切な値です',
            'break_start_at.*.date_format' => '休憩時間が不適切な値です',
            'break_end_at.*.date_format' => '休憩時間が不適切な値です',
            'break_start_at.*.after_or_equal' => '休憩時間が不適切な値です',
            'break_start_at.*.before_or_equal' => '休憩時間が不適切な値です',
            'break_end_at.*.before_or_equal' => '休憩時間もしくは退勤時間が不適切な値です',
            'reason.required' => '備考を記入してください',
            'reason.string' => '備考を記入してください',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $starts = $this->input('break_start_at', []);
            $ends = $this->input('break_end_at', []);

            $count = max(count($starts), count($ends));

            for ($i = 0; $i < $count; $i++) {
                $start = $starts[$i] ?? null;
                $end = $ends[$i] ?? null;

                if (!empty($start) && empty($end)) {
                    $validator->errors()->add(
                        "break_end_at.$i",
                        '休憩時間が不適切な値です'
                    );
                }

                if (empty($start) && !empty($end)) {
                    $validator->errors()->add(
                        "break_start_at.$i",
                        '休憩時間が不適切な値です'
                    );
                }

                if (!empty($start) && !empty($end) && $end <= $start) {
                    $validator->errors()->add(
                        "break_end_at.$i",
                        '休憩時間が不適切な値です'
                    );
                }
            }
        });
    }

}