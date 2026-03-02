<?php
/**
 * CLI Script to Change Administrator Password
 * Usage: php change_admin_password.php <new_password>
 */

if (php_sapi_name() !== 'cli') {
    die("This script can only be run from the command line.\n");
}

if ($argc < 2) {
    die("Usage: php change_admin_password.php <new_password>\n");
}

require_once 'db_config.php';

$newPassword = $argv[1];
$username = 'admin';

try {
    $pdo = get_db_connection();
    
    // Check if admin exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND is_admin = 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if (!$user) {
        die("❌ Error: Administrator user 'admin' not found in database.\n");
    }
    
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    if ($stmt->execute([$hashedPassword, $user['id']])) {
        echo "✅ Password for user 'admin' updated successfully!\n";
    } else {
        echo "❌ Error updating password.\n";
    }
    
} catch (Exception $e) {
    die("❌ Error: " . $e->getMessage() . "\n");
}
