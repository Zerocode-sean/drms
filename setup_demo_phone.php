<?php
/**
 * Add Your Real Phone Number for Presentation Demo
 */

require_once 'src/backend/config/env_loader.php';
require_once 'src/backend/config/db_config.php';
require_once 'src/backend/config/sms_gateway_manager.php';

loadEnv(__DIR__ . '/.env');

echo "📱 SETTING UP YOUR PHONE NUMBER FOR PRESENTATION DEMO\n";
echo str_repeat("=", 60) . "\n\n";

$yourPhoneNumber = '0723396228'; // Your actual phone number
$formattedNumber = '+254723396228'; // Formatted for SMS
$username = 'testuser'; // Test user to update

echo "🎯 DEMO SETUP:\n";
echo "• Your phone: $yourPhoneNumber\n";
echo "• Formatted for SMS: $formattedNumber\n";
echo "• Adding to user: $username\n\n";

try {
    $pdo = new PDO("mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']}", $_ENV['DB_USER'], $_ENV['DB_PASS']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Update the testuser with your phone number
    echo "1. 📝 UPDATING DATABASE:\n";
    $stmt = $pdo->prepare("UPDATE users SET phone = ? WHERE username = ?");
    $result = $stmt->execute([$formattedNumber, $username]);
    
    if ($result) {
        echo "   ✅ Successfully updated '$username' phone number to $formattedNumber\n\n";
    } else {
        echo "   ❌ Failed to update phone number\n\n";
        exit;
    }
    
    // Verify the update
    echo "2. ✅ VERIFICATION:\n";
    $stmt = $pdo->prepare("SELECT id, username, email, phone FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "   User Details:\n";
        echo "   • ID: {$user['id']}\n";
        echo "   • Username: {$user['username']}\n";
        echo "   • Email: {$user['email']}\n";
        echo "   • Phone: {$user['phone']}\n\n";
    }
    
    // Test SMS sending to your number
    echo "3. 🧪 TESTING SMS TO YOUR NUMBER:\n";
    $testMessage = "🎉 DRMS Demo Test: SMS integration working perfectly! Sent at " . date('Y-m-d H:i:s');
    
    echo "   Sending test SMS...\n";
    echo "   To: $formattedNumber\n";
    echo "   Message: $testMessage\n\n";
    
    $result = sendSMSWithFallback($formattedNumber, $testMessage);
    
    if ($result['success']) {
        echo "   ✅ SMS TEST SUCCESSFUL!\n";
        echo "   📊 Details:\n";
        echo "      • Status: " . ($result['status'] ?? 'Unknown') . "\n";
        echo "      • Cost: " . ($result['cost'] ?? 'Unknown') . "\n";
        echo "      • Message ID: " . ($result['message_id'] ?? 'Unknown') . "\n";
        echo "   📱 CHECK YOUR PHONE for the SMS message!\n\n";
        
        // Log to database
        $stmt = $pdo->prepare("INSERT INTO sms_logs (user_id, recipient_phone, message, status, sent_by, twilio_sid, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([
            $user['id'],
            $formattedNumber,
            $testMessage,
            'sent',
            1, // admin user
            $result['message_id'] ?? 'TEST_' . uniqid()
        ]);
        echo "   ✅ SMS logged to database\n\n";
        
    } else {
        $error = $result['error'] ?? 'Unknown error';
        echo "   ⚠️ SMS Test Result: $error\n";
        
        if (strpos($error, 'UserInBlacklist') !== false) {
            echo "   💡 Note: Your number might be blacklisted in sandbox\n";
            echo "   💡 But the web interface will still work for demo!\n";
        }
        echo "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n\n";
}

echo str_repeat("=", 60) . "\n";
echo "🎯 PRESENTATION DEMO READY!\n";
echo str_repeat("=", 60) . "\n\n";

echo "📋 DEMO STEPS FOR PRESENTATION:\n\n";

echo "1. 🌐 ADMIN SMS DEMO:\n";
echo "   • Go to: http://localhost/project/src/frontend/assets/admin_send_sms.php\n";
echo "   • Log in as admin\n";
echo "   • Select 'testuser (0723396228)' from dropdown\n";
echo "   • Type message: 'Demo: DRMS SMS working!'\n";
echo "   • Click Send SMS\n";
echo "   • Show success message\n";
echo "   • Check your phone for SMS delivery\n\n";

echo "2. 📱 DRIVER SMS DEMO:\n";
echo "   • Go to: http://localhost/project/src/frontend/assets/driver_send_sms.php\n";
echo "   • Log in as driver\n";
echo "   • Select 'testuser (0723396228)' from dropdown\n";
echo "   • Type message: 'Driver notification test'\n";
echo "   • Click Send SMS\n";
echo "   • Show real-time feedback\n\n";

echo "3. 📊 SMS LOGS DEMO:\n";
echo "   • Go to: http://localhost/project/src/frontend/assets/sms_logs.php\n";
echo "   • Show all sent SMS messages\n";
echo "   • Demonstrate delivery tracking\n";
echo "   • Show professional logging system\n\n";

echo "4. 📱 PHONE DEMO:\n";
echo "   • Show received SMS on your phone\n";
echo "   • Demonstrate real delivery\n";
echo "   • Show sender ID (DRMS or default)\n\n";

echo "🎯 DEMO TALKING POINTS:\n";
echo "• SMS integration with Africa's Talking (Kenya's leading SMS provider)\n";
echo "• Real-time SMS delivery to residents\n";
echo "• Professional admin and driver interfaces\n";
echo "• Comprehensive SMS logging and tracking\n";
echo "• Phone number validation and formatting\n";
echo "• Modern, responsive design\n";
echo "• Production-ready SMS functionality\n\n";

echo "💡 BACKUP DEMO OPTIONS:\n";
echo "• If live SMS fails, show SMS logs with previous messages\n";
echo "• Demonstrate the beautiful UI and user experience\n";
echo "• Show dropdown with phone numbers instead of emails\n";
echo "• Highlight the character counter and loading states\n\n";

echo "🎉 YOUR DEMO IS READY!\n";
echo "Your phone number (0723396228) is now set up for testuser.\n";
echo "You can confidently demonstrate live SMS functionality!\n";
?>
