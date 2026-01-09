<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Hypervel\Console\Command;
use OpenApi\Generator;
use Psr\Container\ContainerInterface;

class GenerateOpenApiSpec extends Command
{
    protected ?string $signature = 'openapi:generate {--o|output=public/openapi.json : Output file path} {--f|format=json : Output format (json or yaml)}';

    protected string $description = 'Generate OpenAPI specification from PHP annotations';

    protected ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        parent::__construct();
    }

    public function handle()
    {
        $outputPath = $this->input->getOption('output') ?: 'public/openapi.json';
        $format = $this->input->getOption('format') ?: 'json';

        $this->output->writeln('<info>Scanning for OpenAPI annotations...</info>');

        $openapi = Generator::scan([
            BASE_PATH . '/app/Http/Controllers',
        ]);

        if ($format === 'yaml') {
            $this->output->writeln('<info>Generating OpenAPI YAML specification...</info>');
            $yaml = $openapi->toYaml();
            file_put_contents($outputPath, $yaml);
        } else {
            $this->output->writeln('<info>Generating OpenAPI JSON specification...</info>');
            $json = $openapi->toJson();
            file_put_contents($outputPath, $json);
        }

        $this->output->writeln("<info>OpenAPI specification generated successfully at: {$outputPath}</info>");

        return 0;
    }
}
