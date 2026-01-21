<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Services\PasswordValidator;
use Hyperf\Foundation\Http\FormRequest;
use Psr\Container\ContainerInterface;

class ResetPasswordRequest extends FormRequest
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
            'token' => 'required|string',
            'password' => 'required|string|min:8',
        ];
    }

    public function messages(): array
    {
        return [
            'token.required' => 'The token field is required.',
            'token.string' => 'The token must be a string.',
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
