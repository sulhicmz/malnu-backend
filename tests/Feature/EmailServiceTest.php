<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\EmailService;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;
use Swift_Mailer;
use Swift_Message;
use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class EmailServiceTest extends TestCase
{
    private EmailService $emailService;

    private MockObject|Swift_Mailer $mockMailer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockMailer = $this->createMock(Swift_Mailer::class);
        $this->emailService = new EmailService();

        $this->setProperty($this->emailService, 'mailer', $this->mockMailer);
    }

    public function testSendPasswordResetEmailSuccessfully()
    {
        $token = 'test-reset-token-12345';
        $email = 'user@example.com';

        $this->mockMailer->expects($this->once())
            ->method('send')
            ->willReturn(1);

        $result = $this->emailService->sendPasswordResetEmail($email, $token);

        $this->assertTrue($result);
    }

    public function testSendPasswordResetEmailWithSendFailureReturnsFalse()
    {
        $token = 'test-reset-token-12345';
        $email = 'user@example.com';

        $this->mockMailer->expects($this->once())
            ->method('send')
            ->willThrowException(new Exception('SMTP connection failed'));

        $result = $this->emailService->sendPasswordResetEmail($email, $token);

        $this->assertFalse($result);
    }

    public function testSendPasswordResetEmailGeneratesCorrectLink()
    {
        $token = 'test-token-abc123';
        $email = 'user@example.com';

        $capturedMessage = null;
        $this->mockMailer->expects($this->once())
            ->method('send')
            ->with($this->callback(function (Swift_Message $message) use (&$capturedMessage) {
                $capturedMessage = $message;
                return true;
            }))
            ->willReturn(1);

        $this->emailService->sendPasswordResetEmail($email, $token);

        $this->assertNotNull($capturedMessage);
        $body = $capturedMessage->getBody();
        $this->assertStringContainsString('token=' . $token, $body);
        $this->assertStringContainsString('reset-password', $body);
    }

    public function testSendPasswordResetEmailIncludesAppName()
    {
        $token = 'test-token-xyz';
        $email = 'user@example.com';

        putenv('MAIL_FROM_NAME=TestSchool');

        $capturedMessage = null;
        $this->mockMailer->expects($this->once())
            ->method('send')
            ->with($this->callback(function (Swift_Message $message) use (&$capturedMessage) {
                $capturedMessage = $message;
                return true;
            }))
            ->willReturn(1);

        $this->emailService->sendPasswordResetEmail($email, $token);

        $subject = $capturedMessage->getSubject();
        $body = $capturedMessage->getBody();
        $this->assertStringContainsString('TestSchool', $subject);
        $this->assertStringContainsString('TestSchool', $body);
    }

    public function testSendPasswordResetEmailSetsCorrectRecipient()
    {
        $token = 'test-token-recipient';
        $email = 'recipient@example.com';

        $capturedMessage = null;
        $this->mockMailer->expects($this->once())
            ->method('send')
            ->with($this->callback(function (Swift_Message $message) use (&$capturedMessage) {
                $capturedMessage = $message;
                return true;
            }))
            ->willReturn(1);

        $this->emailService->sendPasswordResetEmail($email, $token);

        $this->assertNotNull($capturedMessage);
        $to = $capturedMessage->getTo();
        $this->assertArrayHasKey($email, $to);
        $this->assertEquals([$email => null], $to);
    }

    public function testSendPasswordResetEmailUsesHtmlFormat()
    {
        $token = 'test-token-html';
        $email = 'html@example.com';

        $capturedMessage = null;
        $this->mockMailer->expects($this->once())
            ->method('send')
            ->with($this->callback(function (Swift_Message $message) use (&$capturedMessage) {
                $capturedMessage = $message;
                return true;
            }))
            ->willReturn(1);

        $this->emailService->sendPasswordResetEmail($email, $token);

        $this->assertNotNull($capturedMessage);
        $body = $capturedMessage->getBody();
        $this->assertStringContainsString('<!DOCTYPE html>', $body);
        $this->assertStringContainsString('<html>', $body);
        $this->assertStringContainsString('</html>', $body);
    }

    public function testSendPasswordResetEmailContainsRequiredElements()
    {
        $token = 'test-token-elements';
        $email = 'elements@example.com';

        $capturedMessage = null;
        $this->mockMailer->expects($this->once())
            ->method('send')
            ->with($this->callback(function (Swift_Message $message) use (&$capturedMessage) {
                $capturedMessage = $message;
                return true;
            }))
            ->willReturn(1);

        $this->emailService->sendPasswordResetEmail($email, $token);

        $this->assertNotNull($capturedMessage);
        $body = $capturedMessage->getBody();
        $this->assertStringContainsString('Password Reset', $body);
        $this->assertStringContainsString('Reset Password', $body);
        $this->assertStringContainsString('1 hour', $body);
        $this->assertStringContainsString('copyright', strtolower($body));
    }

    public function testSendPasswordResetEmailHandlesEmptyToken()
    {
        $token = '';
        $email = 'empty@example.com';

        $this->mockMailer->expects($this->once())
            ->method('send')
            ->willReturn(1);

        $result = $this->emailService->sendPasswordResetEmail($email, $token);

        $this->assertTrue($result);
    }

    public function testSendPasswordResetEmailHandlesSpecialCharactersInToken()
    {
        $token = 'test-token-!@#$%^&*()_+-={}[]|\:";\'<>?,./';
        $email = 'special@example.com';

        $this->mockMailer->expects($this->once())
            ->method('send')
            ->willReturn(1);

        $result = $this->emailService->sendPasswordResetEmail($email, $token);

        $this->assertTrue($result);
    }

    public function testSendPasswordResetEmailHandlesLongToken()
    {
        $token = str_repeat('a', 256);
        $email = 'long@example.com';

        $this->mockMailer->expects($this->once())
            ->method('send')
            ->willReturn(1);

        $result = $this->emailService->sendPasswordResetEmail($email, $token);

        $this->assertTrue($result);
    }

    public function testSendPasswordResetEmailUsesCustomFrontendUrl()
    {
        putenv('FRONTEND_URL=https://custom-frontend.example.com');
        $token = 'test-token-frontend';
        $email = 'frontend@example.com';

        $capturedMessage = null;
        $this->mockMailer->expects($this->once())
            ->method('send')
            ->with($this->callback(function (Swift_Message $message) use (&$capturedMessage) {
                $capturedMessage = $message;
                return true;
            }))
            ->willReturn(1);

        $this->emailService->sendPasswordResetEmail($email, $token);

        $this->assertNotNull($capturedMessage);
        $body = $capturedMessage->getBody();
        $this->assertStringContainsString('https://custom-frontend.example.com', $body);
    }

    public function testSendPasswordResetEmailDefaultsToFrontendUrl()
    {
        putenv('FRONTEND_URL=');
        $token = 'test-token-default';
        $email = 'default@example.com';

        $capturedMessage = null;
        $this->mockMailer->expects($this->once())
            ->method('send')
            ->with($this->callback(function (Swift_Message $message) use (&$capturedMessage) {
                $capturedMessage = $message;
                return true;
            }))
            ->willReturn(1);

        $this->emailService->sendPasswordResetEmail($email, $token);

        $this->assertNotNull($capturedMessage);
        $body = $capturedMessage->getBody();
        $this->assertStringContainsString('http://localhost:3000', $body);
    }

    private function setProperty(object $object, string $property, mixed $value): void
    {
        $reflection = new ReflectionClass($object);
        $property = $reflection->getProperty($property);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }
}
