<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\CacheService;
use Hyperf\Context\ApplicationContext;
use Hyperf\DbConnection\Db;
use Hyperf\Testing\Client;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class CachePerformanceTest extends TestCase
{
    private Client $client;

    private CacheService $cache;

    protected function setUp(): void
    {
        parent::setUp();
        $container = ApplicationContext::getContainer();
        $this->client = $container->get(Client::class);
        $this->cache = new CacheService($container);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->cache->flush();
    }

    public function testCacheServiceIsConfigured()
    {
        $this->assertNotNull($this->cache);
        $this->assertInstanceOf(CacheService::class, $this->cache);
    }

    public function testCacheCanStoreAndRetrieveData()
    {
        $key = 'test_key';
        $value = ['test' => 'data'];
        $ttl = CacheService::TTL_SHORT;

        $this->cache->set($key, $value, $ttl);
        $retrieved = $this->cache->get($key);

        $this->assertEquals($value, $retrieved);
        $this->cache->forget($key);
    }

    public function testCacheMetricsAreAccessible()
    {
        $metrics = $this->cache->getMetrics();

        $this->assertIsArray($metrics);
        $this->assertArrayHasKey('total_keys', $metrics);
        $this->assertArrayHasKey('hit_rate', $metrics);
    }

    public function testApiResponseCachingWorks()
    {
        $this->cache->flush();

        $response1 = $this->client->get('/api/v1/students');

        $this->assertEquals(200, $response1->getStatusCode());

        $cacheKey = 'api_response:' . md5('/api/v1/students:');
        $cachedData = $this->cache->get($cacheKey);

        $this->assertNotNull($cachedData, 'API response should be cached');
    }

    public function testCachedResponsesAreFaster()
    {
        $this->cache->flush();

        $iterations = 10;
        $uncachedTimes = [];
        $cachedTimes = [];

        for ($i = 0; $i < $iterations; ++$i) {
            $start = microtime(true);
            $this->client->get('/api/v1/students');
            $uncachedTimes[] = microtime(true) - $start;

            $start = microtime(true);
            $this->client->get('/api/v1/students');
            $cachedTimes[] = microtime(true) - $start;
        }

        $avgUncached = array_sum($uncachedTimes) / count($uncachedTimes) * 1000;
        $avgCached = array_sum($cachedTimes) / count($cachedTimes) * 1000;

        $this->assertLessThan(
            $avgUncached,
            $avgCached,
            'Cached responses should be faster than uncached responses'
        );

        $this->assertLessThan(
            200,
            $avgCached,
            'Cached response time should be <200ms (actual: ' . round($avgCached, 2) . 'ms)'
        );
    }

    public function testQueryCachingImprovesPerformance()
    {
        $this->cache->flush();

        $cacheKey = 'students:index::::1:15';

        $startTime = microtime(true);
        $data = $this->cache->remember($cacheKey, CacheService::TTL_SHORT, function () {
            return Db::table('students')
                ->select('students.*', 'classes.name as class_name')
                ->leftJoin('classes', 'students.class_id', '=', 'classes.id')
                ->limit(15)
                ->get();
        });
        $firstQueryTime = (microtime(true) - $startTime) * 1000;

        $startTime = microtime(true);
        $data2 = $this->cache->remember($cacheKey, CacheService::TTL_SHORT, function () {
            return Db::table('students')
                ->select('students.*', 'classes.name as class_name')
                ->leftJoin('classes', 'students.student_class', '=', 'classes.id')
                ->limit(15)
                ->get();
        });
        $secondQueryTime = (microtime(true) - $startTime) * 1000;

        $this->assertEquals($data, $data2, 'Cached data should match original data');
        $this->assertLessThan($firstQueryTime, $secondQueryTime, 'Cached query should be faster');
        $this->assertLessThan(50, $secondQueryTime, 'Cached query time should be <50ms');
    }

    public function testCacheInvalidationWorks()
    {
        $cacheKey = 'test_invalidation_key';
        $value = ['data' => 'test'];

        $this->cache->set($cacheKey, $value, CacheService::TTL_SHORT);
        $this->assertNotNull($this->cache->get($cacheKey));

        $this->cache->forget($cacheKey);
        $this->assertNull($this->cache->get($cacheKey));
    }

    public function testPatternBasedCacheInvalidation()
    {
        $keys = [
            'students:index::::1:15',
            'students:index::::2:15',
            'students:index::::1:20',
        ];

        foreach ($keys as $key) {
            $this->cache->set($key, ['data' => $key], CacheService::TTL_SHORT);
        }

        $this->cache->forgetByPattern('students:index:*');

        foreach ($keys as $key) {
            $this->assertNull($this->cache->get($key), "Key {$key} should be invalidated");
        }
    }

    public function testCacheHitRateImprovesOverTime()
    {
        $this->cache->flush();

        $iterations = 20;

        for ($i = 0; $i < $iterations; ++$i) {
            $this->client->get('/api/v1/students');
        }

        $metrics = $this->cache->getMetrics();

        $this->assertGreaterThan(0, $metrics['keyspace_hits'], 'Cache hits should be > 0');
        $this->assertGreaterThan(50, $metrics['hit_rate'], 'Cache hit rate should be >50%');
    }
}
