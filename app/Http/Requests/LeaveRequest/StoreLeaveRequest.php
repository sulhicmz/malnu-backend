<?php

declare(strict_types=1);

namespace App\Http\Requests\LeaveRequest;

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
            'staff_id' => 'required|integer|exists:staff,id',
            'leave_type_id' => 'required|integer|exists:leave_types,id',
            'start_date' => 'required|date|date_format:Y-m-d',
            'end_date' => 'required|date|date_format:Y-m-d|after_or_equal:start_date',
            'reason' => 'required|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'staff_id.required' => 'The staff_id field is required.',
            'staff_id.integer' => 'Invalid staff_id',
            'staff_id.exists' => 'The selected staff is invalid.',
            'leave_type_id.required' => 'The leave_type_id field is required.',
            'leave_type_id.integer' => 'Invalid leave_type_id',
            'leave_type_id.exists' => 'The selected leave type is invalid.',
            'start_date.required' => 'The start_date field is required.',
            'start_date.date' => 'Invalid date format',
            'start_date.date_format' => 'Invalid date format',
            'end_date.required' => 'The end_date field is required.',
            'end_date.date' => 'Invalid date format',
            'end_date.date_format' => 'Invalid date format',
            'end_date.after_or_equal' => 'Start date must be before or equal to end date',
            'reason.required' => 'The reason field is required.',
            'reason.string' => 'The reason must be a string.',
            'reason.max' => 'Reason must not exceed 500 characters',
        ];
    }
}
