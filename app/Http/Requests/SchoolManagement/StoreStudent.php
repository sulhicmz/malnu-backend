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
            'nisn' => 'required|string|max:20|unique:students,nisn',
            'user_id' => 'sometimes|string|exists:users,id',
            'class_id' => 'required|string|exists:classes,id',
            'birth_date' => 'sometimes|nullable|date',
            'birth_place' => 'sometimes|nullable|string|max:50',
            'address' => 'sometimes|nullable|string',
            'parent_id' => 'sometimes|nullable|string|exists:parents,id',
            'enrollment_date' => 'required|date',
            'status' => 'required|string|max:20',
        ];
    }

    public function messages(): array
    {
        return [
            'nisn.required' => 'The NISN field is required.',
            'nisn.string' => 'The NISN must be a string.',
            'nisn.max' => 'The NISN must not exceed 20 characters.',
            'nisn.unique' => 'The NISN has already been taken.',
            'user_id.string' => 'The user_id must be a valid UUID.',
            'user_id.exists' => 'The selected user does not exist.',
            'class_id.required' => 'The class_id field is required.',
            'class_id.string' => 'The class_id must be a valid UUID.',
            'class_id.exists' => 'The selected class does not exist.',
            'birth_date.date' => 'The birth date must be a valid date.',
            'birth_place.string' => 'The birth place must be a string.',
            'birth_place.max' => 'The birth place must not exceed 50 characters.',
            'address.string' => 'The address must be a string.',
            'parent_id.string' => 'The parent_id must be a valid UUID.',
            'parent_id.exists' => 'The selected parent does not exist.',
            'enrollment_date.required' => 'The enrollment date field is required.',
            'enrollment_date.date' => 'The enrollment date must be a valid date.',
            'status.required' => 'The status field is required.',
            'status.string' => 'The status must be a string.',
            'status.max' => 'The status must not exceed 20 characters.',
        ];
    }
}
