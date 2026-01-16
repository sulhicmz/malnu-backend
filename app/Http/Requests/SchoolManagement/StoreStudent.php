<?php

declare(strict_types=1);

namespace App\Http\Requests\SchoolManagement;

use App\Models\SchoolManagement\Student;
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
            'nisn' => 'required|string|max:50|unique:students,nisn',
            'email' => 'nullable|email|max:255|unique:students,email',
            'class_id' => 'required|integer|exists:classes,id',
            'enrollment_year' => 'required|integer|min:1900|max:2100',
            'status' => 'required|in:active,inactive,graduated',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The name field is required.',
            'name.max' => 'The name must not exceed 255 characters.',
            'nisn.required' => 'The NISN field is required.',
            'nisn.max' => 'The NISN must not exceed 50 characters.',
            'nisn.unique' => 'The NISN has already been taken.',
            'email.email' => 'The email must be a valid email address.',
            'email.max' => 'The email must not exceed 255 characters.',
            'email.unique' => 'The email has already been taken.',
            'class_id.required' => 'The class_id field is required.',
            'class_id.integer' => 'The class_id must be an integer.',
            'class_id.exists' => 'The selected class does not exist.',
            'enrollment_year.required' => 'The enrollment_year field is required.',
            'enrollment_year.integer' => 'The enrollment_year must be an integer.',
            'enrollment_year.min' => 'The enrollment_year must be at least 1900.',
            'enrollment_year.max' => 'The enrollment_year must not exceed 2100.',
            'status.required' => 'The status field is required.',
            'status.in' => 'The status must be one of: active, inactive, graduated.',
        ];
    }
}
