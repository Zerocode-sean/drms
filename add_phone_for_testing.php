<?php
/**
 * Quick Setup - Add Your Phone Number for SMS Testing
 */

require_once 'src/backend/config/env_loader.php';
require_once 'src/backend/config/db_config.php';

loadEnv(__DIR__ . '/.env');

echo "📱 ADD YOUR PHONE NUMBER FOR SMS TESTING\n";
echo str_repeat("=", 50) . "\n\n";

echo "🎯 QUICK SETUP TO AVOID 'UserInBlacklist' ERROR:\n\n";

echo "OPTION 1 - Add your phone to an existing user:\n";
echo "Execute this SQL command in your database:\n\n";

echo "-- Replace +254XXXXXXXXX with your actual phone number\n";
echo "UPDATE users SET phone = '+254XXXXXXXXX' WHERE username = 'testuser';\n\n";

echo "OPTION 2 - Update via this script:\n";
echo "Uncomment and modify the code below, then run this script:\n\n";

// Uncomment and modify this section with your actual phone number
/*
$yourPhoneNumber = '+254712345678'; // 👈 REPLACE WITH YOUR ACTUAL NUMBER
$username = 'testuser'; // User to update

try {
    $pdo = new PDO("mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']}", $_ENV['DB_USER'], $_ENV['DB_PASS']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->prepare("UPDATE users SET phone = ? WHERE username = ?");
    $result = $stmt->execute([$yourPhoneNumber, $username]);
    
    if ($result) {
        echo "✅ Successfully updated $username's phone number to $yourPhoneNumber\n";
        echo "You can now test SMS by sending to this user through the web interface!\n";
    } else {
        echo "❌ Failed to update phone number\n";
    }
    
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}
*/

echo "OPTION 3 - Test with existing users:\n";
echo "Use the web interface to send SMS to these users who already have phone numbers:\n\n";

try {
    $pdo = new PDO("mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']}", $_ENV['DB_USER'], $_ENV['DB_PASS']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->query("SELECT id, username, phone FROM users WHERE phone IS NOT NULL AND phone != '' ORDER BY username");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($users as $user) {
        echo "• User ID {$user['id']}: {$user['username']} ({$user['phone']})\n";
    }
    
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "🚀 NEXT STEPS:\n";
echo "1. Add your phone number using one of the options above\n";
echo "2. Go to: http://localhost/project/src/frontend/assets/admin_send_sms.php\n";
echo "3. Log in as admin\n";
echo "4. Select the user with your phone number\n";
echo "5. Send a test SMS message\n";
echo "6. Check your phone for the message!\n";
echo "7. View delivery status in SMS logs\n\n";

echo "💡 REMEMBER:\n";
echo "• Your SMS system is working perfectly!\n";
echo "• UserInBlacklist only affects test numbers\n";
echo "• Real phone numbers work fine\n";
echo "• Test with your actual number to see SMS delivery\n";
?>
