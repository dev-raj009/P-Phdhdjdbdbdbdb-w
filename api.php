<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

class PWAPI {

    // ── Standard headers (WEB client) ─────────────────────
    public static function headers(string $token = ''): array {
        $h = [
            'client-id: '      . PW_CLIENT_ID,
            'client-type: '    . PW_CLIENT_TYPE,
            'client-version: ' . PW_CLIENT_VERSION,
            'randomId: '       . PW_RANDOM_ID,
            'Accept: application/json, text/plain, */*',
        ];
        if ($token) $h[] = 'Authorization: Bearer ' . $token;
        return $h;
    }

    // ── MOBILE headers (for OTP) ───────────────────────────
    private static function mobileHeaders(): array {
        return [
            'client-id: '      . PW_CLIENT_ID,
            'client-version: 12.84',
            'Client-Type: MOBILE',
            'randomId: e4307177362e86f1',
            'Accept: application/json, text/plain, */*',
            'Content-Type: application/json',
        ];
    }

    // ── Generic cURL GET ──────────────────────────────────
    public static function get(string $url, array $headers): array {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $body = curl_exec($ch);
        curl_close($ch);
        return json_decode($body, true) ?? [];
    }

    // ── Generic cURL POST (JSON) ──────────────────────────
    public static function postJSON(string $url, array $headers, array $payload): array {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => array_merge($headers, ['Content-Type: application/json']),
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $body = curl_exec($ch);
        curl_close($ch);
        return json_decode($body, true) ?? [];
    }

    // ── Generic cURL POST (form-encoded) ─────────────────
    public static function postForm(string $url, array $headers, array $payload): array {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($payload),
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $body = curl_exec($ch);
        curl_close($ch);
        return json_decode($body, true) ?? [];
    }

    // ── Send OTP ──────────────────────────────────────────
    public static function sendOTP(string $mobile): array {
        return self::postJSON(
            PW_API_BASE . '/v1/users/get-otp?smsType=0',
            self::mobileHeaders(),
            [
                'username'       => $mobile,
                'countryCode'    => '+91',
                'organizationId' => PW_ORG_ID,
            ]
        );
    }

    // ── Verify OTP → get token ────────────────────────────
    public static function verifyOTP(string $mobile, string $otp): array {
        return self::postForm(
            PW_API_BASE . '/v3/oauth/token',
            self::mobileHeaders(),
            [
                'username'       => $mobile,
                'otp'            => $otp,
                'client_id'      => 'system-admin',
                'client_secret'  => PW_CLIENT_SECRET,
                'grant_type'     => 'password',
                'organizationId' => PW_ORG_ID,
                'latitude'       => 0,
                'longitude'      => 0,
            ]
        );
    }

    // ── Validate token by fetching profile ───────────────
    public static function getProfile(string $token): array {
        return self::get(
            PW_API_BASE . '/v1/users/profile',
            self::headers($token)
        );
    }

    // ── Fetch user's batches (courses) ────────────────────
    public static function getBatches(string $token): array {
        return self::get(
            PW_API_BASE . '/v3/batches/my-batches?mode=1&amount=paid&page=1',
            self::headers($token)
        );
    }

    // ── Fetch single batch details ────────────────────────
    public static function getBatchDetails(string $token, string $batchId): array {
        return self::get(
            PW_API_BASE . '/v3/batches/' . $batchId . '/details',
            self::headers($token)
        );
    }
}
