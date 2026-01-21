<?php

declare(strict_types=1);

namespace App\Contracts;

interface EmailServiceInterface
{
    /**
     * Send an email
     *
     * @param string $to Recipient email address
     * @param string $subject Email subject
     * @param string $body Email body content (HTML or plain text)
     * @return bool True if email sent successfully, false otherwise
     */
    public function send(string $to, string $subject, string $body): bool;
}
