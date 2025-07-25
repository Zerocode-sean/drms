<?php
// Africa's Talking SMS Configuration for DRMS
// Sign up at https://africastalking.com for free credits and sandbox testing

// Load environment variable loader utility
require_once __DIR__ . '/env_loader.php';

// Load environment variables
loadEnv(__DIR__ . '/../../../.env');

// Africa's Talking Configuration from environment variables
$AFRICASTALKING_USERNAME = $_ENV['AFRICASTALKING_USERNAME'] ?? 'sandbox'; // Use 'sandbox' for testing
$AFRICASTALKING_API_KEY = $_ENV['AFRICASTALKING_API_KEY'] ?? '';
$AFRICASTALKING_FROM = $_ENV['AFRICASTALKING_FROM'] ?? 'DRMS'; // Your sender ID

// Validate configuration
if (empty($AFRICASTALKING_API_KEY)) {
    error_log('Warning: Africa\'s Talking API key not configured. SMS will not work.');
}

/**
 * Send SMS using Africa's Talking API
 * @param string $to Phone number in international format (+254...)
 * @param string $message SMS message content
 * @return array Response with success status and details
 */
function sendAfricasTalkingSMS($to, $message) {
    global $AFRICASTALKING_USERNAME, $AFRICASTALKING_API_KEY, $AFRICASTALKING_FROM;
    
    if (empty($AFRICASTALKING_API_KEY)) {
        return [
            'success' => false,
            'error' => 'Africa\'s Talking API key not configured'
        ];
    }
    
    try {
        // Format phone number
        $to = formatPhoneNumberForAfricasTalking($to);
        
        // Prepare POST data
        $postData = [
            'username' => $AFRICASTALKING_USERNAME,
            'to' => $to,
            'message' => $message
        ];
        
        // Only add 'from' field if sender ID is not empty
        if (!empty($AFRICASTALKING_FROM)) {
            $postData['from'] = $AFRICASTALKING_FROM;
        }
        
        // Initialize cURL
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.africastalking.com/version1/messaging',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($postData),
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/x-www-form-urlencoded',
                'apiKey: ' . $AFRICASTALKING_API_KEY
            ],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);
        curl_close($curl);
        
        if ($curlError) {
            throw new Exception('cURL Error: ' . $curlError);
        }
        
        if ($httpCode !== 201) {
            throw new Exception('HTTP Error: ' . $httpCode . ' - ' . $response);
        }
        
        $data = json_decode($response, true);
        
        if (!$data) {
            throw new Exception('Invalid JSON response from Africa\'s Talking');
        }
        
        // Check if the message was accepted
        if (isset($data['SMSMessageData']['Recipients']) && count($data['SMSMessageData']['Recipients']) > 0) {
            $recipient = $data['SMSMessageData']['Recipients'][0];
            
            if (isset($recipient['status']) && strpos($recipient['status'], 'Success') !== false) {
                return [
                    'success' => true,
                    'message_id' => $recipient['messageId'] ?? uniqid('AT_'),
                    'status' => $recipient['status'],
                    'cost' => $recipient['cost'] ?? 'Unknown',
                    'to' => $to
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Message rejected: ' . ($recipient['status'] ?? 'Unknown error')
                ];
            }
        } else {
            return [
                'success' => false,
                'error' => 'No recipients in response'
            ];
        }
        
    } catch (Exception $e) {
        error_log('Africa\'s Talking SMS Error: ' . $e->getMessage());
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Format phone number for Africa's Talking (international format)
 * @param string $phone Phone number
 * @return string Formatted phone number
 */
function formatPhoneNumberForAfricasTalking($phone) {
    // Remove any non-digit characters
    $phone = preg_replace('/\D/', '', $phone);
    
    // Handle Kenyan numbers
    if (strlen($phone) == 10 && substr($phone, 0, 1) == '0') {
        // Convert 0712345678 to +254712345678
        return '+254' . substr($phone, 1);
    } elseif (strlen($phone) == 9) {
        // Convert 712345678 to +254712345678
        return '+254' . $phone;
    } elseif (strlen($phone) == 12 && substr($phone, 0, 3) == '254') {
        // Convert 254712345678 to +254712345678
        return '+' . $phone;
    } elseif (substr($phone, 0, 1) == '+') {
        // Already in international format
        return $phone;
    } else {
        // Assume it's already formatted or add + if needed
        return '+' . $phone;
    }
}

/**
 * Get account balance from Africa's Talking
 * @return array Balance information
 */
function getAfricasTalkingBalance() {
    global $AFRICASTALKING_USERNAME, $AFRICASTALKING_API_KEY;
    
    if (empty($AFRICASTALKING_API_KEY)) {
        return [
            'success' => false,
            'error' => 'API key not configured'
        ];
    }
    
    try {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.africastalking.com/version1/user?username=' . $AFRICASTALKING_USERNAME,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'apiKey: ' . $AFRICASTALKING_API_KEY
            ],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            return [
                'success' => true,
                'balance' => $data['UserData']['balance'] ?? 'Unknown'
            ];
        } else {
            return [
                'success' => false,
                'error' => 'Failed to get balance: HTTP ' . $httpCode
            ];
        }
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}
?>
