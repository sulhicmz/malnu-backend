<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\CacheService;
use Tests\TestCase;

/**
 * @internal
 * @covers \App\Services\CacheService
 */
class CacheServiceTest extends TestCase
{
    private CacheService $cacheService;

    private $mockCacheDriver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cacheService = new CacheService();
        $this->mockCacheDriver = $this->createMock('Psr\SimpleCache\CacheInterface');
    }

    public function testStoreValue(): void
    {
        $key = 'test_key';
        $value = ['data' => 'test_value'];
        $ttl = 60;

        $this->mockCacheDriver->expects($this->once())
            ->method('set')
            ->with($key, $value, $ttl)
            ->willReturn(true);

        $result = $this->cacheService->set($key, $value, $ttl);

        $this->assertTrue($result);
    }

    public function testGetValue(): void
    {
        $key = 'test_key';
        $value = ['data' => 'test_value'];

        $this->mockCacheDriver->expects($this->once())
            ->method('get')
            ->with($key)
            ->willReturn($value);

        $result = $this->cacheService->get($key);

        $this->assertEquals($value, $result);
    }

    public function testGetReturnsDefaultWhenKeyNotExists(): void
    {
        $key = 'non_existent_key';
        $default = 'default_value';

        $this->mockCacheDriver->expects($this->once())
            ->method('get')
            ->with($key)
            ->willReturn(null);

        $result = $this->cacheService->get($key, $default);

        $this->assertEquals($default, $result);
    }

    public function testRememberReturnsCachedValue(): void
    {
        $key = 'remember_key';
        $cachedValue = ['cached' => true];
        $ttl = 60;

        $this->mockCacheDriver->expects($this->once())
            ->method('get')
            ->with($key)
            ->willReturn($cachedValue);

        $callbackExecuted = false;
        $callback = function () use (&$callbackExecuted) {
            $callbackExecuted = true;
            return ['callback' => true];
        };

        $result = $this->cacheService->remember($key, $ttl, $callback);

        $this->assertEquals($cachedValue, $result);
        $this->assertFalse($callbackExecuted);
    }

    public function testRememberExecutesCallbackOnCacheMiss(): void
    {
        $key = 'remember_miss_key';
        $callbackValue = ['callback' => true];
        $ttl = 60;

        $this->mockCacheDriver->expects($this->once())
            ->method('get')
            ->with($key)
            ->willReturn(null);

        $callbackExecuted = false;
        $callback = function () use (&$callbackExecuted) {
            $callbackExecuted = true;
            return $callbackValue;
        };

        $this->mockCacheDriver->expects($this->once())
            ->method('set')
            ->with($key, $callbackValue, $ttl)
            ->willReturn(true);

        $result = $this->cacheService->remember($key, $ttl, $callback);

        $this->assertEquals($callbackValue, $result);
        $this->assertTrue($callbackExecuted);
    }

    public function testForgetRemovesValue(): void
    {
        $key = 'forget_key';

        $this->mockCacheDriver->expects($this->once())
            ->method('delete')
            ->with($key)
            ->willReturn(true);

        $result = $this->cacheService->forget($key);

        $this->assertTrue($result);
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
        ];

        $this->mockCacheDriver->expects($this->once())
            ->method('set')
            ->with($key, $complexData, 60)
            ->willReturn(true);

        $this->mockCacheDriver->expects($this->once())
            ->method('get')
            ->with($key)
            ->willReturn($complexData);

        $this->cacheService->set($key, $complexData, 60);
        $result = $this->cacheService->get($key);

        $this->assertEquals($complexData, $result);
    }

    public function testFlushClearsAllCache(): void
    {
        $key1 = 'flush_key_1';
        $key2 = 'flush_key_2';

        $this->mockCacheDriver->expects($this->exactly(3))
            ->method('set')
            ->willReturn(true);

        $this->mockCacheDriver->expects($this->once())
            ->method('clear')
            ->willReturn(true);

        $this->mockCacheDriver->expects($this->once())
            ->method('get')
            ->with($key1)
            ->willReturn(null);

        $this->mockCacheDriver->expects($this->once())
            ->method('get')
            ->with($key2)
            ->willReturn(null);

        $this->cacheService->set($key1, ['data' => 1], 60);
        $this->cacheService->set($key2, ['data' => 2], 60);

        $result = $this->cacheService->flush();

        $this->assertTrue($result);
    }
}
