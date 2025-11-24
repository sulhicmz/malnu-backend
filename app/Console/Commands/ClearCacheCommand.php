<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;
use Hyperf\Support\Facades\Cache;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class ClearCacheCommand extends HyperfCommand
{
    /**
     * The console command name.
     */
    protected ?string $name = 'cache:clear';

    /**
     * The console command description.
     */
    protected string $description = 'Clear application cache';

    protected ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        parent::__construct();
    }

    public function configure(): void
    {
        parent::configure();
        $this->setDescription($this->description);
        $this->addOption('tag', 't', InputOption::VALUE_OPTIONAL, 'Cache tag to clear');
        $this->addOption('prefix', 'p', InputOption::VALUE_OPTIONAL, 'Cache prefix to clear');
    }

    public function handle(): void
    {
        $tag = $this->input->getOption('tag');
        $prefix = $this->input->getOption('prefix');

        if ($tag) {
            $this->clearCacheByTag($tag);
        } elseif ($prefix) {
            $this->clearCacheByPrefix($prefix);
        } else {
            $this->clearAllCache();
        }
    }

    private function clearAllCache(): void
    {
        try {
            Cache::clear();
            $this->info('Application cache cleared successfully.');
        } catch (\Exception $e) {
            $this->error('Error clearing cache: ' . $e->getMessage());
        }
    }

    private function clearCacheByTag(string $tag): void
    {
        $this->error('Cache tags are not implemented in this version. Use cache:clear without options to clear all cache.');
    }

    private function clearCacheByPrefix(string $prefix): void
    {
        $this->error('Cache prefix clearing is not implemented in this version. Use cache:clear without options to clear all cache.');
    }
}