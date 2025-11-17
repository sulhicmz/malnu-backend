<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Hypervel\Foundation\Http\FormRequest;

class ClassRequest extends FormRequest
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
                    'name' => 'required|string|max:255',
                    'level' => 'required|string|max:50',
                    'homeroom_teacher_id' => 'required|string|exists:teachers,id',
                    'academic_year' => 'required|string|max:20',
                    'capacity' => 'required|integer|min:1',
                ];
            case 'PUT':
            case 'PATCH': // Update
                $classId = $this->route('class');
                return [
                    'name' => 'sometimes|required|string|max:255',
                    'level' => 'sometimes|required|string|max:50',
                    'homeroom_teacher_id' => 'sometimes|required|string|exists:teachers,id',
                    'academic_year' => 'sometimes|required|string|max:20',
                    'capacity' => 'sometimes|required|integer|min:1',
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
            'name.required' => 'Class name is required.',
            'level.required' => 'Class level is required.',
            'homeroom_teacher_id.required' => 'Homeroom teacher ID is required.',
            'homeroom_teacher_id.exists' => 'The selected homeroom teacher does not exist.',
            'academic_year.required' => 'Academic year is required.',
            'capacity.required' => 'Capacity is required.',
            'capacity.integer' => 'Capacity must be a number.',
            'capacity.min' => 'Capacity must be at least 1.',
        ];
    }
}