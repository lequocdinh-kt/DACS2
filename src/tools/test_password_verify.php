<?php
/**
 * Small debug helper (only enable when needed)
 * Usage: http://yourhost/src/tools/test_password_verify.php?email=you@example.com&password=yourpass&debug=1
 * IMPORTANT: Do NOT leave this file on production long-term.
 */

if (!isset($_GET['debug']) || $_GET['debug'] != '1') {
    die('Disabled');
}

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../models/database.php';
require_once __DIR__ . '/../models/user_db.php';

$email = $_GET['email'] ?? null;
$pw = $_GET['password'] ?? null;

header('Content-Type: text/plain');

if (!$email || !$pw) {
    echo "Usage: ?email=...&password=...&debug=1\n";
    exit;
}

$user = get_user_by_email($email);
if (!$user) {
    echo "User not found for email: $email\n";
    exit;
}

$hash = $user['password'] ?? null;
if (!$hash) {
    echo "No password hash stored for this user.\n";
    exit;
}

$ok = password_verify($pw, $hash) ? 'OK' : 'FAIL';

echo "Email: $email\n";
echo "Stored hash length: " . strlen($hash) . "\n";
echo "password_verify result: $ok\n";

// Optional: show hash prefix
echo "Hash prefix: " . substr($hash, 0, 4) . "\n";

?>