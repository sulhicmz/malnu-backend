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
        return [
            'name' => 'sometimes|string|max:255',
            'nisn' => 'sometimes|string|max:20',
            'email' => 'sometimes|nullable|email|max:255',
            'class_id' => 'sometimes|integer|exists:classes,id',
            'enrollment_year' => 'sometimes|integer|digits:4',
            'status' => 'sometimes|string|in:active,inactive,graduated,suspended',
            'phone' => 'sometimes|nullable|string|max:20',
            'address' => 'sometimes|nullable|string|max:500',
            'date_of_birth' => 'sometimes|nullable|date|date_format:Y-m-d',
            'gender' => 'sometimes|nullable|string|in:male,female',
            'parent_name' => 'sometimes|nullable|string|max:255',
            'parent_phone' => 'sometimes|nullable|string|max:20',
        ];
    }

    public function messages(): array
    {
        return [
            'name.string' => 'The name must be a string.',
            'name.max' => 'The name may not be greater than 255 characters.',
            'nisn.string' => 'The nisn must be a string.',
            'nisn.max' => 'The nisn may not be greater than 20 characters.',
            'email.email' => 'The email must be a valid email address.',
            'email.max' => 'The email may not be greater than 255 characters.',
            'class_id.integer' => 'The class_id must be an integer.',
            'class_id.exists' => 'The selected class is invalid.',
            'enrollment_year.integer' => 'The enrollment_year must be an integer.',
            'enrollment_year.digits' => 'The enrollment_year must be 4 digits.',
            'status.string' => 'The status must be a string.',
            'status.in' => 'The status must be one of: active, inactive, graduated, suspended.',
        ];
    }
}
