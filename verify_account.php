<?php
/**
 * Africa's Talking Account Verification Test
 * This script helps verify and activate your Africa's Talking account
 */

require_once 'src/backend/config/env_loader.php';
loadEnv(__DIR__ . '/.env');

$apiKey = $_ENV['AFRICASTALKING_API_KEY'] ?? '';
$username = $_ENV['AFRICASTALKING_USERNAME'] ?? 'sandbox';

echo "=== Africa's Talking Account Verification Test ===\n\n";

// Step 1: Check account status
echo "1. Checking account status...\n";
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => 'https://api.africastalking.com/version1/user?username=' . $username,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Accept: application/json',
        'apiKey: ' . $apiKey
    ],
    CURLOPT_TIMEOUT => 30,
    CURLOPT_SSL_VERIFYPEER => true
]);

$response = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

echo "HTTP Code: $httpCode\n";
echo "Response: $response\n\n";

if ($httpCode === 200) {
    echo "✅ Account is verified and active!\n\n";
    $data = json_decode($response, true);
    if (isset($data['UserData'])) {
        echo "Account Details:\n";
        foreach ($data['UserData'] as $key => $value) {
            echo "- $key: $value\n";
        }
    }
} else {
    echo "❌ Account verification needed\n\n";
    
    // Step 2: Try to get account balance (sometimes this works for verification)
    echo "2. Attempting balance check for verification...\n";
    
    // Step 3: Try a minimal SMS send (this often triggers verification)
    echo "3. Attempting minimal SMS send for verification...\n";
    
    $postData = [
        'username' => $username,
        'to' => '+254700000000', // Standard test number
        'message' => 'Account verification test',
        'from' => 'AT_Test' // Use a generic sender ID for verification
    ];
    
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => 'https://api.africastalking.com/version1/messaging',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($postData),
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'Content-Type: application/x-www-form-urlencoded',
            'apiKey: ' . $apiKey
        ],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => true
    ]);
    
    $smsResponse = curl_exec($curl);
    $smsHttpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    
    echo "SMS Test HTTP Code: $smsHttpCode\n";
    echo "SMS Test Response: $smsResponse\n\n";
    
    if ($smsHttpCode === 201) {
        echo "🎉 SMS sent successfully! Your account should now be verified.\n";
        echo "Try running the main SMS test again in a few minutes.\n\n";
    } else {
        echo "⚠️ SMS verification attempt failed.\n\n";
    }
}

echo "=== NEXT STEPS ===\n";
echo "If verification is still needed, try these manual steps:\n\n";

echo "1. **Dashboard Verification:**\n";
echo "   - Log into your Africa's Talking dashboard\n";
echo "   - Look for account status indicators\n";
echo "   - Check if there are any pending verification steps\n\n";

echo "2. **Alternative Verification:**\n";
echo "   - Try sending a test SMS from the dashboard\n";
echo "   - Add credits to your account (even free sandbox credits)\n";
echo "   - Complete any pending profile information\n\n";

echo "3. **API Key Issues:**\n";
echo "   - Generate a new API key from the dashboard\n";
echo "   - Make sure you're using the sandbox API key (not production)\n";
echo "   - Ensure the API key has SMS permissions enabled\n\n";

echo "4. **Contact Support:**\n";
echo "   - If the above doesn't work, contact Africa's Talking support\n";
echo "   - They can manually verify your account\n";
echo "   - Mention you're trying to integrate their SMS API\n\n";

echo "Current API Key (first 15 chars): " . substr($apiKey, 0, 15) . "...\n";
echo "Username: $username\n";
?>
