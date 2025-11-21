<?php

declare(strict_types=1);

namespace App\Services;

use Hyperf\Contract\ValidatorInterface;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Psr\Container\ContainerInterface;

class ValidationService
{
    private ValidatorFactoryInterface $validatorFactory;

    public function __construct(ContainerInterface $container)
    {
        $this->validatorFactory = $container->get(ValidatorFactoryInterface::class);
    }

    /**
     * Validate data against rules
     */
    public function validate(array $data, array $rules, array $messages = [], array $customAttributes = []): array
    {
        $validator = $this->validatorFactory->make($data, $rules, $messages, $customAttributes);

        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            
            return [
                'valid' => false,
                'errors' => $errors,
                'first_error' => $this->getFirstError($errors)
            ];
        }

        return [
            'valid' => true,
            'errors' => [],
            'first_error' => null
        ];
    }

    /**
     * Get the first error message from validation errors
     */
    private function getFirstError(array $errors): ?string
    {
        foreach ($errors as $field => $fieldErrors) {
            if (is_array($fieldErrors) && !empty($fieldErrors)) {
                return $fieldErrors[0];
            }
        }
        
        return null;
    }

    /**
     * Validate email format
     */
    public function validateEmail(string $email): bool
    {
        return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Validate phone number format
     */
    public function validatePhone(string $phone): bool
    {
        // Basic phone validation - can be enhanced based on requirements
        return preg_match('/^[\+]?[1-9][\d]{0,15}$/', $phone) === 1;
    }

    /**
     * Validate date format
     */
    public function validateDate(string $date, string $format = 'Y-m-d'): bool
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    /**
     * Sanitize string input
     */
    public function sanitizeString(string $input): string
    {
        // Remove null bytes
        $input = str_replace("\0", '', $input);
        
        // Remove escape characters that could be used for injection
        $input = str_replace("\\", '', $input);
        
        // Basic XSS prevention - remove common script tags
        $input = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $input);
        $input = preg_replace('/<iframe\b[^<]*(?:(?!<\/iframe>)<[^<]*)*<\/iframe>/mi', '', $input);
        
        // Remove javascript: and vbscript: protocols
        $input = preg_replace('/javascript:/i', 'javascript&#58;', $input);
        $input = preg_replace('/vbscript:/i', 'vbscript&#58;', $input);
        
        // Remove data: and file: protocols (potential XSS vectors)
        $input = preg_replace('/(data|file):/i', '\1&#58;', $input);
        
        // Strip tags but allow some safe ones if needed (customize based on requirements)
        $input = strip_tags($input);
        
        // Trim whitespace
        $input = trim($input);
        
        return $input;
    }

    /**
     * Sanitize array of inputs
     */
    public function sanitizeArray(array $data): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                // Recursively sanitize nested arrays
                $sanitized[$key] = $this->sanitizeArray($value);
            } elseif (is_string($value)) {
                // Sanitize string values
                $sanitized[$key] = $this->sanitizeString($value);
            } else {
                // Keep non-string, non-array values as they are
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }
}