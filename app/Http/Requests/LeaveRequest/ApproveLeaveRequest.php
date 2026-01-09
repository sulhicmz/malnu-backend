<?php

declare(strict_types=1);

namespace App\Http\Requests\LeaveRequest;

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
            'approval_comments' => 'sometimes|string|nullable',
        ];
    }

    public function messages(): array
    {
        return [
            'approval_comments.string' => 'Approval comments must be a string',
        ];
    }
}
