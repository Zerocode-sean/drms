<?php
// BlessedText SMS Configuration for DRMS
// Get these credentials from your BlessedText account

// Load environment variable loader utility
require_once __DIR__ . '/env_loader.php';

// Load environment variables
loadEnv(__DIR__ . '/../../../.env');

// BlessedText Account Credentials from environment variables
define('BLESSEDTEXT_API_KEY', $_ENV['BLESSEDTEXT_API_KEY'] ?? '5c72f45976114ca3b869c70dfdd7145d');
define('BLESSEDTEXT_SENDER_ID', $_ENV['BLESSEDTEXT_SENDER_ID'] ?? 'FERRITE');
define('BLESSEDTEXT_USERNAME', $_ENV['BLESSEDTEXT_USERNAME'] ?? 'johnmutua');

// BlessedText API Endpoint - Correct URL
define('BLESSEDTEXT_API_URL', 'https://sms.blessedtexts.com/api/sms/v1/sendsms');

/**
 * Send SMS using BlessedText API
 * 
 * @param string $to Recipient phone number (format: 254712345678 or +254712345678)
 * @param string $message SMS message content
 * @return array Response with success status and details
 */
function sendBlessedTextSMS($to, $message) {
    // Format phone number for BlessedText (remove + and ensure Kenya format)
    $to = ltrim($to, '+');
    
    // If number starts with 0, replace with 254
    if (str_starts_with($to, '0')) {
        $to = '254' . substr($to, 1);
    }
    
    // If number doesn't start with 254, assume it's a local number
    if (!str_starts_with($to, '254')) {
        $to = '254' . $to;
    }
    
    // Prepare POST data for BlessedText API (according to official documentation)
    $postData = [
        'api_key' => BLESSEDTEXT_API_KEY,
        'sender_id' => BLESSEDTEXT_SENDER_ID,
        'message' => $message,
        'phone' => $to  // Parameter name from documentation
    ];
    
    // Initialize cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, BLESSEDTEXT_API_URL);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData)); // Send as JSON
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Temporarily disable SSL verification
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'DRMS-SMS-Gateway/1.0');
    
    // Execute request
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    // Handle cURL errors
    if ($error) {
        return [
            'success' => false,
            'error' => 'cURL Error: ' . $error,
            'gateway' => 'BlessedText'
        ];
    }
    
    // Parse response
    $responseData = json_decode($response, true);
    
    // BlessedText returns an array with status_code 1000 for success
    if ($httpCode >= 200 && $httpCode < 300) {
        // Response is an array, check first element
        if (is_array($responseData) && !empty($responseData)) {
            $firstResponse = $responseData[0];
            
            // Check if status_code is 1000 (success)
            if (isset($firstResponse['status_code']) && $firstResponse['status_code'] === '1000') {
                return [
                    'success' => true,
                    'message' => 'SMS sent successfully via BlessedText',
                    'gateway' => 'BlessedText',
                    'message_id' => $firstResponse['message_id'] ?? null,
                    'cost' => $firstResponse['message_cost'] ?? null,
                    'to' => $firstResponse['phone'] ?? $to,
                    'status_code' => $firstResponse['status_code'],
                    'status_desc' => $firstResponse['status_desc'] ?? 'Success',
                    'response' => $responseData
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $firstResponse['status_desc'] ?? 'Unknown BlessedText error',
                    'gateway' => 'BlessedText',
                    'status_code' => $firstResponse['status_code'] ?? null,
                    'details' => $responseData
                ];
            }
        } else {
            return [
                'success' => false,
                'error' => 'Invalid response format from BlessedText',
                'gateway' => 'BlessedText',
                'response' => $response
            ];
        }
    } else {
        return [
            'success' => false,
            'error' => $responseData['message'] ?? 'HTTP Error: ' . $httpCode,
            'gateway' => 'BlessedText',
            'code' => $httpCode,
            'details' => $responseData
        ];
    }
}

/**
 * Send bulk SMS using BlessedText API
 * 
 * @param array $recipients Array of phone numbers
 * @param string $message SMS message content
 * @return array Response with success status and details
 */
function sendBlessedTextBulkSMS($recipients, $message) {
    // Format all phone numbers
    $formattedRecipients = [];
    foreach ($recipients as $phone) {
        $phone = ltrim($phone, '+');
        if (str_starts_with($phone, '0')) {
            $phone = '254' . substr($phone, 1);
        }
        if (!str_starts_with($phone, '254')) {
            $phone = '254' . $phone;
        }
        $formattedRecipients[] = $phone;
    }
    
    // Join recipients with comma (as per documentation)
    $recipientList = implode(',', $formattedRecipients);
    
    // Prepare POST data for bulk SMS (using correct parameters)
    $postData = [
        'api_key' => BLESSEDTEXT_API_KEY,
        'sender_id' => BLESSEDTEXT_SENDER_ID,
        'message' => $message,
        'phone' => $recipientList  // Use 'phone' parameter as per documentation
    ];
    
    // Initialize cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, BLESSEDTEXT_API_URL);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData)); // Send as JSON
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60); // Longer timeout for bulk messages
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'DRMS-SMS-Gateway/1.0');
    
    // Execute request
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    // Handle cURL errors
    if ($error) {
        return [
            'success' => false,
            'error' => 'cURL Error: ' . $error,
            'gateway' => 'BlessedText'
        ];
    }
    
    // Parse response
    $responseData = json_decode($response, true);
    
    if ($httpCode >= 200 && $httpCode < 300) {
        if (isset($responseData['status']) && $responseData['status'] === 'success') {
            return [
                'success' => true,
                'message' => 'Bulk SMS sent successfully via BlessedText',
                'gateway' => 'BlessedText',
                'sent_count' => count($formattedRecipients),
                'total_cost' => $responseData['total_cost'] ?? null,
                'balance' => $responseData['balance'] ?? null,
                'response' => $responseData
            ];
        } else {
            return [
                'success' => false,
                'error' => $responseData['message'] ?? 'Unknown BlessedText error',
                'gateway' => 'BlessedText',
                'details' => $responseData
            ];
        }
    } else {
        return [
            'success' => false,
            'error' => 'HTTP Error: ' . $httpCode,
            'gateway' => 'BlessedText',
            'details' => $responseData
        ];
    }
}

/**
 * Check BlessedText account balance
 * 
 * @return array Response with balance information
 */
function getBlessedTextBalance() {
    $postData = [
        'api_key' => BLESSEDTEXT_API_KEY,
        'username' => BLESSEDTEXT_USERNAME,
        'action' => 'balance'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://www.blessedtextsms.com/api/balance');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded',
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return [
            'success' => false,
            'error' => 'cURL Error: ' . $error
        ];
    }
    
    $responseData = json_decode($response, true);
    
    if ($httpCode >= 200 && $httpCode < 300 && isset($responseData['balance'])) {
        return [
            'success' => true,
            'balance' => $responseData['balance'],
            'currency' => $responseData['currency'] ?? 'KES',
            'gateway' => 'BlessedText'
        ];
    } else {
        return [
            'success' => false,
            'error' => $responseData['message'] ?? 'Could not retrieve balance',
            'gateway' => 'BlessedText'
        ];
    }
}

/**
 * Validate phone number format for BlessedText
 */
function validateBlessedTextPhoneNumber($phone) {
    // Remove spaces, dashes, parentheses
    $phone = preg_replace('/[\s\-\(\)]/', '', $phone);
    $phone = ltrim($phone, '+');
    
    // Check if it's a valid Kenyan number format
    if (preg_match('/^(254|0)[1-9]\d{8}$/', $phone)) {
        return true;
    }
    
    return false;
}

/**
 * Test BlessedText configuration
 */
function testBlessedTextConnection() {
    $testNumber = '254700000000'; // Test number
    $testMessage = 'DRMS SMS Gateway Test - ' . date('Y-m-d H:i:s');
    
    return sendBlessedTextSMS($testNumber, $testMessage);
}
?>
