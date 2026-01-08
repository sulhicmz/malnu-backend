<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Hypervel\Console\Command;

class GenerateJwtSecretCommand extends Command
{
    protected ?string $signature = 'jwt:secret';

    protected string $description = 'Generate a secure JWT_SECRET and add it to .env file';

    public function handle()
    {
        $key = $this->generateSecureKey();

        $envFile = base_path('.env');

        if (! file_exists($envFile)) {
            $this->output->writeln('<error>.env file not found. Please copy .env.example to .env first.</error>');
            return 1;
        }

        $envContent = file_get_contents($envFile);

        if (str_contains($envContent, 'JWT_SECRET=')) {
            $envContent = preg_replace(
                '/^JWT_SECRET=.*$/m',
                'JWT_SECRET=' . $key,
                $envContent
            );
        } else {
            $envContent .= "\nJWT_SECRET=" . $key;
        }

        file_put_contents($envFile, $envContent);

        $this->output->writeln('<info>JWT secret generated successfully!</info>');
        $this->output->writeln('<comment>Do NOT commit this secret to version control!</comment>');
        $this->output->writeln('<info>JWT_SECRET=' . $key . '</info>');

        return 0;
    }

    private function generateSecureKey(): string
    {
        return bin2hex(random_bytes(32));
    }
}
