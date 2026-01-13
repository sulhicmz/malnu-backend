<?php
 
 declare(strict_types=1);
 
 namespace App\Providers;
 
 use Hyperf\Support\ServiceProvider;
 
 class AppServiceProvider extends ServiceProvider
 {
     public function boot(): void
     {
         $this->validateJwtSecret();
     }
 
     public function register(): void
     {
     }
 
     private function validateJwtSecret(): void
     {
         $env = \env('APP_ENV', 'local');
         
         if (in_array($env, ['local', 'testing'])) {
             return;
         }
         
         $jwtSecret = \env('JWT_SECRET', '');
         
         if (empty($jwtSecret)) {
             throw new \RuntimeException(
                 'JWT_SECRET is not set in .env file. ' .
                 'Generate a secure secret: openssl rand -hex 32'
             );
         }
         
         $placeholders = [
             'your-secret-key-here',
             'change-me',
             'your-jwt-secret',
             'jwt-secret-key',
             'secret',
             'password',
         ];
         
         if (in_array(strtolower(trim($jwtSecret)), $placeholders)) {
             throw new \RuntimeException(
                 'JWT_SECRET cannot use placeholder values. ' .
                 'Generate a secure secret: openssl rand -hex 32'
             );
         }
         
         if (strlen($jwtSecret) < 32) {
             throw new \RuntimeException(
                 'JWT_SECRET must be at least 32 characters long for HS256 algorithm. ' .
                 'Current length: ' . strlen($jwtSecret) . '. ' .
                 'Generate a secure secret: openssl rand -hex 32'
             );
         }
     }
 }
