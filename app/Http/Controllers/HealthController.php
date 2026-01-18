<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controllers\Api\BaseController;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Redis\Redis;
use Psr\Container\ContainerInterface;

class HealthController extends BaseController
{
    #[Inject]
    private Redis $redis;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct(
            $container->get(\Hyperf\HttpServer\Contract\RequestInterface::class),
            $container->get(\Hyperf\HttpServer\Contract\ResponseInterface::class),
            $container
        );
    }

    public function index()
    {
        $status = 'ok';
        $checks = [];

        $checks['database'] = $this->checkDatabase();
        $checks['redis'] = $this->checkRedis();

        if (in_array(false, array_column($checks, 'status'))) {
            $status = 'degraded';
        }

        return $this->response->json([
            'status' => $status,
            'timestamp' => date('Y-m-d\TH:i:s\Z'),
            'checks' => array_map(fn($check) => $check['status'], $checks),
        ]);
    }

    public function detailed()
    {
        $status = 'ok';
        $checks = [];

        $checks['database'] = $this->checkDatabase();
        $checks['redis'] = $this->checkRedis();
        $checks['system'] = $this->checkSystemResources();

        if (in_array(false, array_column($checks, 'status'))) {
            $status = 'degraded';
        }

        return $this->response->json([
            'status' => $status,
            'timestamp' => date('Y-m-d\TH:i:s\Z'),
            'checks' => $checks,
        ]);
    }

    private function checkDatabase(): array
    {
        $startTime = microtime(true);
        $status = true;
        $message = 'Connection successful';
        $responseTime = 0;

        try {
            Db::select('SELECT 1');
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);
        } catch (\Exception $e) {
            $status = false;
            $message = 'Connection failed: ' . $e->getMessage();
        }

        return [
            'status' => $status,
            'message' => $message,
            'response_time_ms' => $responseTime,
        ];
    }

    private function checkRedis(): array
    {
        $startTime = microtime(true);
        $status = true;
        $message = 'Connection successful';
        $responseTime = 0;

        try {
            $this->redis->ping();
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);
        } catch (\Exception $e) {
            $status = false;
            $message = 'Connection failed: ' . $e->getMessage();
        }

        return [
            'status' => $status,
            'message' => $message,
            'response_time_ms' => $responseTime,
        ];
    }

    private function checkSystemResources(): array
    {
        $status = true;
        $message = 'Resources within normal limits';
        $data = [];

        $memoryUsage = memory_get_usage(true);
        $memoryLimit = ini_get('memory_limit');
        $data['memory_usage_bytes'] = $memoryUsage;
        $data['memory_limit'] = $memoryLimit;
        $data['memory_usage_mb'] = round($memoryUsage / 1024 / 1024, 2);

        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            $data['cpu_load_1min'] = $load[0] ?? 0;
            $data['cpu_load_5min'] = $load[1] ?? 0;
            $data['cpu_load_15min'] = $load[2] ?? 0;

            if (($load[0] ?? 0) > 10) {
                $status = false;
                $message = 'High CPU load detected';
            }
        }

        return [
            'status' => $status,
            'message' => $message,
            'data' => $data,
        ];
    }
}