<?php

declare(strict_types=1);

namespace App\Http\Requests\SchoolManagement;

use Hyperf\Foundation\Http\FormRequest;

class UpdateStudent extends FormRequest
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
            'nisn' => 'sometimes|string|max:50|unique:students,nisn,' . $id,
            'status' => 'sometimes|in:active,inactive,graduated',
        ];
    }

    public function messages(): array
    {
        return [
            'name.string' => 'The name must be a string.',
            'name.max' => 'The name must not exceed 255 characters.',
            'nisn.string' => 'The NISN must be a string.',
            'nisn.max' => 'The NISN must not exceed 50 characters.',
            'nisn.unique' => 'The NISN has already been taken.',
            'email.email' => 'The email must be a valid email address.',
            'email.max' => 'The email must not exceed 255 characters.',
            'email.unique' => 'The email has already been taken.',
            'status.in' => 'The status must be one of: active, inactive, graduated.',
        ];
    }
}
