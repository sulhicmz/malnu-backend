<?php

namespace Tests\Unit;

use App\Services\CacheService;
use Tests\TestCase;

class CacheServiceTest extends TestCase
{
    public function test_cache_service_put_and_get(): void
    {
        $key = 'test_key';
        $value = 'test_value';

        // Test putting a value in cache
        $result = CacheService::put($key, $value, 60);
        $this->assertTrue($result);

        // Test getting the value from cache
        $retrievedValue = CacheService::get($key);
        $this->assertEquals($value, $retrievedValue);
    }

    public function test_cache_service_has(): void
    {
        $key = 'test_has_key';
        $value = 'test_has_value';

        // Initially key should not exist
        $this->assertFalse(CacheService::has($key));

        // After putting, it should exist
        CacheService::put($key, $value, 60);
        $this->assertTrue(CacheService::has($key));
    }

    public function test_cache_service_forget(): void
    {
        $key = 'test_forget_key';
        $value = 'test_forget_value';

        // Put value in cache
        CacheService::put($key, $value, 60);
        $this->assertTrue(CacheService::has($key));

        // Remove from cache
        $result = CacheService::forget($key);
        $this->assertTrue($result);
        
        // Should no longer exist
        $this->assertFalse(CacheService::has($key));
    }

    public function test_cache_service_forever(): void
    {
        $key = 'test_forever_key';
        $value = 'test_forever_value';

        // Test putting a value permanently in cache
        $result = CacheService::forever($key, $value);
        $this->assertTrue($result);

        // Test getting the value from cache
        $retrievedValue = CacheService::get($key);
        $this->assertEquals($value, $retrievedValue);
    }
}