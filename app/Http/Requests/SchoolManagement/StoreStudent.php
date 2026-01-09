<?php

declare(strict_types=1);

namespace App\Http\Requests\SchoolManagement;

use Hyperf\Foundation\Http\FormRequest;

class StoreStudent extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'nisn' => 'required|string|max:20|unique:students,nisn',
            'email' => 'sometimes|nullable|email|max:255|unique:students,email',
            'class_id' => 'required|integer|exists:classes,id',
            'enrollment_year' => 'required|integer|digits:4',
            'status' => 'required|string|in:active,inactive,graduated,suspended',
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
            'name.required' => 'The name field is required.',
            'name.string' => 'The name must be a string.',
            'name.max' => 'The name may not be greater than 255 characters.',
            'nisn.required' => 'The nisn field is required.',
            'nisn.string' => 'The nisn must be a string.',
            'nisn.max' => 'The nisn may not be greater than 20 characters.',
            'nisn.unique' => 'The NISN has already been taken.',
            'email.email' => 'The email must be a valid email address.',
            'email.max' => 'The email may not be greater than 255 characters.',
            'email.unique' => 'The email has already been taken.',
            'class_id.required' => 'The class_id field is required.',
            'class_id.integer' => 'The class_id must be an integer.',
            'class_id.exists' => 'The selected class is invalid.',
            'enrollment_year.required' => 'The enrollment_year field is required.',
            'enrollment_year.integer' => 'The enrollment_year must be an integer.',
            'enrollment_year.digits' => 'The enrollment_year must be 4 digits.',
            'status.required' => 'The status field is required.',
            'status.string' => 'The status must be a string.',
            'status.in' => 'The status must be one of: active, inactive, graduated, suspended.',
        ];
    }
}
