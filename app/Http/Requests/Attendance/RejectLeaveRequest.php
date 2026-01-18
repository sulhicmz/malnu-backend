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
            'approval_comments' => 'required|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'approval_comments.required' => 'Comments are required when rejecting a leave request.',
            'approval_comments.string' => 'Comments must be a string.',
            'approval_comments.max' => 'Comments must not exceed 1000 characters.',
        ];
    }
}
