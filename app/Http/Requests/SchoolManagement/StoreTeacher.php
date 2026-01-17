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
            'nip' => 'required|string|max:20|unique:teachers,nip',
            'user_id' => 'sometimes|string|exists:users,id',
            'expertise' => 'sometimes|nullable|string|max:100',
            'join_date' => 'required|date',
            'status' => 'required|string|max:20',
        ];
    }

    public function messages(): array
    {
        return [
            'nip.required' => 'The NIP field is required.',
            'nip.string' => 'The NIP must be a string.',
            'nip.max' => 'The NIP must not exceed 20 characters.',
            'nip.unique' => 'The NIP has already been taken.',
            'user_id.string' => 'The user_id must be a valid UUID.',
            'user_id.exists' => 'The selected user does not exist.',
            'expertise.string' => 'The expertise must be a string.',
            'expertise.max' => 'The expertise must not exceed 100 characters.',
            'join_date.required' => 'The join date field is required.',
            'join_date.date' => 'The join date must be a valid date.',
            'status.required' => 'The status field is required.',
            'status.string' => 'The status must be a string.',
            'status.max' => 'The status must not exceed 20 characters.',
        ];
    }
}
