<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Hypervel\Foundation\Http\FormRequest;

class TeacherRequest extends FormRequest
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
                    'nip' => 'required|string|unique:teachers,nip',
                    'expertise' => 'required|string|max:255',
                    'join_date' => 'required|date',
                    'status' => 'required|in:active,inactive,resigned',
                ];
            case 'PUT':
            case 'PATCH': // Update
                $teacherId = $this->route('teacher');
                return [
                    'user_id' => 'sometimes|required|string|exists:users,id',
                    'nip' => "sometimes|required|string|unique:teachers,nip,{$teacherId},id",
                    'expertise' => 'sometimes|required|string|max:255',
                    'join_date' => 'sometimes|required|date',
                    'status' => 'sometimes|required|in:active,inactive,resigned',
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
            'nip.required' => 'NIP is required.',
            'nip.unique' => 'This NIP is already taken.',
            'expertise.required' => 'Expertise is required.',
            'join_date.required' => 'Join date is required.',
            'status.required' => 'Status is required.',
            'status.in' => 'Status must be active, inactive, or resigned.',
        ];
    }
}