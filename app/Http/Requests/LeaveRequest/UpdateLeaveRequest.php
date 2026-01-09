<?php

declare(strict_types=1);

namespace App\Http\Requests\LeaveRequest;

use Hyperf\Foundation\Http\FormRequest;

class UpdateLeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'comments' => 'sometimes|string|nullable',
        ];
    }

    public function messages(): array
    {
        return [
            'comments.string' => 'Comments must be a string',
        ];
    }
}
