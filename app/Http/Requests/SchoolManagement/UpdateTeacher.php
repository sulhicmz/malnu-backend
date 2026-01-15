<?php

declare(strict_types=1);

namespace App\Http\Requests\SchoolManagement;

use Hyperf\Foundation\Http\FormRequest;

class UpdateTeacher extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'name' => 'sometimes|string|max:255',
            'nip' => 'sometimes|string|max:50|unique:teachers,nip,' . $id,
            'email' => 'nullable|email|max:255|unique:teachers,email,' . $id,
            'status' => 'sometimes|in:active,inactive',
        ];
    }

    public function messages(): array
    {
        return [
            'name.string' => 'The name must be a string.',
            'name.max' => 'The name must not exceed 255 characters.',
            'nip.string' => 'The NIP must be a string.',
            'nip.max' => 'The NIP must not exceed 50 characters.',
            'nip.unique' => 'The NIP has already been taken.',
            'email.email' => 'The email must be a valid email address.',
            'email.max' => 'The email must not exceed 255 characters.',
            'email.unique' => 'The email has already been taken.',
            'status.in' => 'The status must be one of: active, inactive.',
        ];
    }
}
