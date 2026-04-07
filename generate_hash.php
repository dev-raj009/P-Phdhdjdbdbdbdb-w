<?php
/**
 * ONE-TIME UTILITY — Admin Password Hash Generator
 * 
 * Run this ONCE from CLI to generate a new hash:
 *   php generate_hash.php yourNewPassword
 *
 * Then paste the output hash into config.php → ADMIN_PASSWORD_HASH
 * DELETE this file afterward!
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die("This script must be run from command line only.\nPlease delete this file after use.");
}

$pass = $argv[1] ?? null;
if (!$pass) {
    echo "Usage: php generate_hash.php <your_new_password>\n";
    exit(1);
}

$hash = password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12]);
echo "\n✅ Password hash generated:\n\n";
echo $hash . "\n\n";
echo "Paste this into config.php → ADMIN_PASSWORD_HASH constant.\n";
echo "⚠️  DELETE this file after use!\n\n";
