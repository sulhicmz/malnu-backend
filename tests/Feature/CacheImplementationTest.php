<?php

declare(strict_types=1);

namespace Tests\Feature;

use Hyperf\Config\Config;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Redis\Redis;
use PHPUnit\Framework\TestCase;

class CacheImplementationTest extends TestCase
{
    #[Inject]
    private Redis $redis;

    private string $testPrefix = 'test_cache:';

    protected function setUp(): void
    {
        parent::setUp();
        $this->clearTestCache();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->clearTestCache();
    }

    private function clearTestCache(): void
    {
        $keys = $this->redis->keys($this->testPrefix . '*');
        foreach ($keys as $key) {
            $this->redis->del($key);
        }
    }

    public function test_cache_driver_is_configured(): void
    {
        $driver = env('CACHE_DRIVER');
        $this->assertEquals('redis', $driver, 'Cache driver should be configured as redis');
    }

    public function test_session_driver_is_configured(): void
    {
        $driver = env('SESSION_DRIVER');
        $this->assertEquals('redis', $driver, 'Session driver should be configured as redis');
    }

    public function test_cache_ttl_configuration(): void
    {
        $ttl = (int) env('CACHE_DEFAULT_TTL', 3600);
        $this->assertEquals(3600, $ttl, 'Default cache TTL should be 1 hour');
    }

    public function test_basic_cache_set_and_get(): void
    {
        $key = $this->testPrefix . 'test_key';
        $value = 'test_value';

        $this->redis->set($key, $value, 3600);
        $retrieved = $this->redis->get($key);

        $this->assertEquals($value, $retrieved, 'Cache should return stored value');
    }

    public function test_cache_expiration(): void
    {
        $key = $this->testPrefix . 'expiring_key';
        $value = 'expires_soon';

        $this->redis->set($key, $value, 2);
        $this->assertTrue($this->redis->exists($key), 'Key should exist immediately');

        sleep(3);
        $this->assertFalse($this->redis->exists($key), 'Key should expire after TTL');
    }

    public function test_cache_delete(): void
    {
        $key = $this->testPrefix . 'delete_key';
        $value = 'delete_me';

        $this->redis->set($key, $value, 3600);
        $this->assertTrue($this->redis->exists($key), 'Key should exist before delete');

        $this->redis->del($key);
        $this->assertFalse($this->redis->exists($key), 'Key should not exist after delete');
    }

    public function test_cache_increment_decrement(): void
    {
        $key = $this->testPrefix . 'counter_key';
        $initial = $this->redis->incr($key);
        $this->assertEquals(1, $initial, 'Initial increment should return 1');

        $this->redis->incr($key);
        $this->assertEquals(2, $this->redis->get($key), 'Second increment should return 2');

        $this->redis->decr($key);
        $this->assertEquals(1, $this->redis->get($key), 'Decrement should reduce value');
    }

    public function test_cache_multiple_operations(): void
    {
        $data = [
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3',
        ];

        foreach ($data as $key => $value) {
            $this->redis->set($this->testPrefix . $key, $value, 3600);
        }

        $retrieved1 = $this->redis->get($this->testPrefix . 'key1');
        $retrieved2 = $this->redis->get($this->testPrefix . 'key2');
        $retrieved3 = $this->redis->get($this->testPrefix . 'key3');

        $this->assertEquals('value1', $retrieved1);
        $this->assertEquals('value2', $retrieved2);
        $this->assertEquals('value3', $retrieved3);
    }

    public function test_cache_flush(): void
    {
        $key1 = $this->testPrefix . 'flush_key1';
        $key2 = $this->testPrefix . 'flush_key2';

        $this->redis->set($key1, 'value1', 3600);
        $this->redis->set($key2, 'value2', 3600);

        $this->assertTrue($this->redis->exists($key1));
        $this->assertTrue($this->redis->exists($key2));

        $this->clearTestCache();

        $this->assertFalse($this->redis->exists($key1), 'All keys should be flushed');
        $this->assertFalse($this->redis->exists($key2), 'All keys should be flushed');
    }

    public function test_session_storage_in_redis(): void
    {
        $sessionKey = $this->testPrefix . 'session:' . uniqid();
        $sessionData = ['user_id' => 123, 'role' => 'admin'];

        $this->redis->set($sessionKey, json_encode($sessionData), 7200);
        $retrieved = $this->redis->get($sessionKey);
        $decoded = json_decode($retrieved, true);

        $this->assertEquals($sessionData['user_id'], $decoded['user_id']);
        $this->assertEquals($sessionData['role'], $decoded['role']);
    }

    public function test_cache_with_ttl_override(): void
    {
        $key = $this->testPrefix . 'ttl_key';
        $value = 'ttl_value';

        $this->redis->set($key, $value, 10);
        $this->assertEquals($value, $this->redis->get($key), 'Should retrieve value before expiration');

        sleep(2);
        $this->assertNull($this->redis->get($key), 'Value should expire after TTL');
    }

    public function test_cache_performance(): void
    {
        $iterations = 1000;
        $startTime = microtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            $key = $this->testPrefix . 'perf_key_' . $i;
            $value = 'perf_value_' . $i;
            $this->redis->set($key, $value, 3600);
        }

        $endTime = microtime(true);
        $totalTime = ($endTime - $startTime) * 1000;

        $avgTimePerOp = $totalTime / $iterations;

        $this->assertLessThan(1, $avgTimePerOp, 'Average operation should be under 1ms');
    }

    public function test_cache_key_prefix_isolation(): void
    {
        $prefix1 = 'prefix1:';
        $prefix2 = 'prefix2:';

        $key1 = 'test_key';
        $key2 = 'test_key';

        $this->redis->set($prefix1 . $key1, 'value1', 3600);
        $this->redis->set($prefix2 . $key2, 'value2', 3600);

        $this->assertEquals('value1', $this->redis->get($prefix1 . $key1), 'Should retrieve with correct prefix');
        $this->assertEquals('value2', $this->redis->get($prefix2 . $key2), 'Should retrieve with correct prefix');
        $this->assertNull($this->redis->get($prefix1 . $key2), 'Should not retrieve with wrong prefix');
        $this->assertNull($this->redis->get($prefix2 . $key1), 'Should not retrieve with wrong prefix');
    }
}
