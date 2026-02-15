<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserConsent;
use App\Services\Gdpr\DataExportService;
use App\Services\Gdpr\AccountDeletionService;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

class GdprController extends Controller
{
    private DataExportService $exportService;
    private AccountDeletionService $deletionService;

    public function __construct(ContainerInterface $container)
    {
        $this->exportService = new DataExportService($container);
        $this->deletionService = new AccountDeletionService($container);
    }

    /**
     * Get current user's consent status
     */
    public function getConsentStatus(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $user = $request->getAttribute('user');

        if (!$user) {
            return $response->json([
                'success' => false,
                'error' => ['code' => 'UNAUTHORIZED', 'message' => 'User not authenticated'],
            ])->withStatus(401);
        }

        $consents = UserConsent::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->unique('consent_type')
            ->values();

        return $response->json([
            'success' => true,
            'data' => [
                'user_id' => $user->id,
                'consents' => $consents,
                'required_consents' => [
                    UserConsent::CONSENT_TERMS_OF_SERVICE,
                    UserConsent::CONSENT_PRIVACY_POLICY,
                ],
            ],
        ]);
    }

    /**
     * Record user consent
     */
    public function recordConsent(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $user = $request->getAttribute('user');

        if (!$user) {
            return $response->json([
                'success' => false,
                'error' => ['code' => 'UNAUTHORIZED', 'message' => 'User not authenticated'],
            ])->withStatus(401);
        }

        $consentType = $request->input('consent_type');
        $consentGiven = $request->input('consent_given', false);
        $version = $request->input('version', '1.0');

        if (!in_array($consentType, UserConsent::ALL_CONSENT_TYPES, true)) {
            return $response->json([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_CONSENT_TYPE',
                    'message' => 'Invalid consent type provided',
                    'valid_types' => UserConsent::ALL_CONSENT_TYPES,
                ],
            ])->withStatus(400);
        }

        $ipAddress = $request->getHeaderLine('x-forwarded-for')
            ?? $request->getServerParams()['remote_addr']
            ?? null;
        $userAgent = $request->getHeaderLine('user-agent');

        $consent = UserConsent::record(
            $user->id,
            $consentType,
            $consentGiven,
            $version,
            $ipAddress,
            $userAgent,
            $request->input('metadata', [])
        );

        return $response->json([
            'success' => true,
            'message' => 'Consent recorded successfully',
            'data' => $consent,
        ]);
    }

    /**
     * Withdraw consent
     */
    public function withdrawConsent(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $user = $request->getAttribute('user');

        if (!$user) {
            return $response->json([
                'success' => false,
                'error' => ['code' => 'UNAUTHORIZED', 'message' => 'User not authenticated'],
            ])->withStatus(401);
        }

        $consentType = $request->input('consent_type');
        $reason = $request->input('reason', 'User request');

        $consent = UserConsent::where('user_id', $user->id)
            ->where('consent_type', $consentType)
            ->whereNull('withdrawn_at')
            ->latest()
            ->first();

        if (!$consent) {
            return $response->json([
                'success' => false,
                'error' => ['code' => 'CONSENT_NOT_FOUND', 'message' => 'Active consent not found'],
            ])->withStatus(404);
        }

        $consent->withdraw($reason);

        return $response->json([
            'success' => true,
            'message' => 'Consent withdrawn successfully',
        ]);
    }

    /**
     * Export user data (GDPR Article 20)
     */
    public function exportData(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $user = $request->getAttribute('user');

        if (!$user) {
            return $response->json([
                'success' => false,
                'error' => ['code' => 'UNAUTHORIZED', 'message' => 'User not authenticated'],
            ])->withStatus(401);
        }

        $format = $request->input('format', 'json');

        try {
            if ($format === 'json') {
                $data = $this->exportService->exportToJson($user);

                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withHeader('Content-Disposition', 'attachment; filename="gdpr-export-' . $user->id . '.json"')
                    ->withBody(new \Hyperf\Utils\Stream\SwooleStream($data));
            }

            if ($format === 'csv') {
                $data = $this->exportService->exportToCsv($user);

                return $response
                    ->withHeader('Content-Type', 'text/csv')
                    ->withHeader('Content-Disposition', 'attachment; filename="gdpr-export-' . $user->id . '.csv"')
                    ->withBody(new \Hyperf\Utils\Stream\SwooleStream($data));
            }

            return $response->json([
                'success' => false,
                'error' => ['code' => 'INVALID_FORMAT', 'message' => 'Invalid format. Use json or csv'],
            ])->withStatus(400);
        } catch (\Exception $e) {
            return $response->json([
                'success' => false,
                'error' => ['code' => 'EXPORT_FAILED', 'message' => 'Failed to export data: ' . $e->getMessage()],
            ])->withStatus(500);
        }
    }

    /**
     * Validate account deletion
     */
    public function validateDeletion(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $user = $request->getAttribute('user');

        if (!$user) {
            return $response->json([
                'success' => false,
                'error' => ['code' => 'UNAUTHORIZED', 'message' => 'User not authenticated'],
            ])->withStatus(401);
        }

        $validation = $this->deletionService->validateDeletion($user);

        return $response->json([
            'success' => true,
            'data' => $validation,
        ]);
    }

    /**
     * Delete/anonymize user account (GDPR Article 17)
     */
    public function deleteAccount(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $user = $request->getAttribute('user');

        if (!$user) {
            return $response->json([
                'success' => false,
                'error' => ['code' => 'UNAUTHORIZED', 'message' => 'User not authenticated'],
            ])->withStatus(401);
        }

        $reason = $request->input('reason', 'User request');
        $delayDays = (int) $request->input('delay_days', 0);

        try {
            if ($delayDays > 0) {
                $result = $this->deletionService->scheduleDeletion($user, $delayDays);

                return $response->json([
                    'success' => true,
                    'message' => 'Account deletion scheduled',
                    'data' => $result,
                ]);
            }

            $this->deletionService->anonymizeUser($user, $reason);

            return $response->json([
                'success' => true,
                'message' => 'Account has been anonymized successfully',
            ]);
        } catch (\Exception $e) {
            return $response->json([
                'success' => false,
                'error' => ['code' => 'DELETION_FAILED', 'message' => 'Failed to delete account: ' . $e->getMessage()],
            ])->withStatus(500);
        }
    }

    /**
     * Cancel scheduled account deletion
     */
    public function cancelDeletion(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $user = $request->getAttribute('user');

        if (!$user) {
            return $response->json([
                'success' => false,
                'error' => ['code' => 'UNAUTHORIZED', 'message' => 'User not authenticated'],
            ])->withStatus(401);
        }

        $result = $this->deletionService->cancelScheduledDeletion($user);

        if (!$result) {
            return $response->json([
                'success' => false,
                'error' => ['code' => 'NO_SCHEDULED_DELETION', 'message' => 'No scheduled deletion found'],
            ])->withStatus(400);
        }

        return $response->json([
            'success' => true,
            'message' => 'Scheduled deletion cancelled successfully',
        ]);
    }

    /**
     * Get privacy policy
     */
    public function getPrivacyPolicy(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $policy = [
            'version' => '1.0',
            'last_updated' => '2026-02-15',
            'sections' => [
                [
                    'title' => 'Data Controller',
                    'content' => 'Malnu School Management System acts as the data controller for all personal information collected.',
                ],
                [
                    'title' => 'Data We Collect',
                    'content' => 'We collect personal information including name, email, phone number, and academic records necessary for school management.',
                ],
                [
                    'title' => 'Your Rights',
                    'content' => 'Under GDPR, you have the right to access, rectify, erase, restrict processing, data portability, and object to processing.',
                ],
                [
                    'title' => 'Contact',
                    'content' => 'For data privacy inquiries, contact the data protection officer at privacy@malnu.school.',
                ],
            ],
        ];

        return $response->json([
            'success' => true,
            'data' => $policy,
        ]);
    }

    /**
     * Get terms of service
     */
    public function getTermsOfService(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $terms = [
            'version' => '1.0',
            'last_updated' => '2026-02-15',
            'sections' => [
                [
                    'title' => 'Acceptance of Terms',
                    'content' => 'By using the Malnu School Management System, you agree to these terms of service.',
                ],
                [
                    'title' => 'User Responsibilities',
                    'content' => 'Users are responsible for maintaining the confidentiality of their account credentials.',
                ],
                [
                    'title' => 'Data Usage',
                    'content' => 'User data is used solely for educational and administrative purposes within the school system.',
                ],
            ],
        ];

        return $response->json([
            'success' => true,
            'data' => $terms,
        ]);
    }
}
