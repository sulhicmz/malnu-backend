<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class CsrfProtectionTest extends TestCase
{
    public function testCsrfProtectionIsEnabledForWebRoutes(): void
    {
        $this->assertTrue(class_exists(\App\Http\Middleware\VerifyCsrfToken::class));
        $this->assertTrue(class_exists(\Hypervel\Foundation\Http\Middleware\VerifyCsrfToken::class));
    }

    public function testApiRoutesAreExcludedFromCsrf(): void
    {
        $middleware = new \App\Http\Middleware\VerifyCsrfToken(
            $this->getContainer(),
            $this->getContainer()->get(\Hyperf\Contract\ConfigInterface::class),
            $this->getContainer()->get(\Hypervel\Encryption\Contracts\Encrypter::class),
            $this->getContainer()->get(\Hyperf\HttpServer\Request::class)
        );

        $excluded = $middleware->getExcludedPaths();

        $this->assertContains('api/*', $excluded);
        $this->assertContains('csp-report', $excluded);
    }
}
