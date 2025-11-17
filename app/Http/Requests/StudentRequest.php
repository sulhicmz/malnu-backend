<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Hypervel\Foundation\Http\FormRequest;

class StudentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $method = $this->method();
        
        switch ($method) {
            case 'POST': // Create
                return [
                    'user_id' => 'required|string|exists:users,id',
                    'nisn' => 'required|string|unique:students,nisn',
                    'class_id' => 'required|string|exists:classes,id',
                    'birth_date' => 'required|date',
                    'birth_place' => 'required|string|max:255',
                    'address' => 'required|string',
                    'parent_id' => 'nullable|string|exists:parents,id',
                    'enrollment_date' => 'required|date',
                    'status' => 'required|in:active,inactive,graduated',
                ];
            case 'PUT':
            case 'PATCH': // Update
                $studentId = $this->route('student');
                return [
                    'user_id' => 'sometimes|required|string|exists:users,id',
                    'nisn' => "sometimes|required|string|unique:students,nisn,{$studentId},id",
                    'class_id' => 'sometimes|required|string|exists:classes,id',
                    'birth_date' => 'sometimes|required|date',
                    'birth_place' => 'sometimes|required|string|max:255',
                    'address' => 'sometimes|required|string',
                    'parent_id' => 'sometimes|nullable|string|exists:parents,id',
                    'enrollment_date' => 'sometimes|required|date',
                    'status' => 'sometimes|required|in:active,inactive,graduated',
                ];
            default:
                return [];
        }
    }
    
    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'user_id.required' => 'User ID is required.',
            'user_id.exists' => 'The selected user does not exist.',
            'nisn.required' => 'NISN is required.',
            'nisn.unique' => 'This NISN is already taken.',
            'class_id.required' => 'Class ID is required.',
            'class_id.exists' => 'The selected class does not exist.',
            'birth_date.required' => 'Birth date is required.',
            'birth_place.required' => 'Birth place is required.',
            'address.required' => 'Address is required.',
            'enrollment_date.required' => 'Enrollment date is required.',
            'status.required' => 'Status is required.',
            'status.in' => 'Status must be active, inactive, or graduated.',
        ];
    }
}