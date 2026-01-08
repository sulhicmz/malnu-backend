<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Hypervel\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|regex:/^[a-zA-Z0-9\s\-_\.]+$/',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|max:255|regex:/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)/',
            'username' => 'nullable|string|max:255|unique:users,username',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Name is required',
            'name.max' => 'Name must not exceed 255 characters',
            'name.regex' => 'Name contains invalid characters',
            'email.required' => 'Email address is required',
            'email.email' => 'Please provide a valid email address',
            'email.max' => 'Email address must not exceed 255 characters',
            'email.unique' => 'This email address is already registered',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least 8 characters',
            'password.max' => 'Password must not exceed 255 characters',
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, and one number',
            'username.required' => 'Username is required',
            'username.max' => 'Username must not exceed 255 characters',
            'username.unique' => 'This username is already taken',
        ];
    }
}
