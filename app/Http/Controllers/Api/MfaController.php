<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Contracts\AuthServiceInterface;
use App\Contracts\MfaServiceInterface;
use App\Traits\InputValidationTrait;

class MfaController extends BaseController
{
    use InputValidationTrait;

    private MfaServiceInterface $mfaService;

    private AuthServiceInterface $authService;

    public function __construct(MfaServiceInterface $mfaService, AuthServiceInterface $authService)
    {
        parent::__construct();
        $this->mfaService = $mfaService;
        $this->authService = $authService;
    }

    public function setup()
    {
        try {
            $user = $this->getAuthenticatedUser();
            if (! $user) {
                return $this->unauthorizedResponse('User not authenticated');
            }

            $data = $this->request->all();
            $type = $data['type'] ?? 'totp';

            $setupData = $this->mfaService->setupMfa($user['id'], $type);

            $qrCodeSvg = $this->mfaService->generateQrCodeSvg(
                'Malnu School Management',
                $user['email'],
                $setupData['secret']
            );

            return $this->successResponse([
                'secret' => $setupData['secret'],
                'qr_code_svg' => $qrCodeSvg,
                'type' => $type,
            ], 'MFA setup initiated. Verify with code to enable.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'MFA_SETUP_ERROR', null, 500);
        }
    }

    public function enable()
    {
        try {
            $user = $this->getAuthenticatedUser();
            if (! $user) {
                return $this->unauthorizedResponse('User not authenticated');
            }

            $data = $this->request->all();

            $errors = $this->validateRequired($data, ['code']);
            if (! empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $success = $this->mfaService->enableMfa($user['id'], $data['code']);

            if (! $success) {
                return $this->errorResponse('Invalid verification code', 'INVALID_MFA_CODE', null, 400);
            }

            $backupCodes = $this->mfaService->generateBackupCodes($user['id']);

            return $this->successResponse([
                'backup_codes' => $backupCodes,
                'message' => 'MFA enabled successfully. Save these backup codes in a secure location.',
            ], 'MFA enabled successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'MFA_ENABLE_ERROR', null, 500);
        }
    }

    public function disable()
    {
        try {
            $user = $this->getAuthenticatedUser();
            if (! $user) {
                return $this->unauthorizedResponse('User not authenticated');
            }

            $data = $this->request->all();

            $errors = $this->validateRequired($data, ['code']);
            if (! empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $verified = $this->mfaService->verifyMfa(
                $user['id'],
                $data['code'],
                $this->request->getServerParams()['REMOTE_ADDR'] ?? null,
                $this->request->getHeaderLine('User-Agent')
            );

            if (! $verified) {
                return $this->errorResponse('Invalid MFA code', 'INVALID_MFA_CODE', null, 400);
            }

            $this->mfaService->disableMfa($user['id']);

            return $this->successResponse(null, 'MFA disabled successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'MFA_DISABLE_ERROR', null, 500);
        }
    }

    public function status()
    {
        try {
            $user = $this->getAuthenticatedUser();
            if (! $user) {
                return $this->unauthorizedResponse('User not authenticated');
            }

            $status = $this->mfaService->getMfaStatus($user['id']);

            return $this->successResponse($status, 'MFA status retrieved');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'MFA_STATUS_ERROR', null, 500);
        }
    }

    public function regenerateBackupCodes()
    {
        try {
            $user = $this->getAuthenticatedUser();
            if (! $user) {
                return $this->unauthorizedResponse('User not authenticated');
            }

            $data = $this->request->all();

            $errors = $this->validateRequired($data, ['code']);
            if (! empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $verified = $this->mfaService->verifyMfa(
                $user['id'],
                $data['code'],
                $this->request->getServerParams()['REMOTE_ADDR'] ?? null,
                $this->request->getHeaderLine('User-Agent')
            );

            if (! $verified) {
                return $this->errorResponse('Invalid MFA code', 'INVALID_MFA_CODE', null, 400);
            }

            $backupCodes = $this->mfaService->regenerateBackupCodes($user['id']);

            return $this->successResponse([
                'backup_codes' => $backupCodes,
                'message' => 'New backup codes generated. Save these in a secure location.',
            ], 'Backup codes regenerated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'BACKUP_CODES_ERROR', null, 500);
        }
    }

    public function verify()
    {
        try {
            $data = $this->request->all();
            $data = $this->sanitizeInput($data);

            $errors = $this->validateRequired($data, ['email', 'code']);
            if (! empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            if (! $this->validateEmail($data['email'])) {
                return $this->validationErrorResponse(['email' => ['Invalid email format']]);
            }

            $user = $this->authService->getUserFromToken($this->request->getHeaderLine('Authorization'));
            if (! $user) {
                return $this->unauthorizedResponse('Invalid or expired session');
            }

            $verified = $this->mfaService->verifyMfa(
                $user['id'],
                $data['code'],
                $this->request->getServerParams()['REMOTE_ADDR'] ?? null,
                $this->request->getHeaderLine('User-Agent')
            );

            if (! $verified) {
                return $this->errorResponse('Invalid MFA code', 'INVALID_MFA_CODE', null, 400);
            }

            return $this->successResponse([
                'verified' => true,
            ], 'MFA verification successful');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'MFA_VERIFY_ERROR', null, 500);
        }
    }

    private function getAuthenticatedUser(): ?array
    {
        $authHeader = $this->request->getHeaderLine('Authorization');
        if (! $authHeader || ! str_starts_with($authHeader, 'Bearer ')) {
            return null;
        }

        $token = substr($authHeader, 7);
        return $this->authService->getUserFromToken($token);
    }
}
