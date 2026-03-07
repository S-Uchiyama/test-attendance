<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class AdminUpdateAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'clock_in' => ['nullable', 'date_format:H:i'],
            'clock_out' => ['nullable', 'date_format:H:i', 'after_or_equal:clock_in'],
            'breaks' => ['array'],
            'breaks.*.start' => ['nullable', 'date_format:H:i'],
            'breaks.*.end' => ['nullable', 'date_format:H:i'],
            'note' => ['required', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'clock_out.after_or_equal' => '出勤時間もしくは退勤時間が不適切な値です',
            'note.required' => '備考を記入してください',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $clockInTime = $this->toTime($this->input('clock_in'));
            $clockOutTime = $this->toTime($this->input('clock_out'));

            foreach ($this->input('breaks', []) as $index => $break) {
                $start = $break['start'] ?? null;
                $end = $break['end'] ?? null;
                $startTime = $this->toTime($start);
                $endTime = $this->toTime($end);

                if (($start && !$end) || (!$start && $end)) {
                    $validator->errors()->add("breaks.$index.start", '休憩時間が不適切な値です');
                }

                if ($startTime && $endTime && $endTime->lt($startTime)) {
                    $validator->errors()->add("breaks.$index.end", '休憩時間が不適切な値です');
                }

                if ($startTime && $clockInTime && $startTime->lt($clockInTime)) {
                    $validator->errors()->add("breaks.$index.start", '休憩時間が不適切な値です');
                }

                if ($endTime && $clockInTime && $endTime->lt($clockInTime)) {
                    $validator->errors()->add("breaks.$index.end", '休憩時間が不適切な値です');
                }

                if ($startTime && $clockOutTime && $startTime->gt($clockOutTime)) {
                    $validator->errors()->add("breaks.$index.start", '休憩時間が不適切な値です');
                }

                if ($endTime && $clockOutTime && $endTime->gt($clockOutTime)) {
                    $validator->errors()->add("breaks.$index.end", '休憩時間もしくは退勤時間が不適切な値です');
                }
            }
        });
    }

    private function toTime(?string $value): ?Carbon
    {
        if (!$value) {
            return null;
        }

        try {
            return Carbon::createFromFormat('H:i', $value);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
