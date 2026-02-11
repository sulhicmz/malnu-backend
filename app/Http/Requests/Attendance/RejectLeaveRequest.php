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
            'approval_comments' => 'sometimes|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'approval_comments.string' => 'Rejection comments must be a string.',
            'approval_comments.max' => 'Rejection comments must not exceed 1000 characters.',
        ];
    }
}
