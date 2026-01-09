<?php

declare(strict_types=1);

namespace App\Http\Requests\SchoolManagement;

use Hyperf\Foundation\Http\FormRequest;

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
            'nip' => 'required|string|max:20|unique:teachers,nip',
            'email' => 'sometimes|nullable|email|max:255|unique:teachers,email',
            'subject_id' => 'required|integer|exists:subjects,id',
            'class_id' => 'sometimes|nullable|integer|exists:classes,id',
            'join_date' => 'required|date|date_format:Y-m-d',
            'status' => 'sometimes|string|in:active,inactive,on_leave,resigned',
            'phone' => 'sometimes|nullable|string|max:20',
            'address' => 'sometimes|nullable|string|max:500',
            'date_of_birth' => 'sometimes|nullable|date|date_format:Y-m-d',
            'gender' => 'sometimes|nullable|string|in:male,female',
            'education_level' => 'sometimes|nullable|string|max:255',
            'specialization' => 'sometimes|nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The name field is required.',
            'name.string' => 'The name must be a string.',
            'name.max' => 'The name may not be greater than 255 characters.',
            'nip.required' => 'The nip field is required.',
            'nip.string' => 'The nip must be a string.',
            'nip.max' => 'The nip may not be greater than 20 characters.',
            'nip.unique' => 'The NIP has already been taken.',
            'email.email' => 'The email must be a valid email address.',
            'email.max' => 'The email may not be greater than 255 characters.',
            'email.unique' => 'The email has already been taken.',
            'subject_id.required' => 'The subject_id field is required.',
            'subject_id.integer' => 'The subject_id must be an integer.',
            'subject_id.exists' => 'The selected subject is invalid.',
            'class_id.integer' => 'The class_id must be an integer.',
            'class_id.exists' => 'The selected class is invalid.',
            'join_date.required' => 'The join_date field is required.',
            'join_date.date' => 'Invalid date format',
            'join_date.date_format' => 'Invalid date format',
            'status.string' => 'The status must be a string.',
            'status.in' => 'The status must be one of: active, inactive, on_leave, resigned.',
        ];
    }
}
