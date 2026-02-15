<?php

declare(strict_types=1);

namespace App\Http\Requests\SchoolManagement;

use Hypervel\Foundation\Http\FormRequest;

class StoreTeacher extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'nip' => 'required|string|max:50|unique:teachers,nip',
            'email' => 'nullable|email|max:255|unique:teachers,email',
            'subject_id' => 'required|integer|exists:subjects,id',
            'join_date' => 'required|date|before_or_equal:today',
            'status' => 'required|in:active,inactive',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The name field is required.',
            'name.max' => 'The name must not exceed 255 characters.',
            'nip.required' => 'The NIP field is required.',
            'nip.max' => 'The NIP must not exceed 50 characters.',
            'nip.unique' => 'The NIP has already been taken.',
            'email.email' => 'The email must be a valid email address.',
            'email.max' => 'The email must not exceed 255 characters.',
            'email.unique' => 'The email has already been taken.',
            'subject_id.required' => 'The subject_id field is required.',
            'subject_id.integer' => 'The subject_id must be an integer.',
            'subject_id.exists' => 'The selected subject does not exist.',
            'join_date.required' => 'The join_date field is required.',
            'join_date.date' => 'The join_date must be a valid date.',
            'join_date.before_or_equal' => 'The join_date must be today or in the past.',
            'status.required' => 'The status field is required.',
            'status.in' => 'The status must be one of: active, inactive.',
        ];
    }
}
