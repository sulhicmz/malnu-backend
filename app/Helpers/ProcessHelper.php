<?php

declare(strict_types=1);

namespace App\Helpers;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class ProcessHelper
{
    public static function execute(string $command, array $arguments = [], ?string $workingDirectory = null, ?int $timeout = null): array
    {
        $process = new Process(array_merge([$command], $arguments), $workingDirectory);

        if ($timeout !== null) {
            $process->setTimeout($timeout);
        }

        $process->run();

        return [
            'exit_code' => $process->getExitCode(),
            'output' => $process->getOutput(),
            'error_output' => $process->getErrorOutput(),
            'successful' => $process->isSuccessful(),
        ];
    }

    public static function executeOrFail(string $command, array $arguments = [], ?string $workingDirectory = null, ?int $timeout = null): array
    {
        $process = new Process(array_merge([$command], $arguments), $workingDirectory);

        if ($timeout !== null) {
            $process->setTimeout($timeout);
        }

        try {
            $process->mustRun();
        } catch (ProcessFailedException $e) {
            return [
                'exit_code' => $e->getExitCode(),
                'output' => $e->getOutput(),
                'error_output' => $e->getErrorOutput(),
                'successful' => false,
                'exception' => $e->getMessage(),
            ];
        }

        return [
            'exit_code' => $process->getExitCode(),
            'output' => $process->getOutput(),
            'error_output' => $process->getErrorOutput(),
            'successful' => true,
        ];
    }

    public static function executeQuietly(string $command, array $arguments = [], ?string $workingDirectory = null): int
    {
        $result = self::execute($command, $arguments, $workingDirectory);

        return $result['exit_code'] ?? 1;
    }

    public static function escapeArgument(string $argument): string
    {
        return Process::escapeArgument($argument);
    }
}
