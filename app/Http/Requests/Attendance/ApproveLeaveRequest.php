<?php

declare(strict_types=1);

namespace App\Http\Requests\Attendance;

use Hyperf\Foundation\Http\FormRequest;

class ApproveLeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'approval_comments' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'approval_comments.string' => 'The approval comments must be a string.',
            'approval_comments.max' => 'The approval comments must not exceed 500 characters.',
        ];
    }
}
