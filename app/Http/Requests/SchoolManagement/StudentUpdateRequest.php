<?php

declare(strict_types=1);

namespace App\Http\Requests\SchoolManagement;

use Hypervel\Foundation\Http\FormRequest;

class StudentUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $studentId = $this->route('id');

        return [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255|unique:students,email,' . $studentId . ',id',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'class_id' => 'sometimes|uuid|exists:class_models,id',
            'enrollment_date' => 'sometimes|date_format:Y-m-d',
            'date_of_birth' => 'nullable|date_format:Y-m-d|before:today',
            'gender' => 'nullable|in:male,female,other',
            'parent_name' => 'nullable|string|max:255',
            'parent_phone' => 'nullable|string|max:20',
        ];
    }

    public function messages(): array
    {
        return [
            'name.max' => 'Student name must not exceed 255 characters',
            'email.email' => 'Please provide a valid email address',
            'email.max' => 'Email address must not exceed 255 characters',
            'email.unique' => 'This email is already registered',
            'phone.max' => 'Phone number must not exceed 20 characters',
            'address.max' => 'Address must not exceed 500 characters',
            'class_id.uuid' => 'Invalid class ID format',
            'class_id.exists' => 'Selected class does not exist',
            'enrollment_date.date_format' => 'Enrollment date must be in Y-m-d format',
            'date_of_birth.date_format' => 'Date of birth must be in Y-m-d format',
            'date_of_birth.before' => 'Date of birth must be before today',
            'gender.in' => 'Gender must be male, female, or other',
            'parent_name.max' => 'Parent name must not exceed 255 characters',
            'parent_phone.max' => 'Parent phone must not exceed 20 characters',
        ];
    }
}
