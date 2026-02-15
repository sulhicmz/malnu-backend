<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Hypervel\Http\Request;
use Hypervel\Http\Response as HttpResponse;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MobileMiddleware implements MiddlewareInterface
{
    protected ContainerInterface $container;
    protected RequestInterface $request;
    protected HttpResponse $response;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->request = $container->get(RequestInterface::class);
        $this->response = $container->get(HttpResponse::class);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $userAgent = $request->getHeaderLine('User-Agent');
        
        $isMobile = $this->isMobileRequest($userAgent);
        
        $request = $request->withAttribute('is_mobile', $isMobile);
        $request = $request->withAttribute('device_info', $this->getDeviceInfo($userAgent));

        return $handler->handle($request);
    }

    private function isMobileRequest(string $userAgent): bool
    {
        $mobileAgents = [
            'android', 'iphone', 'ipad', 'ipod', 'blackberry',
            'windows phone', 'mobile', 'webos', 'opera mini',
        ];

        foreach ($mobileAgents as $agent) {
            if (stripos($userAgent, $agent) !== false) {
                return true;
            }
        }

        return false;
    }

    private function getDeviceInfo(string $userAgent): array
    {
        $deviceInfo = [
            'type' => 'unknown',
            'os' => 'unknown',
            'browser' => 'unknown',
        ];

        $userAgentLower = strtolower($userAgent);

        if (strpos($userAgentLower, 'android') !== false) {
            $deviceInfo['os'] = 'Android';
            $deviceInfo['type'] = 'mobile';
        } elseif (strpos($userAgentLower, 'iphone') !== false) {
            $deviceInfo['os'] = 'iOS';
            $deviceInfo['type'] = 'mobile';
        } elseif (strpos($userAgentLower, 'ipad') !== false) {
            $deviceInfo['os'] = 'iOS';
            $deviceInfo['type'] = 'tablet';
        } elseif (strpos($userAgentLower, 'ipod') !== false) {
            $deviceInfo['os'] = 'iOS';
            $deviceInfo['type'] = 'mobile';
        } elseif (strpos($userAgentLower, 'windows phone') !== false) {
            $deviceInfo['os'] = 'Windows Phone';
            $deviceInfo['type'] = 'mobile';
        }

        if (strpos($userAgentLower, 'chrome') !== false) {
            $deviceInfo['browser'] = 'Chrome';
        } elseif (strpos($userAgentLower, 'safari') !== false) {
            $deviceInfo['browser'] = 'Safari';
        } elseif (strpos($userAgentLower, 'firefox') !== false) {
            $deviceInfo['browser'] = 'Firefox';
        }

        return $deviceInfo;
    }
}
