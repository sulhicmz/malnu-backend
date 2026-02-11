<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\CacheService;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class CacheServiceTest extends TestCase
{
    private CacheService $cacheService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cacheService = new CacheService();
    }

    public function testCanStoreAndRetrieveValue(): void
    {
        $key = 'test_key';
        $value = ['data' => 'test_value'];
        $ttl = 60;

        $result = $this->cacheService->set($key, $value, $ttl);

        $this->assertTrue($result);
        $this->assertEquals($value, $this->cacheService->get($key));
    }

    public function testGetReturnsDefaultValueWhenKeyNotExists(): void
    {
        $key = 'non_existent_key';
        $default = 'default_value';

        $result = $this->cacheService->get($key, $default);

        $this->assertEquals($default, $result);
    }

    public function testRememberReturnsCachedValue(): void
    {
        $key = 'remember_key';
        $cachedValue = ['cached' => true];
        $ttl = 60;

        $this->cacheService->set($key, $cachedValue, $ttl);

        $callbackCalled = false;
        $callback = function () use (&$callbackCalled) {
            $callbackCalled = true;

            return ['callback' => true];
        };

        $result = $this->cacheService->remember($key, $ttl, $callback);

        $this->assertFalse($callbackCalled);
        $this->assertEquals($cachedValue, $result);
    }

    public function testRememberExecutesCallbackWhenCacheMiss(): void
    {
        $key = 'remember_miss_key';
        $callbackValue = ['callback' => true];
        $ttl = 60;

        $callback = fn () => $callbackValue;

        $result = $this->cacheService->remember($key, $ttl, $callback);

        $this->assertEquals($callbackValue, $result);
    }

    public function testForgetRemovesCachedValue(): void
    {
        $key = 'forget_key';
        $value = ['data' => 'test'];

        $this->cacheService->set($key, $value, 60);
        $this->assertNotNull($this->cacheService->get($key));

        $result = $this->cacheService->forget($key);

        $this->assertTrue($result);
        $this->assertNull($this->cacheService->get($key));
    }

    public function testGenerateKeyCreatesConsistentHash(): void
    {
        $prefix = 'test_prefix';
        $params = [
            'user_id' => '123',
            'page' => 2,
            'filter' => 'active',
        ];

        $key1 = $this->cacheService->generateKey($prefix, $params);
        $key2 = $this->cacheService->generateKey($prefix, $params);

        $this->assertEquals($key1, $key2);
        $this->assertStringStartsWith($prefix, $key1);
    }

    public function testGenerateKeyHandlesDifferentParamOrders(): void
    {
        $prefix = 'test_prefix';
        $params1 = ['a' => 1, 'b' => 2];
        $params2 = ['b' => 2, 'a' => 1];

        $key1 = $this->cacheService->generateKey($prefix, $params1);
        $key2 = $this->cacheService->generateKey($prefix, $params2);

        $this->assertEquals($key1, $key2);
    }

    public function testGetTtlReturnsCorrectValues(): void
    {
        $this->assertEquals(60, $this->cacheService->getTTL('short'));
        $this->assertEquals(300, $this->cacheService->getTTL('medium'));
        $this->assertEquals(3600, $this->cacheService->getTTL('long'));
        $this->assertEquals(86400, $this->cacheService->getTTL('day'));
        $this->assertEquals(300, $this->cacheService->getTTL('invalid'));
    }

    public function testSetAndGetComplexDataTypes(): void
    {
        $key = 'complex_key';

        $complexData = [
            'string' => 'test',
            'integer' => 123,
            'float' => 3.14,
            'boolean' => true,
            'null' => null,
            'array' => ['nested' => ['data' => [1, 2, 3]]],
            'object' => (object) ['property' => 'value'],
        ];

        $this->cacheService->set($key, $complexData, 60);
        $result = $this->cacheService->get($key);

        $this->assertEquals($complexData, $result);
    }

    public function testFlushClearsAllCache(): void
    {
        $key1 = 'flush_key_1';
        $key2 = 'flush_key_2';

        $this->cacheService->set($key1, ['data' => 1], 60);
        $this->cacheService->set($key2, ['data' => 2], 60);

        $result = $this->cacheService->flush();

        $this->assertTrue($result);
        $this->assertNull($this->cacheService->get($key1));
        $this->assertNull($this->cacheService->get($key2));
    }
}
