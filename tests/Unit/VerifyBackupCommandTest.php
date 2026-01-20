<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Console\Commands\VerifyBackupCommand;
use Hyperf\Contract\ConfigInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionClass;

/**
 * @internal
 * @coversNothing
 */
class VerifyBackupCommandTest extends TestCase
{
    private ContainerInterface $container;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = $this->createMock(ContainerInterface::class);
        $config = $this->createMock(ConfigInterface::class);
        $this->container->method('get')->with(ConfigInterface::class)->willReturn($config);
    }

    public function testVerifyChecksumsUsesSha256Only()
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'backup_test_');
        file_put_contents($tempFile, 'test content');

        $command = new VerifyBackupCommand($this->container);

        $reflection = new ReflectionClass($command);
        $method = $reflection->getMethod('verifyChecksums');
        $method->setAccessible(true);

        $result = $method->invoke($command, $tempFile);

        $this->assertArrayHasKey('sha256', $result['details']);
        $this->assertArrayNotHasKey('md5', $result['details']);
        $this->assertIsString($result['details']['sha256']);
        $this->assertEquals(64, strlen($result['details']['sha256']));
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $result['details']['sha256']);

        unlink($tempFile);
        if (file_exists($tempFile . '.checksum')) {
            unlink($tempFile . '.checksum');
        }
    }

    public function testSaveChecksumsOnlySavesSha256()
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'backup_test_');
        file_put_contents($tempFile, 'test content');

        $command = new VerifyBackupCommand($this->container);

        $reflection = new ReflectionClass($command);
        $method = $reflection->getMethod('saveChecksums');
        $method->setAccessible(true);

        $sha256 = hash('sha256', 'test content');
        $method->invoke($command, $tempFile, $sha256);

        $this->assertFileExists($tempFile . '.checksum');
        $checksumData = json_decode(file_get_contents($tempFile . '.checksum'), true);

        $this->assertArrayHasKey('sha256', $checksumData);
        $this->assertArrayNotHasKey('md5', $checksumData);
        $this->assertEquals($sha256, $checksumData['sha256']);
        $this->assertArrayHasKey('backup_file', $checksumData);
        $this->assertArrayHasKey('timestamp', $checksumData);

        unlink($tempFile);
        unlink($tempFile . '.checksum');
    }

    public function testVerifyChecksumsVerifiesAgainstStoredSha256()
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'backup_test_');
        file_put_contents($tempFile, 'test content');

        $command = new VerifyBackupCommand($this->container);

        $reflection = new ReflectionClass($command);
        $saveMethod = $reflection->getMethod('saveChecksums');
        $saveMethod->setAccessible(true);

        $sha256 = hash('sha256', 'test content');
        $saveMethod->invoke($command, $tempFile, $sha256);

        $verifyMethod = $reflection->getMethod('verifyChecksums');
        $verifyMethod->setAccessible(true);

        $result = $verifyMethod->invoke($command, $tempFile);

        $this->assertTrue($result['passed']);
        $this->assertStringContainsString('Checksum verification passed', $result['message']);

        unlink($tempFile);
        unlink($tempFile . '.checksum');
    }

    public function testVerifyChecksumsDetectsTamperedFile()
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'backup_test_');
        file_put_contents($tempFile, 'original content');

        $command = new VerifyBackupCommand($this->container);

        $reflection = new ReflectionClass($command);
        $saveMethod = $reflection->getMethod('saveChecksums');
        $saveMethod->setAccessible(true);

        $sha256 = hash('sha256', 'original content');
        $saveMethod->invoke($command, $tempFile, $sha256);

        file_put_contents($tempFile, 'tampered content');

        $verifyMethod = $reflection->getMethod('verifyChecksums');
        $verifyMethod->setAccessible(true);

        $result = $verifyMethod->invoke($command, $tempFile);

        $this->assertFalse($result['passed']);
        $this->assertStringContainsString('Checksum verification failed', $result['message']);

        unlink($tempFile);
        unlink($tempFile . '.checksum');
    }

    public function testSha256HashFormatIsValid()
    {
        $testString = 'Test backup content for SHA-256 verification';
        $sha256 = hash('sha256', $testString);

        $this->assertIsString($sha256);
        $this->assertEquals(64, strlen($sha256));
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $sha256);

        $expectedSha256 = 'b5d4045c3f466fa91fe2cc6abe79232a1a57cdf104f7a26e716e0a1e2789df78';
        $this->assertEquals($expectedSha256, $sha256);
    }
}
