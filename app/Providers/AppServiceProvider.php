<?php
 
 declare(strict_types=1);
 
 namespace App\Providers;
 
 use RuntimeException;
 
 class AppServiceProvider extends \Hyperf\Support\ServiceProvider
 {
     private array $insecureJwtSecrets = [
         'your-secret-key-here',
         'change-me',
         'your-jwt-secret',
         'jwt-secret-key',
         'secret',
         'password',
         'test-secret',
         'dev-secret',
         'default-secret-key',
     ];
 
     public function boot(): void
     {
         $this->validateJwtSecret();
     }
 
     public function register(): void
     {
     }
 
     private function validateJwtSecret(): void
     {
         $jwtSecret = env('JWT_SECRET');
         $appEnv = env('APP_ENV', 'production');
         
         if (!$jwtSecret) {
             if ($appEnv !== 'testing' && $appEnv !== 'local') {
                 throw new RuntimeException(
                     'JWT_SECRET environment variable is not set. ' .
                     'Please set a secure JWT secret in your .env file. ' .
                     'Generate one using: openssl rand -hex 32'
                 );
             }
             
             return;
         }
 
         if (in_array(strtolower(trim($jwtSecret)), $this->insecureJwtSecrets)) {
             throw new RuntimeException(
                 'JWT_SECRET is set to an insecure placeholder value (' . $jwtSecret . '). ' .
                 'Please generate a secure JWT secret using: openssl rand -hex 32'
             );
         }
 
         if (strlen($jwtSecret) < 32) {
             throw new RuntimeException(
                 'JWT_SECRET is too short (currently ' . strlen($jwtSecret) . ' characters). ' .
                 'It must be at least 32 characters for security. ' .
                 'Generate one using: openssl rand -hex 32'
             );
         }
     }
 }
