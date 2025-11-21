<?php

declare(strict_types=1);

namespace App\Http\Requests\Attendance;

use Hyperf\Validation\Request\FormRequest;

class LeaveRequestUpdateRequest extends FormRequest
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
            'comments' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'comments.string' => 'Comments must be a string.',
            'comments.max' => 'Comments may not be greater than :max characters.',
        ];
    }
}