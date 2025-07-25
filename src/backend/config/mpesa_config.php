<?php
// M-Pesa/Daraja API Configuration for DRMS
// Get these credentials from your Safaricom Developer Portal

// Load environment variable loader utility
require_once __DIR__ . '/env_loader.php';

// Load environment variables
loadEnv(__DIR__ . '/../../../.env');

// M-Pesa API Configuration from environment variables
define('MPESA_CONSUMER_KEY', $_ENV['MPESA_CONSUMER_KEY'] ?? 'your_consumer_key_here');
define('MPESA_CONSUMER_SECRET', $_ENV['MPESA_CONSUMER_SECRET'] ?? 'your_consumer_secret_here');
define('MPESA_SHORTCODE', $_ENV['MPESA_SHORTCODE'] ?? '174379');
define('MPESA_PASSKEY', $_ENV['MPESA_PASSKEY'] ?? 'your_passkey_here');
define('MPESA_CALLBACK_URL', $_ENV['MPESA_CALLBACK_URL'] ?? 'https://your-domain.com/callback');

// M-Pesa API URLs (Sandbox)
define('MPESA_TOKEN_URL', 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials');
define('MPESA_STK_URL', 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest');

// Production URLs (uncomment when going live)
// define('MPESA_TOKEN_URL', 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials');
// define('MPESA_STK_URL', 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest');

/**
 * Get M-Pesa access token
 * 
 * @return string|false Access token or false on failure
 */
function getMpesaAccessToken() {
    $credentials = base64_encode(MPESA_CONSUMER_KEY . ':' . MPESA_CONSUMER_SECRET);
    
    $ch = curl_init(MPESA_TOKEN_URL);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Basic $credentials"]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200 && $result) {
        $token = json_decode($result);
        return $token->access_token ?? false;
    }
    
    return false;
}

/**
 * Initiate STK Push payment
 * 
 * @param string $phone Phone number
 * @param int $amount Amount to charge
 * @param string $accountRef Account reference
 * @param string $description Transaction description
 * @return array Response array
 */
function initiateStkPush($phone, $amount, $accountRef, $description = 'DRMS Payment') {
    $accessToken = getMpesaAccessToken();
    
    if (!$accessToken) {
        return ['success' => false, 'error' => 'Failed to get access token'];
    }
    
    $timestamp = date('YmdHis');
    $password = base64_encode(MPESA_SHORTCODE . MPESA_PASSKEY . $timestamp);
    
    $payload = [
        'BusinessShortCode' => MPESA_SHORTCODE,
        'Password' => $password,
        'Timestamp' => $timestamp,
        'TransactionType' => 'CustomerPayBillOnline',
        'Amount' => (int)$amount,
        'PartyA' => $phone,
        'PartyB' => MPESA_SHORTCODE,
        'PhoneNumber' => $phone,
        'CallBackURL' => MPESA_CALLBACK_URL,
        'AccountReference' => $accountRef,
        'TransactionDesc' => $description
    ];
    
    $ch = curl_init(MPESA_STK_URL);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Bearer $accessToken"
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return ['success' => false, 'error' => 'cURL Error: ' . $error];
    }
    
    $result = json_decode($response, true);
    
    if ($httpCode === 200 && isset($result['ResponseCode']) && $result['ResponseCode'] == '0') {
        return [
            'success' => true,
            'message' => 'STK Push initiated successfully',
            'checkout_request_id' => $result['CheckoutRequestID'] ?? null
        ];
    } else {
        return [
            'success' => false,
            'error' => $result['errorMessage'] ?? 'Failed to initiate payment',
            'details' => $result
        ];
    }
}

/**
 * Check STK Push payment status
 * 
 * @param string $checkoutRequestId The checkout request ID from STK push
 * @return array Response array
 */
function checkStkPushStatus($checkoutRequestId) {
    $accessToken = getMpesaAccessToken();
    
    if (!$accessToken) {
        return ['success' => false, 'error' => 'Failed to get access token'];
    }
    
    $timestamp = date('YmdHis');
    $password = base64_encode(MPESA_SHORTCODE . MPESA_PASSKEY . $timestamp);
    
    $payload = [
        'BusinessShortCode' => MPESA_SHORTCODE,
        'Password' => $password,
        'Timestamp' => $timestamp,
        'CheckoutRequestID' => $checkoutRequestId
    ];
    
    // Use query URL for status check
    $queryUrl = 'https://sandbox.safaricom.co.ke/mpesa/stkpushquery/v1/query';
    // Production URL: https://api.safaricom.co.ke/mpesa/stkpushquery/v1/query
    
    $ch = curl_init($queryUrl);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Bearer $accessToken"
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return ['success' => false, 'error' => 'cURL Error: ' . $error];
    }
    
    $result = json_decode($response, true);
    
    if ($httpCode === 200 && isset($result['ResponseCode'])) {
        // Map M-Pesa response codes to our status
        $status = 'pending'; // default
        
        if ($result['ResponseCode'] == '0') {
            // Success - payment completed
            $status = 'completed';
            return [
                'success' => true,
                'status' => $status,
                'transaction_id' => $result['MpesaReceiptNumber'] ?? null,
                'amount' => $result['Amount'] ?? null,
                'phone' => $result['PhoneNumber'] ?? null
            ];
        } elseif ($result['ResponseCode'] == '1032') {
            // Request cancelled by user
            $status = 'cancelled';
        } elseif (in_array($result['ResponseCode'], ['1', '1001', '1019', '26', '2001'])) {
            // Various failure codes
            $status = 'failed';
        } elseif ($result['ResponseCode'] == '1037') {
            // Timeout/pending - user hasn't responded yet
            $status = 'pending';
        }
        
        return [
            'success' => true,
            'status' => $status,
            'error_message' => $result['ResultDesc'] ?? $result['errorMessage'] ?? null
        ];
    } else {
        return [
            'success' => false,
            'error' => $result['errorMessage'] ?? 'Failed to check payment status',
            'details' => $result
        ];
    }
}

/**
 * Validate M-Pesa phone number format
 */
function validateMpesaPhone($phone) {
    // Remove spaces, dashes, etc.
    $phone = preg_replace('/[\s\-\(\)]/', '', $phone);
    
    // Convert to international format
    if (str_starts_with($phone, '0')) {
        $phone = '254' . substr($phone, 1);
    }
    
    // Validate format (Kenyan numbers)
    if (preg_match('/^254[0-9]{9}$/', $phone)) {
        return $phone;
    }
    
    return false;
}
?>
