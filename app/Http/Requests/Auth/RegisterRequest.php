<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Services\PasswordValidator;
use Hyperf\Foundation\Http\FormRequest;
use Psr\Container\ContainerInterface;

class RegisterRequest extends FormRequest
{
    private PasswordValidator $passwordValidator;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->passwordValidator = new PasswordValidator();
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|min:3',
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The name field is required.',
            'name.string' => 'The name must be a string.',
            'name.min' => 'The name must be at least 3 characters.',
            'email.required' => 'The email field is required.',
            'email.email' => 'The email must be a valid email address.',
            'password.required' => 'The password field is required.',
            'password.string' => 'The password must be a string.',
            'password.min' => 'The password must be at least 8 characters.',
        ];
    }

    protected function validationData(): array
    {
        $data = parent::validationData();

        if (isset($data['password'])) {
            $passwordErrors = $this->passwordValidator->validate($data['password']);
            if (!empty($passwordErrors)) {
                $this->validator->errors()->add('password', $passwordErrors);
            }
        }

        return $data;
    }
}
