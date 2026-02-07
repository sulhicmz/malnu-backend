<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\EmailService;
use Tests\TestCase;

/**
 * @internal
 * @covers \App\Services\EmailService
 */
class EmailServiceTest extends TestCase
{
    private EmailService $emailService;

    private $mockMailer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->emailService = new EmailService();
        $this->mockMailer = $this->createMock('Swift_Mailer');
    }

    public function testSendPasswordResetEmail(): void
    {
        $email = 'user@example.com';
        $token = 'reset_token_12345';

        $this->mockMailer->expects($this->once())
            ->method('send')
            ->with(
                $this->callback(function ($subject) {
                    return strpos($subject, 'Password Reset') !== false;
                }),
                $email,
                $this->callback(function ($body) {
                    return strpos($body, $token) !== false;
                })
            )
            ->willReturn(1);

        $result = $this->emailService->sendPasswordResetEmail($email, $token);

        $this->assertTrue($result);
    }

    public function testSendNotificationEmail(): void
    {
        $email = 'user@example.com';
        $subject = 'Test Notification';
        $body = 'This is a notification';

        $this->mockMailer->expects($this->once())
            ->method('send')
            ->with(
                $subject,
                $email,
                $this->callback(function ($actualBody) use ($body) {
                    return strpos($actualBody, $body) !== false;
                })
            )
            ->willReturn(1);

        $result = $this->emailService->sendNotificationEmail($email, $subject, $body);

        $this->assertTrue($result);
    }

    public function testSendEmailSuccessfullyReturnsTrue(): void
    {
        $email = 'success@example.com';

        $this->mockMailer->expects($this->once())
            ->method('send')
            ->willReturn(1);

        $result = $this->emailService->sendPasswordResetEmail($email, 'token');

        $this->assertTrue($result);
    }

    public function testSendEmailFailureReturnsFalse(): void
    {
        $email = 'failure@example.com';

        $this->mockMailer->expects($this->once())
            ->method('send')
            ->willReturn(0);

        $result = $this->emailService->sendPasswordResetEmail($email, 'token');

        $this->assertFalse($result);
    }

    public function testSendEmailWithMultipleRecipients(): void
    {
        $emails = ['user1@example.com', 'user2@example.com', 'user3@example.com'];
        $subject = 'Group Notification';

        $expectedSendCount = 0;

        $this->mockMailer->expects($this->exactly(3))
            ->method('send')
            ->willReturnCallback(function () use (&$expectedSendCount) {
                $expectedSendCount++;
                return 1;
            });

        foreach ($emails as $email) {
            $this->emailService->sendNotificationEmail($email, $subject, 'Message');
        }

        $this->assertEquals(3, $expectedSendCount);
    }

    public function testSendEmailWithHtmlContent(): void
    {
        $email = 'html@example.com';
        $htmlBody = '<h1>HTML Email</h1><p>This is <strong>formatted</strong></p>';

        $this->mockMailer->expects($this->once())
            ->method('send')
            ->with($this->callback(function ($body) use ($htmlBody) {
                return strpos($body, $htmlBody) !== false;
            }))
            ->willReturn(1);

        $result = $this->emailService->sendNotificationEmail($email, 'HTML Email', $htmlBody);

        $this->assertTrue($result);
    }

    public function testSendEmailWithPlainTextContent(): void
    {
        $email = 'plain@example.com';
        $plainBody = 'This is plain text email content';

        $this->mockMailer->expects($this->once())
            ->method('send')
            ->with($plainBody)
            ->willReturn(1);

        $result = $this->emailService->sendNotificationEmail($email, 'Plain Email', $plainBody);

        $this->assertTrue($result);
    }
}
