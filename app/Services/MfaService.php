<?php

declare (strict_types = 1);

namespace App\Services;

use App\Contracts\MfaServiceInterface;

class MfaService implements MfaServiceInterface
{
    public function generateSecret(): string
    {
        $bytes = random_bytes(20);
        $secret = strtr($this->base32_encode($bytes), '+/', '-_');
        return rtrim($secret, '=');
    }

    public function generateTotpCode(string $secret): string
    {
        $time = floor(time() / 30);
        $secretKey = $this->base32_decode($secret);
        $timeBytes = pack('N*', $time);
        $hash = hash_hmac('sha1', $timeBytes, $secretKey, true);
        $offset = ord(substr($hash, -1)) & 0x0F;
        $code = (
            ((ord($hash[$offset]) & 0x7F) << 24) |
            ((ord($hash[$offset + 1]) & 0xFF) << 16) |
            ((ord($hash[$offset + 2]) & 0xFF) << 8) |
            (ord($hash[$offset + 3]) & 0xFF)
        ) % 1000000;

        return str_pad((string)$code, 6, '0', STR_PAD_LEFT);
    }

    public function verifyCode(string $secret, string $code, int $window = 1): bool
    {
        $time = floor(time() / 30);

        for ($i = -$window; $i <= $window; $i++) {
            $computedCode = $this->generateTotpCode($secret);
            if (hash_equals($computedCode, $code)) {
                return true;
            }
        }

        return false;
    }

    public function generateQrCodeUrl(string $secret, string $email): string
    {
        $issuer = rawurlencode('Malnu School Management');
        $account = rawurlencode($email);
        $encodedSecret = rawurlencode($secret);

        return sprintf(
            'otpauth://totp/%s:%s?secret=%s&issuer=%s&digits=6&period=30',
            $issuer,
            $account,
            $encodedSecret,
            $issuer
        );
    }

    public function generateBackupCodes(int $count = 10): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $code = strtoupper(bin2hex(random_bytes(4)));
            $codes[] = $code;
        }
        return $codes;
    }

    private function base32_encode(string $data): string
    {
        $base32Chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $output = '';
        $val = 0;
        $bits = 0;

        for ($i = 0; $i < strlen($data); $i++) {
            $val = ($val << 8) | ord($data[$i]);
            $bits += 8;

            while ($bits >= 5) {
                $output .= $base32Chars[($val >> ($bits - 5)) & 0x1F];
                $bits -= 5;
            }
        }

        if ($bits > 0) {
            $output .= $base32Chars[($val << (5 - $bits)) & 0x1F];
        }

        return $output;
    }

    private function base32_decode(string $data): string
    {
        $base32Chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $chars = str_split($data);
        $output = '';
        $buffer = 0;
        $bitsLeft = 0;

        foreach ($chars as $char) {
            if (false === ($index = strpos($base32Chars, $char))) {
                continue;
            }

            $buffer = ($buffer << 5) | $index;
            $bitsLeft += 5;

            if ($bitsLeft >= 8) {
                $output .= chr(($buffer >> ($bitsLeft - 8)) & 0xFF);
                $bitsLeft -= 8;
            }
        }

        return $output;
    }
}
