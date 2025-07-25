<?php
/**
 * Quick SMS Test - BlessedText Primary
 */

require_once 'src/backend/config/sms_gateway_manager.php';

echo "<h2>🚀 Quick SMS Gateway Test</h2>\n";
echo "<pre>\n";

echo "=== Configuration Status ===\n";
echo "Primary Gateway: " . SMS_PRIMARY_GATEWAY . "\n";
echo "Fallback Gateway: " . SMS_FALLBACK_GATEWAY . "\n";
echo "BlessedText API Key: " . (strlen(BLESSEDTEXT_API_KEY) > 10 ? '✅ Set' : '❌ Not set') . "\n";
echo "BlessedText Username: " . BLESSEDTEXT_USERNAME . "\n";
echo "BlessedText Sender ID: " . BLESSEDTEXT_SENDER_ID . "\n\n";

echo "=== Gateway Availability ===\n";
$gateways = getAvailableSMSGateways();
foreach ($gateways as $id => $gateway) {
    $status = $gateway['available'] && $gateway['config_valid'] ? '✅ Ready' : 
              ($gateway['available'] ? '⚠️ Config Issue' : '❌ Not Available');
    echo "$id: $status\n";
    if ($id === 'blessedtext') {
        echo "   - Available: " . ($gateway['available'] ? 'Yes' : 'No') . "\n";
        echo "   - Config Valid: " . ($gateway['config_valid'] ? 'Yes' : 'No') . "\n";
    }
}

echo "\n=== Quick Test (Change phone number) ===\n";
$testPhone = '+254712345678'; // Change this to your number
$testMessage = 'BlessedText Test from DRMS - ' . date('H:i:s');

echo "Testing SMS to: $testPhone\n";
echo "Message: $testMessage\n";
echo "Gateway: Auto (should use BlessedText primary)\n\n";

$result = sendSMSWithFallback($testPhone, $testMessage);

echo "=== Result ===\n";
echo "Success: " . ($result['success'] ? 'Yes' : 'No') . "\n";
echo "Gateway Used: " . ($result['gateway'] ?? 'Unknown') . "\n";
echo "Used Fallback: " . (($result['used_fallback'] ?? false) ? 'Yes' : 'No') . "\n";

if ($result['success']) {
    echo "Message ID: " . ($result['message_id'] ?? 'N/A') . "\n";
    echo "Cost: " . ($result['cost'] ?? 'N/A') . "\n";
    echo "Balance: " . ($result['balance'] ?? 'N/A') . "\n";
} else {
    echo "Error: " . ($result['error'] ?? 'Unknown') . "\n";
    if (isset($result['primary_error'])) {
        echo "Primary Error: " . $result['primary_error'] . "\n";
    }
    if (isset($result['fallback_error'])) {
        echo "Fallback Error: " . $result['fallback_error'] . "\n";
    }
}

echo "\n=== Test BlessedText Direct ===\n";
$directResult = sendSMSViaGateway($testPhone, $testMessage, 'blessedtext');
echo "Direct BlessedText Result:\n";
echo "Success: " . ($directResult['success'] ? 'Yes' : 'No') . "\n";
echo "Error: " . ($directResult['error'] ?? 'None') . "\n";

echo "</pre>\n";

echo "<p><strong>Notes:</strong></p>";
echo "<ul>";
echo "<li>If BlessedText shows 'Config Issue', check your API key is correct</li>";
echo "<li>If test fails, verify your BlessedText account has credits</li>";
echo "<li>Change the test phone number to your own number above</li>";
echo "<li>The system will now use BlessedText as primary, Africa's Talking as fallback</li>";
echo "</ul>";

echo "<p><a href='test_blessedtext_integration.php'>🧪 Full Test Page</a> | ";
echo "<a href='src/frontend/assets/sms_logs.php'>📋 SMS Logs</a> | ";
echo "<a href='src/frontend/assets/admin_send_sms.php'>📤 Send SMS</a></p>";
?>
