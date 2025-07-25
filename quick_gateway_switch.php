<?php
/**
 * Quick Gateway Switch - Temporarily switch to Twilio
 */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (isset($input['action']) && $input['action'] === 'set_twilio_primary') {
        $envFile = '.env';
        $envContent = file_get_contents($envFile);
        
        // Update gateway settings
        $envContent = preg_replace('/^SMS_PRIMARY_GATEWAY=.*/m', 'SMS_PRIMARY_GATEWAY=twilio', $envContent);
        $envContent = preg_replace('/^SMS_FALLBACK_GATEWAY=.*/m', 'SMS_FALLBACK_GATEWAY=blessedtext', $envContent);
        
        if (file_put_contents($envFile, $envContent)) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update config']);
        }
        exit;
    }
}

// Show current status
require_once 'src/backend/config/env_loader.php';
loadEnv('.env');

echo "<h2>⚡ Quick Gateway Manager</h2>\n";
echo "<pre>\n";

echo "=== Current Configuration ===\n";
echo "Primary Gateway: " . ($_ENV['SMS_PRIMARY_GATEWAY'] ?? 'Not set') . "\n";
echo "Fallback Gateway: " . ($_ENV['SMS_FALLBACK_GATEWAY'] ?? 'Not set') . "\n\n";

echo "=== Issues Detected ===\n";
echo "❌ BlessedText: 404 Error (wrong endpoint)\n";
echo "⚠️ Twilio: Trial account needs verified numbers\n\n";

echo "=== Recommendations ===\n";
echo "1. Verify your phone number in Twilio Console\n";
echo "2. Temporarily use Twilio as primary\n";
echo "3. Fix BlessedText endpoint in background\n";

echo "</pre>\n";

echo "<h3>🔧 Quick Actions</h3>";
echo "<button onclick='switchToTwilio()' style='padding: 10px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; margin: 5px;'>Use Twilio as Primary</button>";
echo "<button onclick='switchToBlessedText()' style='padding: 10px; background: #17a2b8; color: white; border: none; border-radius: 5px; cursor: pointer; margin: 5px;'>Use BlessedText as Primary</button>";

echo "<h3>📱 Test Links</h3>";
echo "<ul>";
echo "<li><a href='explore_blessedtext_api.php'>🔍 Explore BlessedText API</a></li>";
echo "<li><a href='test_simplified_sms.php'>📱 Test SMS System</a></li>";
echo "<li><a href='https://console.twilio.com/us1/develop/phone-numbers/manage/verified' target='_blank'>🔗 Verify Twilio Number</a></li>";
echo "</ul>";

echo "<script>
function switchToTwilio() {
    if (confirm('Switch to Twilio as primary gateway?\\n\\nNote: You need to verify your phone number in Twilio first.')) {
        updateGateway('twilio', 'blessedtext');
    }
}

function switchToBlessedText() {
    if (confirm('Switch to BlessedText as primary gateway?\\n\\nNote: BlessedText needs correct API endpoint first.')) {
        updateGateway('blessedtext', 'twilio');
    }
}

function updateGateway(primary, fallback) {
    fetch('quick_gateway_switch.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            action: 'update_gateways',
            primary: primary,
            fallback: fallback
        })
    }).then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Gateway configuration updated!');
            location.reload();
        } else {
            alert('Failed to update: ' + (data.error || 'Unknown error'));
        }
    }).catch(err => {
        alert('Error: ' + err.message);
    });
}
</script>";

// Handle gateway updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (isset($input['action']) && $input['action'] === 'update_gateways') {
        $primary = $input['primary'] ?? '';
        $fallback = $input['fallback'] ?? '';
        
        $envFile = '.env';
        $envContent = file_get_contents($envFile);
        
        $envContent = preg_replace('/^SMS_PRIMARY_GATEWAY=.*/m', "SMS_PRIMARY_GATEWAY=$primary", $envContent);
        $envContent = preg_replace('/^SMS_FALLBACK_GATEWAY=.*/m', "SMS_FALLBACK_GATEWAY=$fallback", $envContent);
        
        if (file_put_contents($envFile, $envContent)) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update config']);
        }
        exit;
    }
}
?>
