<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/api.php';

header('Content-Type: application/json');

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input  = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $input['action'] ?? '';

switch ($action) {

    // ── 1. Token login ────────────────────────────────────
    case 'token_login':
        $token = trim($input['token'] ?? '');
        if (!$token) {
            echo json_encode(['success' => false, 'message' => 'Token required']);
            exit;
        }
        // Validate via profile API
        $profileRes = PWAPI::getProfile($token);
        if (!($profileRes['success'] ?? false)) {
            echo json_encode(['success' => false, 'message' => 'Invalid or expired token']);
            exit;
        }
        $prof = $profileRes['data'] ?? [];
        $uid  = DB::upsertUser([
            'name'         => ($prof['firstName'] ?? '') . ' ' . ($prof['lastName'] ?? ''),
            'mobile'       => $prof['username']     ?? null,
            'email'        => $prof['email']        ?? null,
            'access_token' => $token,
            'refresh_token'=> null,
            'token_expiry' => null,
            'org_id'       => PW_ORG_ID,
        ]);

        // Store in session
        ini_set('session.name', SESSION_NAME);
        session_start();
        $_SESSION['user_id'] = $uid;
        $_SESSION['token']   = $token;

        echo json_encode(['success' => true, 'redirect' => 'dashboard.php']);
        break;

    // ── 2. Send OTP ───────────────────────────────────────
    case 'send_otp':
        $mobile = preg_replace('/\D/', '', $input['mobile'] ?? '');
        if (strlen($mobile) !== 10) {
            echo json_encode(['success' => false, 'message' => 'Enter valid 10-digit mobile number']);
            exit;
        }
        $res = PWAPI::sendOTP($mobile);
        if ($res['success'] ?? false) {
            // Store mobile temporarily in session
            ini_set('session.name', SESSION_NAME);
            session_start();
            $_SESSION['otp_mobile'] = $mobile;
            echo json_encode(['success' => true, 'message' => 'OTP sent successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => $res['message'] ?? 'Failed to send OTP']);
        }
        break;

    // ── 3. Verify OTP ─────────────────────────────────────
    case 'verify_otp':
        ini_set('session.name', SESSION_NAME);
        session_start();
        $mobile = $_SESSION['otp_mobile'] ?? '';
        $otp    = trim($input['otp'] ?? '');
        if (!$mobile || !$otp) {
            echo json_encode(['success' => false, 'message' => 'Session expired, please retry']);
            exit;
        }
        $res = PWAPI::verifyOTP($mobile, $otp);
        $token = $res['data']['access_token'] ?? null;
        if (!$token) {
            echo json_encode(['success' => false, 'message' => 'Invalid OTP. Please try again.']);
            exit;
        }
        $refresh = $res['data']['refresh_token'] ?? null;
        $expiry  = isset($res['data']['expires_in']) ? time() + (int)$res['data']['expires_in'] : null;

        // Get profile
        $profileRes = PWAPI::getProfile($token);
        $prof       = $profileRes['data'] ?? [];
        $uid        = DB::upsertUser([
            'name'          => ($prof['firstName'] ?? '') . ' ' . ($prof['lastName'] ?? ''),
            'mobile'        => $mobile,
            'email'         => $prof['email'] ?? null,
            'access_token'  => $token,
            'refresh_token' => $refresh,
            'token_expiry'  => $expiry,
            'org_id'        => PW_ORG_ID,
        ]);

        $_SESSION['user_id'] = $uid;
        $_SESSION['token']   = $token;
        unset($_SESSION['otp_mobile']);

        echo json_encode(['success' => true, 'redirect' => 'dashboard.php']);
        break;

    // ── 4. Logout ─────────────────────────────────────────
    case 'logout':
        ini_set('session.name', SESSION_NAME);
        session_start();
        session_destroy();
        echo json_encode(['success' => true, 'redirect' => 'index.php']);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action']);
}
