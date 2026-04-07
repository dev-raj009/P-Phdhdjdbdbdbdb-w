<?php
require_once __DIR__ . '/config.php';

class DB {
    private static ?PDO $pdo = null;

    public static function get(): PDO {
        if (self::$pdo === null) {
            $dir = dirname(DB_PATH);
            if (!is_dir($dir)) mkdir($dir, 0750, true);

            self::$pdo = new PDO('sqlite:' . DB_PATH);
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            self::init();
        }
        return self::$pdo;
    }

    private static function init(): void {
        self::$pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id            INTEGER PRIMARY KEY AUTOINCREMENT,
                name          TEXT,
                mobile        TEXT,
                email         TEXT,
                access_token  TEXT,
                refresh_token TEXT,
                token_expiry  INTEGER,
                org_id        TEXT,
                created_at    INTEGER DEFAULT (strftime('%s','now')),
                last_login    INTEGER DEFAULT (strftime('%s','now'))
            );

            CREATE TABLE IF NOT EXISTS admin_sessions (
                id         INTEGER PRIMARY KEY AUTOINCREMENT,
                token      TEXT UNIQUE,
                created_at INTEGER DEFAULT (strftime('%s','now')),
                expires_at INTEGER
            );
        ");
    }

    // ── User helpers ───────────────────────────────────────

    public static function upsertUser(array $d): int {
        $db = self::get();

        // Try find by mobile or access_token
        $stmt = $db->prepare("SELECT id FROM users WHERE mobile=? OR access_token=?");
        $stmt->execute([$d['mobile'] ?? null, $d['access_token'] ?? null]);
        $row = $stmt->fetch();

        if ($row) {
            $db->prepare("UPDATE users SET
                name=COALESCE(?,name),
                mobile=COALESCE(?,mobile),
                email=COALESCE(?,email),
                access_token=COALESCE(?,access_token),
                refresh_token=COALESCE(?,refresh_token),
                token_expiry=COALESCE(?,token_expiry),
                org_id=COALESCE(?,org_id),
                last_login=strftime('%s','now')
                WHERE id=?")
            ->execute([
                $d['name']          ?? null,
                $d['mobile']        ?? null,
                $d['email']         ?? null,
                $d['access_token']  ?? null,
                $d['refresh_token'] ?? null,
                $d['token_expiry']  ?? null,
                $d['org_id']        ?? null,
                $row['id']
            ]);
            return (int)$row['id'];
        }

        $db->prepare("INSERT INTO users
            (name,mobile,email,access_token,refresh_token,token_expiry,org_id)
            VALUES (?,?,?,?,?,?,?)")
        ->execute([
            $d['name']          ?? null,
            $d['mobile']        ?? null,
            $d['email']         ?? null,
            $d['access_token']  ?? null,
            $d['refresh_token'] ?? null,
            $d['token_expiry']  ?? null,
            $d['org_id']        ?? null,
        ]);
        return (int)$db->lastInsertId();
    }

    public static function allUsers(): array {
        return self::get()->query("SELECT * FROM users ORDER BY last_login DESC")->fetchAll();
    }

    public static function getUserByToken(string $token): ?array {
        $stmt = self::get()->prepare("SELECT * FROM users WHERE access_token=?");
        $stmt->execute([$token]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    // ── Admin session helpers ──────────────────────────────

    public static function createAdminSession(): string {
        $token = bin2hex(random_bytes(32));
        $exp   = time() + SESSION_LIFETIME;
        self::get()->prepare("INSERT INTO admin_sessions (token,expires_at) VALUES (?,?)")
            ->execute([$token, $exp]);
        // cleanup old sessions
        self::get()->exec("DELETE FROM admin_sessions WHERE expires_at < " . time());
        return $token;
    }

    public static function validateAdminSession(string $token): bool {
        $stmt = self::get()->prepare("SELECT id FROM admin_sessions WHERE token=? AND expires_at>?");
        $stmt->execute([$token, time()]);
        return (bool)$stmt->fetch();
    }

    public static function deleteAdminSession(string $token): void {
        self::get()->prepare("DELETE FROM admin_sessions WHERE token=?")->execute([$token]);
    }
}
