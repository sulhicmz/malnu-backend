<?php

declare(strict_types=1);

namespace App\Http\Requests\Attendance;

use Hypervel\Foundation\Http\FormRequest;

class UpdateLeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'comments' => 'sometimes|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'comments.string' => 'Comments must be a string.',
            'comments.max' => 'Comments must not exceed 1000 characters.',
        ];
    }
}
