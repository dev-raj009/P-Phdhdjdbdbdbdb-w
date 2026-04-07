<?php
// ============================================================
//  PW Portal - Secure Configuration
//  DO NOT expose this file publicly
// ============================================================

define('PW_API_BASE',       'https://api.penpencil.co');
define('PW_CLIENT_ID',      '5eb393ee95fab7468a79d189');
define('PW_ORG_ID',         '5eb393ee95fab7468a79d189');
define('PW_CLIENT_SECRET',  'KjPXuAVfC5xbmgreETNMaL7z');
define('PW_CLIENT_VERSION', '3.3.0');
define('PW_CLIENT_TYPE',    'WEB');
define('PW_RANDOM_ID',      '04b54cdb-bf9e-48ef-974d-620e21bd3e23');

// ── Admin Credentials (stored as bcrypt hash) ──────────────
// Default: admin / Admin@PW2025  → Change after first login!
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD_HASH', '$2y$12$Qv8NkSdFzXt3mB5YwR7uPOKQ9lLZ0c8nHxJv1iYmAp6tRb4sEqWO2');
// ^ bcrypt of "Admin@PW2025"

// ── Session / Security ─────────────────────────────────────
define('SESSION_NAME',    'pw_portal_sess');
define('SESSION_LIFETIME', 3600 * 8); // 8 hours
define('CSRF_TOKEN_NAME', '_csrf');

// ── Database (SQLite – no extra setup needed) ──────────────
define('DB_PATH', __DIR__ . '/data/portal.db');

// ── Timezone ───────────────────────────────────────────────
date_default_timezone_set('Asia/Kolkata');
