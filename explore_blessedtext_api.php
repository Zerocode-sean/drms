<?php
/**
 * BlessedText API Explorer - Try different endpoints and parameter formats
 */

require_once 'src/backend/config/env_loader.php';
loadEnv('.env');

$api_key = $_ENV['BLESSEDTEXT_API_KEY'] ?? '5c72f45976114ca3b869c70dfdd7145d';
$username = $_ENV['BLESSEDTEXT_USERNAME'] ?? 'johnmutua';
$sender_id = $_ENV['BLESSEDTEXT_SENDER_ID'] ?? 'FERRITE';

echo "<h2>🔍 BlessedText API Explorer</h2>\n";
echo "<pre>\n";

echo "=== Credentials ===\n";
echo "API Key: " . substr($api_key, 0, 8) . "...\n";
echo "Username: $username\n";
echo "Sender ID: $sender_id\n\n";

$test_phone = '254712345678';
$test_message = 'API test - ' . date('H:i:s');

// Try different endpoint variations
$endpoints = [
    [
        'url' => 'https://sms.blessedtexts.com/api/send',
        'description' => 'Simple API endpoint'
    ],
    [
        'url' => 'https://api.blessedtexts.com/sms/send',
        'description' => 'Alternative API subdomain'
    ],
    [
        'url' => 'https://sms.blessedtexts.com/api/v1/send',
        'description' => 'Versioned API'
    ],
    [
        'url' => 'https://blessedtexts.com/api/sms/send',
        'description' => 'Main domain API'
    ]
];

// Try different parameter formats
$param_formats = [
    [
        'name' => 'Standard Format',
        'params' => [
            'api_key' => $api_key,
            'username' => $username,
            'sender_id' => $sender_id,
            'recipients' => $test_phone,
            'message' => $test_message,
            'type' => 'plain'
        ]
    ],
    [
        'name' => 'Alternative Format 1',
        'params' => [
            'apikey' => $api_key,
            'user' => $username,
            'sender' => $sender_id,
            'phone' => $test_phone,
            'text' => $test_message
        ]
    ],
    [
        'name' => 'Alternative Format 2',
        'params' => [
            'key' => $api_key,
            'username' => $username,
            'from' => $sender_id,
            'to' => $test_phone,
            'message' => $test_message
        ]
    ]
];

foreach ($endpoints as $endpoint) {
    echo "=== Testing: {$endpoint['description']} ===\n";
    echo "URL: {$endpoint['url']}\n";
    
    foreach ($param_formats as $format) {
        echo "\n--- Parameter Format: {$format['name']} ---\n";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint['url']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($format['params']));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'DRMS-Explorer/1.0');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            echo "❌ cURL Error: $error\n";
        } else {
            echo "HTTP Code: $httpCode ";
            if ($httpCode == 200) {
                echo "✅ SUCCESS\n";
                echo "Response: " . substr($response, 0, 100) . "...\n";
                
                if ($response) {
                    $json = json_decode($response, true);
                    if ($json) {
                        echo "JSON: " . json_encode($json, JSON_PRETTY_PRINT) . "\n";
                        
                        // If this looks successful, note it
                        if (isset($json['status']) || isset($json['success']) || $httpCode == 200) {
                            echo "🎉 THIS LOOKS PROMISING!\n";
                        }
                    }
                }
                break 2; // Exit both loops if we found a working one
            } elseif ($httpCode == 404) {
                echo "❌ Not Found\n";
            } elseif ($httpCode == 400) {
                echo "⚠️ Bad Request (might need different parameters)\n";
                echo "Response: " . substr($response, 0, 100) . "...\n";
            } elseif ($httpCode == 401) {
                echo "🔑 Unauthorized (check credentials)\n";
            } else {
                echo "⚠️ Other error\n";
                echo "Response: " . substr($response, 0, 100) . "...\n";
            }
        }
    }
    echo "\n";
}

echo "</pre>\n";

echo "<h3>📞 Alternative: Use Twilio with Verified Number</h3>";
echo "<p>While we figure out BlessedText, you can:</p>";
echo "<ol>";
echo "<li>Go to <a href='https://console.twilio.com/us1/develop/phone-numbers/manage/verified' target='_blank'>Twilio Console</a></li>";
echo "<li>Verify your phone number (+254723394XXX)</li>";
echo "<li>Use Twilio as primary until BlessedText is working</li>";
echo "</ol>";

echo "<h3>🔧 Quick Fix Options</h3>";
echo "<button onclick='setTwilioPrimary()' style='padding: 10px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;'>Set Twilio as Primary</button>";

echo "<script>
function setTwilioPrimary() {
    if (confirm('Set Twilio as primary gateway while fixing BlessedText?')) {
        fetch('quick_gateway_switch.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({action: 'set_twilio_primary'})
        }).then(response => {
            if (response.ok) {
                alert('Switched to Twilio as primary gateway!');
                location.reload();
            }
        });
    }
}
</script>";
?>
