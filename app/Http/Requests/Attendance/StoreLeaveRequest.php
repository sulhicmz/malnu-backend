<?php

declare(strict_types=1);

namespace App\Http\Requests\Attendance;

use Hyperf\Foundation\Http\FormRequest;

class StoreLeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'staff_id' => 'required|string|exists:staff,id',
            'leave_type_id' => 'required|string|exists:leave_types,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'reason' => 'required|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'staff_id.required' => 'The staff_id field is required.',
            'staff_id.string' => 'The staff_id must be a string.',
            'staff_id.exists' => 'The selected staff member does not exist.',
            'leave_type_id.required' => 'The leave_type_id field is required.',
            'leave_type_id.string' => 'The leave_type_id must be a string.',
            'leave_type_id.exists' => 'The selected leave type does not exist.',
            'start_date.required' => 'The start_date field is required.',
            'start_date.date' => 'The start_date must be a valid date.',
            'start_date.after_or_equal' => 'The start_date must be today or in the future.',
            'end_date.required' => 'The end_date field is required.',
            'end_date.date' => 'The end_date must be a valid date.',
            'end_date.after' => 'The end_date must be after start date.',
            'reason.required' => 'The reason field is required.',
            'reason.max' => 'The reason must not exceed 500 characters.',
        ];
    }
}
