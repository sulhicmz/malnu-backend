<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\CacheService;
use Tests\TestCase;

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

    protected function tearDown(): void
    {
        $this->cacheService->forget('test_key');
        $this->cacheService->forget('test_array_key');
        $this->cacheService->forget('test_ttl_key');
        $this->cacheService->forget('test_pattern_key_1');
        $this->cacheService->forget('test_pattern_key_2');
        $this->cacheService->forget('test_null_key');
        $this->cacheService->forget('test_callback_key');
        parent::tearDown();
    }

    public function testPutAndGetWorksWithStringValue()
    {
        $this->cacheService->put('test_key', 'test_value', 60);

        $result = $this->cacheService->get('test_key');

        $this->assertEquals('test_value', $result);
    }

    public function testPutAndGetWorksWithArrayValue()
    {
        $value = ['name' => 'John', 'age' => 30];
        $this->cacheService->put('test_array_key', $value, 60);

        $result = $this->cacheService->get('test_array_key');

        $this->assertEquals($value, $result);
    }

    public function testPutAndGetWorksWithIntegerValue()
    {
        $this->cacheService->put('test_key', 42, 60);

        $result = $this->cacheService->get('test_key');

        $this->assertEquals(42, $result);
    }

    public function testPutAndGetWorksWithBooleanValue()
    {
        $this->cacheService->put('test_key', true, 60);

        $result = $this->cacheService->get('test_key');

        $this->assertTrue($result);
    }

    public function testPutAndGetWorksWithNullValue()
    {
        $this->cacheService->put('test_null_key', null, 60);

        $result = $this->cacheService->get('test_null_key');

        $this->assertNull($result);
    }

    public function testHasReturnsTrueForExistingKey()
    {
        $this->cacheService->put('test_key', 'value', 60);

        $exists = $this->cacheService->has('test_key');

        $this->assertTrue($exists);
    }

    public function testHasReturnsFalseForNonExistingKey()
    {
        $exists = $this->cacheService->has('non_existing_key');

        $this->assertFalse($exists);
    }

    public function testForgetRemovesKey()
    {
        $this->cacheService->put('test_key', 'value', 60);
        $this->assertTrue($this->cacheService->has('test_key'));

        $result = $this->cacheService->forget('test_key');

        $this->assertTrue($result);
        $this->assertFalse($this->cacheService->has('test_key'));
    }

    public function testForgetReturnsFalseForNonExistingKey()
    {
        $result = $this->cacheService->forget('non_existing_key');

        $this->assertFalse($result);
    }

    public function testGetWithFallbackExecutesCallbackOnMiss()
    {
        $executed = false;
        $callback = function () use (&$executed) {
            $executed = true;
            return 'computed_value';
        };

        $result = $this->cacheService->getWithFallback('test_callback_key', $callback, 60);

        $this->assertEquals('computed_value', $result);
        $this->assertTrue($executed);
    }

    public function testGetWithFallbackUsesCacheOnHit()
    {
        $this->cacheService->put('test_callback_key', 'cached_value', 60);

        $executed = false;
        $callback = function () use (&$executed) {
            $executed = true;
            return 'computed_value';
        };

        $result = $this->cacheService->getWithFallback('test_callback_key', $callback, 60);

        $this->assertEquals('cached_value', $result);
        $this->assertFalse($executed);
    }

    public function testGetWithFallbackCachesCallbackResult()
    {
        $callback = fn () => 'computed_value';

        $result1 = $this->cacheService->getWithFallback('test_callback_key', $callback, 60);
        $result2 = $this->cacheService->get('test_callback_key');

        $this->assertEquals('computed_value', $result1);
        $this->assertEquals('computed_value', $result2);
    }

    public function testGenerateKeyReplacesSinglePlaceholder()
    {
        $key = $this->cacheService->generateKey('user:{id}', ['id' => 123]);

        $this->assertEquals('user:123', $key);
    }

    public function testGenerateKeyReplacesMultiplePlaceholders()
    {
        $key = $this->cacheService->generateKey(
            'user:{id}:posts:{post_id}',
            ['id' => 123, 'post_id' => 456]
        );

        $this->assertEquals('user:123:posts:456', $key);
    }

    public function testGenerateKeyHandlesEmptyParams()
    {
        $key = $this->cacheService->generateKey('simple_key', []);

        $this->assertEquals('simple_key', $key);
    }

    public function testGenerateKeyConvertsParamsToString()
    {
        $key = $this->cacheService->generateKey('item:{id}', ['id' => 123.45]);

        $this->assertEquals('item:123.45', $key);
    }

    public function testGetPrefixReturnsCorrectPrefix()
    {
        $prefix = $this->cacheService->getPrefix();

        $this->assertEquals('hypervel_cache:', $prefix);
    }

    public function testGetStatsReturnsEnabledStatus()
    {
        $stats = $this->cacheService->getStats();

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('prefix', $stats);
        $this->assertArrayHasKey('enabled', $stats);
        $this->assertEquals('hypervel_cache:', $stats['prefix']);
        $this->assertTrue($stats['enabled']);
    }

    public function testGetReturnsNullForNonExistingKey()
    {
        $result = $this->cacheService->get('non_existing_key');

        $this->assertNull($result);
    }

    public function testPutOverwritesExistingValue()
    {
        $this->cacheService->put('test_key', 'old_value', 60);
        $this->assertEquals('old_value', $this->cacheService->get('test_key'));

        $this->cacheService->put('test_key', 'new_value', 60);

        $this->assertEquals('new_value', $this->cacheService->get('test_key'));
    }

    public function testMultipleKeysAreIndependent()
    {
        $this->cacheService->put('key1', 'value1', 60);
        $this->cacheService->put('key2', 'value2', 60);
        $this->cacheService->put('key3', 'value3', 60);

        $this->assertEquals('value1', $this->cacheService->get('key1'));
        $this->assertEquals('value2', $this->cacheService->get('key2'));
        $this->assertEquals('value3', $this->cacheService->get('key3'));
    }

    public function testForgetOneKeyDoesNotAffectOthers()
    {
        $this->cacheService->put('key1', 'value1', 60);
        $this->cacheService->put('key2', 'value2', 60);

        $this->cacheService->forget('key1');

        $this->assertFalse($this->cacheService->has('key1'));
        $this->assertTrue($this->cacheService->has('key2'));
        $this->assertEquals('value2', $this->cacheService->get('key2'));
    }

    public function testGetWithFallbackUsesProvidedTtl()
    {
        $callback = fn () => 'value';

        $result1 = $this->cacheService->getWithFallback('test_callback_key', $callback, 10);
        $this->assertEquals('value', $result1);

        $result2 = $this->cacheService->getWithFallback('test_callback_key', $callback, 60);
        $this->assertEquals('value', $result2);
    }

    public function testPutReturnsTrueOnSuccess()
    {
        $result = $this->cacheService->put('test_key', 'value', 60);

        $this->assertTrue($result);
    }

    public function testEmptyStringKeyIsHandled()
    {
        $this->cacheService->put('', 'value', 60);

        $result = $this->cacheService->get('');

        $this->assertEquals('value', $result);
    }

    public function testSpecialCharactersInKey()
    {
        $key = 'user:123@domain.com?query=test#fragment';
        $this->cacheService->put($key, 'value', 60);

        $result = $this->cacheService->get($key);

        $this->assertEquals('value', $result);
    }

    public function testUnicodeCharactersInKey()
    {
        $key = '用户:日本語:한국어';
        $this->cacheService->put($key, 'value', 60);

        $result = $this->cacheService->get($key);

        $this->assertEquals('value', $result);
    }

    public function testLargeArrayValue()
    {
        $largeArray = range(1, 1000);
        $this->cacheService->put('large_array', $largeArray, 60);

        $result = $this->cacheService->get('large_array');

        $this->assertEquals($largeArray, $result);
        $this->assertCount(1000, $result);
    }

    public function testNestedArrayValue()
    {
        $nested = [
            'user' => [
                'name' => 'John',
                'address' => [
                    'street' => '123 Main St',
                    'city' => 'New York',
                ],
            ],
            'tags' => ['tag1', 'tag2', 'tag3'],
        ];
        $this->cacheService->put('nested_array', $nested, 60);

        $result = $this->cacheService->get('nested_array');

        $this->assertEquals($nested, $result);
    }

    public function testObjectLikeArray()
    {
        $objectLike = [
            'id' => 1,
            'name' => 'Test',
            'active' => true,
            'metadata' => null,
        ];
        $this->cacheService->put('object_like', $objectLike, 60);

        $result = $this->cacheService->get('object_like');

        $this->assertEquals($objectLike, $result);
    }

    public function testGenerateKeyWithPartialParams()
    {
        $key = $this->cacheService->generateKey('prefix:{id}:suffix', ['id' => 999]);

        $this->assertEquals('prefix:999:suffix', $key);
    }

    public function testGenerateKeyPreservesLiteralBraces()
    {
        $key = $this->cacheService->generateKey('key:{{id}}', ['id' => 123]);

        $this->assertEquals('key:{123}', $key);
    }
}
