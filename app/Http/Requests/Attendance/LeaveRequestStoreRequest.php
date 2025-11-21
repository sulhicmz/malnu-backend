<?php

declare(strict_types=1);

namespace App\Http\Requests\Attendance;

use Hyperf\Validation\Request\FormRequest;

class LeaveRequestStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'staff_id' => 'required|integer|exists:staff,id',
            'leave_type_id' => 'required|integer|exists:leave_types,id',
            'start_date' => 'required|date|before_or_equal:end_date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:500',
            'comments' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'staff_id.required' => 'Staff ID is required.',
            'staff_id.integer' => 'Staff ID must be a valid integer.',
            'staff_id.exists' => 'The selected staff does not exist.',
            'leave_type_id.required' => 'Leave type ID is required.',
            'leave_type_id.integer' => 'Leave type ID must be a valid integer.',
            'leave_type_id.exists' => 'The selected leave type does not exist.',
            'start_date.required' => 'Start date is required.',
            'start_date.date' => 'Start date must be a valid date.',
            'end_date.required' => 'End date is required.',
            'end_date.date' => 'End date must be a valid date.',
            'reason.required' => 'Leave reason is required.',
            'reason.string' => 'Leave reason must be a string.',
            'reason.max' => 'Leave reason may not be greater than :max characters.',
        ];
    }
}