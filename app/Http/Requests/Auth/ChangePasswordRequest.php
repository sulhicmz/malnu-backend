<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Services\PasswordValidator;
use Hyperf\Foundation\Http\FormRequest;
use Psr\Container\ContainerInterface;

class ChangePasswordRequest extends FormRequest
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
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8',
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'The current_password field is required.',
            'current_password.string' => 'The current_password must be a string.',
            'new_password.required' => 'The new_password field is required.',
            'new_password.string' => 'The new_password must be a string.',
            'new_password.min' => 'The new_password must be at least 8 characters.',
        ];
    }

    protected function validationData(): array
    {
        $data = parent::validationData();

        if (isset($data['new_password'])) {
            $passwordErrors = $this->passwordValidator->validate($data['new_password']);
            if (!empty($passwordErrors)) {
                $this->validator->errors()->add('new_password', $passwordErrors);
            }
        }

        return $data;
    }
}
