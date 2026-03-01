<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreAttendanceCorrectionRequest extends FormRequest
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
            'reason' => ['required', 'string', 'max:1000'],
            'breaks' => ['array'],
            'breaks.*.start' => ['nullable', 'date_format:H:i'],
            'breaks.*.end' => ['nullable', 'date_format:H:i', 'after_or_equal:breaks.*.start'],
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required' => '備考を記入してください',
            'clock_out.after_or_equal' => '出勤時間もしくは退勤時間が不適切な値です',
            'breaks.*.end.after_or_equal' => '休憩時間が不適切な値です',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $clockIn = $this->input('clock_in');
            $clockOut = $this->input('clock_out');
            $breaks = $this->input('breaks', []);

            $clockInTime = $this->toTime($clockIn);
            $clockOutTime = $this->toTime($clockOut);

            if ($clockInTime && $clockOutTime && $clockInTime->gt($clockOutTime)) {
                $validator->errors()->add('clock_in', '出勤時間もしくは退勤時間が不適切な値です');
            }

            foreach ($breaks as $index => $break) {
                $breakStartTime = $this->toTime($break['start'] ?? null);
                $breakEndTime = $this->toTime($break['end'] ?? null);

                if ($breakStartTime && $clockInTime && $breakStartTime->lt($clockInTime)) {
                    $validator->errors()->add("breaks.$index.start", '休憩時間が不適切な値です');
                }

                if ($breakStartTime && $clockOutTime && $breakStartTime->gt($clockOutTime)) {
                    $validator->errors()->add("breaks.$index.start", '休憩時間が不適切な値です');
                }

                if ($breakEndTime && $clockInTime && $breakEndTime->lt($clockInTime)) {
                    $validator->errors()->add("breaks.$index.end", '休憩時間が不適切な値です');
                }

                if ($breakEndTime && $clockOutTime && $breakEndTime->gt($clockOutTime)) {
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
