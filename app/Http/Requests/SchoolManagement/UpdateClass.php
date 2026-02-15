<?php

declare(strict_types=1);

namespace App\Http\Requests\SchoolManagement;

use Hypervel\Foundation\Http\FormRequest;

class UpdateClass extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:50',
            'level' => 'sometimes|string|max:20',
            'homeroom_teacher_id' => 'nullable|string|exists:teachers,id',
            'academic_year' => 'sometimes|string|max:9',
            'capacity' => 'nullable|integer|min:1|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'name.string' => 'The name must be a string.',
            'name.max' => 'The name must not exceed 50 characters.',
            'level.string' => 'The level must be a string.',
            'level.max' => 'The level must not exceed 20 characters.',
            'homeroom_teacher_id.string' => 'The homeroom teacher ID must be a string.',
            'homeroom_teacher_id.exists' => 'The selected homeroom teacher does not exist.',
            'academic_year.string' => 'The academic year must be a string.',
            'academic_year.max' => 'The academic year must not exceed 9 characters.',
            'capacity.integer' => 'The capacity must be an integer.',
            'capacity.min' => 'The capacity must be at least 1.',
            'capacity.max' => 'The capacity must not exceed 100.',
        ];
    }
}
