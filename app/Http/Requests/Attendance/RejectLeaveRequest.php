<?php

declare(strict_types=1);

namespace App\Http\Requests\Attendance;

use Hyperf\Foundation\Http\FormRequest;

class RejectLeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'approval_comments' => 'required|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'approval_comments.required' => 'The approval comments field is required when rejecting a leave request.',
            'approval_comments.string' => 'The approval comments must be a string.',
            'approval_comments.max' => 'The approval comments must not exceed 500 characters.',
        ];
    }
}
