<?php
/**
 * Unified SMS Gateway Manager for DRMS
 * Supports multiple SMS providers with automatic fallback
 * 
 * Supported Gateways:
 * - BlessedText (primary - local Kenya provider)
 * - Twilio (fallback - international)
 */

// Load SMS configurations (removed Africa's Talking)
require_once __DIR__ . '/sms_utils.php';
require_once __DIR__ . '/blessedtext_config.php';
require_once __DIR__ . '/twilio_config.php';
require_once __DIR__ . '/env_loader.php';

// Load environment variables
loadEnv(__DIR__ . '/../../../.env');

// Gateway configuration from environment
define('SMS_PRIMARY_GATEWAY', $_ENV['SMS_PRIMARY_GATEWAY'] ?? 'blessedtext');
define('SMS_FALLBACK_GATEWAY', $_ENV['SMS_FALLBACK_GATEWAY'] ?? 'twilio');

/**
 * Send SMS using the configured gateway with fallback support
 * 
 * @param string $to Recipient phone number
 * @param string $message SMS message content
 * @param bool $use_fallback Whether to try fallback gateway on failure
 * @return array Response with success status, gateway used, and details
 */
function sendSMSWithFallback($to, $message, $use_fallback = true) {
    $primaryGateway = SMS_PRIMARY_GATEWAY;
    $fallbackGateway = SMS_FALLBACK_GATEWAY;
    
    // Try primary gateway first
    $result = sendSMSViaGateway($to, $message, $primaryGateway);
    
    // If primary failed and fallback is enabled and available
    if (!$result['success'] && $use_fallback && $fallbackGateway && $fallbackGateway !== $primaryGateway) {
        error_log("Primary SMS gateway ($primaryGateway) failed, trying fallback ($fallbackGateway): " . $result['error']);
        
        $fallbackResult = sendSMSViaGateway($to, $message, $fallbackGateway);
        
        if ($fallbackResult['success']) {
            $fallbackResult['used_fallback'] = true;
            $fallbackResult['primary_error'] = $result['error'];
            return $fallbackResult;
        } else {
            // Both failed
            return [
                'success' => false,
                'error' => "Both gateways failed. Primary ($primaryGateway): " . $result['error'] . ". Fallback ($fallbackGateway): " . $fallbackResult['error'],
                'gateway' => 'none',
                'primary_gateway' => $primaryGateway,
                'fallback_gateway' => $fallbackGateway,
                'primary_error' => $result['error'],
                'fallback_error' => $fallbackResult['error']
            ];
        }
    }
    
    return $result;
}

/**
 * Send SMS via specific gateway
 * 
 * @param string $to Recipient phone number
 * @param string $message SMS message content
 * @param string $gateway Gateway to use (blessedtext, twilio)
 * @return array Response with success status and details
 */
function sendSMSViaGateway($to, $message, $gateway) {
    switch (strtolower($gateway)) {
        case 'blessedtext':
            if (function_exists('sendBlessedTextSMS')) {
                return sendBlessedTextSMS($to, $message);
            } else {
                return [
                    'success' => false,
                    'error' => 'BlessedText SMS function not available',
                    'gateway' => 'blessedtext'
                ];
            }
            
        case 'twilio':
            if (function_exists('sendTwilioSMS')) {
                return sendTwilioSMS($to, $message);
            } else {
                return [
                    'success' => false,
                    'error' => 'Twilio SMS function not available',
                    'gateway' => 'twilio'
                ];
            }
            
        default:
            return [
                'success' => false,
                'error' => 'Unknown SMS gateway: ' . $gateway,
                'gateway' => $gateway
            ];
    }
}

/**
 * Send bulk SMS using the configured gateway with fallback support
 * 
 * @param array $recipients Array of phone numbers
 * @param string $message SMS message content
 * @param bool $use_fallback Whether to try fallback gateway on failure
 * @return array Response with success status, gateway used, and details
 */
function sendBulkSMSWithFallback($recipients, $message, $use_fallback = true) {
    $primaryGateway = SMS_PRIMARY_GATEWAY;
    $fallbackGateway = SMS_FALLBACK_GATEWAY;
    
    // Try primary gateway first
    $result = sendBulkSMSViaGateway($recipients, $message, $primaryGateway);
    
    // If primary failed and fallback is enabled and available
    if (!$result['success'] && $use_fallback && $fallbackGateway && $fallbackGateway !== $primaryGateway) {
        error_log("Primary bulk SMS gateway ($primaryGateway) failed, trying fallback ($fallbackGateway): " . $result['error']);
        
        $fallbackResult = sendBulkSMSViaGateway($recipients, $message, $fallbackGateway);
        
        if ($fallbackResult['success']) {
            $fallbackResult['used_fallback'] = true;
            $fallbackResult['primary_error'] = $result['error'];
            return $fallbackResult;
        } else {
            // Both failed
            return [
                'success' => false,
                'error' => "Both gateways failed. Primary ($primaryGateway): " . $result['error'] . ". Fallback ($fallbackGateway): " . $fallbackResult['error'],
                'gateway' => 'none',
                'primary_gateway' => $primaryGateway,
                'fallback_gateway' => $fallbackGateway,
                'primary_error' => $result['error'],
                'fallback_error' => $fallbackResult['error']
            ];
        }
    }
    
    return $result;
}

/**
 * Send bulk SMS via specific gateway
 * 
 * @param array $recipients Array of phone numbers
 * @param string $message SMS message content
 * @param string $gateway Gateway to use (blessedtext, twilio)
 * @return array Response with success status and details
 */
function sendBulkSMSViaGateway($recipients, $message, $gateway) {
    switch (strtolower($gateway)) {
        case 'blessedtext':
            if (function_exists('sendBlessedTextBulkSMS')) {
                return sendBlessedTextBulkSMS($recipients, $message);
            } else {
                return [
                    'success' => false,
                    'error' => 'BlessedText bulk SMS function not available',
                    'gateway' => 'blessedtext'
                ];
            }
            
        case 'twilio':
            // Twilio doesn't have bulk SMS, send individually
            $results = [];
            $success_count = 0;
            $total_count = count($recipients);
            
            foreach ($recipients as $recipient) {
                $result = sendTwilioSMS($recipient, $message);
                $results[] = [
                    'recipient' => $recipient,
                    'success' => $result['success'],
                    'error' => $result['error'] ?? null,
                    'sid' => $result['sid'] ?? null
                ];
                
                if ($result['success']) {
                    $success_count++;
                }
                
                // Small delay to avoid rate limiting
                usleep(100000); // 0.1 second
            }
            
            return [
                'success' => $success_count > 0,
                'gateway' => 'twilio',
                'total_sent' => $success_count,
                'total_recipients' => $total_count,
                'success_rate' => round(($success_count / $total_count) * 100, 2),
                'results' => $results,
                'message' => $success_count === $total_count 
                    ? 'All messages sent successfully' 
                    : "Sent $success_count out of $total_count messages"
            ];
            
        default:
            return [
                'success' => false,
                'error' => 'Unknown SMS gateway: ' . $gateway,
                'gateway' => $gateway
            ];
    }
}

/**
 * Get available SMS gateways and their status
 * 
 * @return array List of gateways with their availability status
 */
function getAvailableSMSGateways() {
    $gateways = [];
    
    // Check BlessedText
    $gateways['blessedtext'] = [
        'name' => 'BlessedText',
        'available' => function_exists('sendBlessedTextSMS'),
        'config_valid' => defined('BLESSEDTEXT_API_KEY') && BLESSEDTEXT_API_KEY !== 'your_api_key_here',
        'recommended' => true,
        'description' => 'Local Kenya SMS provider with good delivery rates'
    ];
    
    // Check Twilio
    $gateways['twilio'] = [
        'name' => 'Twilio',
        'available' => function_exists('sendTwilioSMS'),
        'config_valid' => defined('TWILIO_ACCOUNT_SID') && TWILIO_ACCOUNT_SID !== 'your_sid_here',
        'recommended' => false,
        'description' => 'International SMS provider with global coverage'
    ];
    
    return $gateways;
}

/**
 * Test SMS gateway connection and configuration
 * 
 * @param string $gateway Gateway to test
 * @param string $test_number Phone number to send test SMS to
 * @return array Test results
 */
function testSMSGateway($gateway, $test_number) {
    $test_message = "DRMS Test SMS - " . date('Y-m-d H:i:s');
    
    $start_time = microtime(true);
    $result = sendSMSViaGateway($test_number, $test_message, $gateway);
    $end_time = microtime(true);
    
    $result['response_time'] = round(($end_time - $start_time) * 1000, 2) . 'ms';
    $result['test_number'] = $test_number;
    $result['test_time'] = date('Y-m-d H:i:s');
    
    return $result;
}
?>
