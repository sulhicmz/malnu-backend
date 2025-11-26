<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\NotificationService;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Job\Job;

class SendNotificationJob extends Job
{
    public array $params;

    #[Inject]
    protected NotificationService $notificationService;

    #[Inject]
    protected StdoutLoggerInterface $logger;

    public function __construct(array $params)
    {
        $this->params = $params;
    }

    public function handle()
    {
        try {
            $this->notificationService->sendNotification(
                $this->params['title'],
                $this->params['content'],
                $this->params['user_ids'],
                $this->params['type'] ?? 'general',
                $this->params['priority'] ?? 'medium',
                $this->params['channels'] ?? ['email'],
                $this->params['data'] ?? []
            );
            
            $this->logger->info('Notification job completed successfully');
        } catch (\Exception $e) {
            $this->logger->error('Notification job failed: ' . $e->getMessage());
            throw $e;
        }
    }
}