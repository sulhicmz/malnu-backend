<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use Hypervel\Support\Facades\Config;

class ConfigurationTest extends TestCase
{
    /**
     * Test that the application environment is properly configured.
     */
    public function testApplicationEnvironment(): void
    {
        $env = Config::get('app.env');
        $this->assertIsString($env);
        $this->assertNotEmpty($env);
    }

    /**
     * Test that the application name is properly configured.
     */
    public function testApplicationName(): void
    {
        $name = Config::get('app.name');
        $this->assertIsString($name);
        $this->assertNotEmpty($name);
    }

    /**
     * Test that the application key is set.
     */
    public function testApplicationKey(): void
    {
        $key = Config::get('app.key');
        $this->assertIsString($key);
        $this->assertNotEmpty($key);
    }

    /**
     * Test that database configuration is properly set.
     */
    public function testDatabaseConfiguration(): void
    {
        $driver = Config::get('database.default');
        $this->assertIsString($driver);
        $this->assertNotEmpty($driver);
    }

    /**
     * Test that cache configuration is properly set.
     */
    public function testCacheConfiguration(): void
    {
        $driver = Config::get('cache.default');
        $this->assertIsString($driver);
        $this->assertNotEmpty($driver);
    }

    /**
     * Test that session configuration is properly set.
     */
    public function testSessionConfiguration(): void
    {
        $driver = Config::get('session.driver');
        $this->assertIsString($driver);
        $this->assertNotEmpty($driver);
    }

    /**
     * Test that the application timezone is properly configured.
     */
    public function testApplicationTimezone(): void
    {
        $timezone = Config::get('app.timezone');
        $this->assertIsString($timezone);
        $this->assertNotEmpty($timezone);
    }

    /**
     * Test that the application locale is properly configured.
     */
    public function testApplicationLocale(): void
    {
        $locale = Config::get('app.locale');
        $this->assertIsString($locale);
        $this->assertNotEmpty($locale);
    }

    /**
     * Test that logging configuration is properly set.
     */
    public function testLoggingConfiguration(): void
    {
        $driver = Config::get('logging.default');
        $this->assertIsString($driver);
        $this->assertNotEmpty($driver);
    }
}