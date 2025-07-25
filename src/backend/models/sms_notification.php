<?php
// Enhanced Notification System with SMS support and multiple gateways
require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../config/sms_gateway_manager.php';

/**
 * Send notification (in-app + SMS)
 */
function sendNotificationWithSMS($conn, $user_id, $message, $send_sms = false) {
    try {
        // Add in-app notification (using system user as sender)
        add_notification($conn, 1, $user_id, $message);
        
        if ($send_sms) {
            // Get user phone number
            $stmt = $conn->prepare('SELECT phone, username FROM users WHERE id = ?');
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            
            if ($user && !empty($user['phone'])) {
                // Send SMS using gateway manager with fallback
                $smsResult = sendSMSWithFallback($user['phone'], $message);
                
                // Log SMS attempt with gateway information
                $status = $smsResult['success'] ? 'sent' : 'failed';
                $error = $smsResult['success'] ? null : $smsResult['error'];
                $gateway = $smsResult['gateway'] ?? 'unknown';
                $message_id = $smsResult['message_id'] ?? $smsResult['sid'] ?? null;
                $used_fallback = $smsResult['used_fallback'] ?? false;
                
                // Enhanced logging with gateway info
                $logStmt = $conn->prepare('INSERT INTO sms_logs (user_id, recipient_phone, message, status, sent_by, gateway_used, message_id, error_message, used_fallback) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
                $logStmt->bind_param('isssisssi', $user_id, $user['phone'], $message, $status, 1, $gateway, $message_id, $error, $used_fallback);
                $logStmt->execute();
                
                return $smsResult;
            }
        }
        
        return ['success' => true, 'message' => 'Notification sent'];
        
    } catch (Exception $e) {
        error_log('Notification error: ' . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Send bulk SMS notifications to multiple users
 */
function sendBulkSMS($conn, $user_ids, $message, $sent_by_id = 1) {
    $results = [];
    
    foreach ($user_ids as $user_id) {
        try {
            // Get user details
            $stmt = $conn->prepare('SELECT phone, username FROM users WHERE id = ?');
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            
            if ($user && !empty($user['phone'])) {
                // Send SMS using gateway manager with fallback
                $smsResult = sendSMSWithFallback($user['phone'], $message);
                
                // Log result with enhanced gateway information
                $status = $smsResult['success'] ? 'sent' : 'failed';
                $error = $smsResult['success'] ? null : $smsResult['error'];
                $gateway = $smsResult['gateway'] ?? 'unknown';
                $message_id = $smsResult['message_id'] ?? $smsResult['sid'] ?? null;
                $used_fallback = $smsResult['used_fallback'] ?? false;
                
                $logStmt = $conn->prepare('INSERT INTO sms_logs (user_id, recipient_phone, message, status, sent_by, gateway_used, message_id, error_message, used_fallback) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
                $logStmt->bind_param('isssisssi', $user_id, $user['phone'], $message, $status, $sent_by_id, $gateway, $message_id, $error, $used_fallback);
                $logStmt->execute();
                
                $results[] = [
                    'user_id' => $user_id,
                    'username' => $user['username'],
                    'phone' => $user['phone'],
                    'success' => $smsResult['success'],
                    'gateway' => $gateway,
                    'used_fallback' => $used_fallback,
                    'error' => $smsResult['error'] ?? null
                ];
            } else {
                $results[] = [
                    'user_id' => $user_id,
                    'username' => $user['username'] ?? 'Unknown',
                    'phone' => null,
                    'success' => false,
                    'gateway' => 'none',
                    'error' => 'No phone number'
                ];
            }
            
            // Small delay to avoid rate limiting
            usleep(100000); // 0.1 second
            
        } catch (Exception $e) {
            $results[] = [
                'user_id' => $user_id,
                'success' => false,
                'gateway' => 'error',
                'error' => $e->getMessage()
            ];
        }
    }
    
    return $results;
}

/**
 * Send SMS for request status updates
 */
function sendRequestStatusSMS($conn, $request_id, $new_status) {
    try {
        // Get request and user details
        $stmt = $conn->prepare('
            SELECT r.*, u.phone, u.username 
            FROM requests1 r 
            JOIN users u ON r.user_id = u.id 
            WHERE r.id = ?
        ');
        $stmt->bind_param('i', $request_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $request = $result->fetch_assoc();
        
        if (!$request) {
            return ['success' => false, 'error' => 'Request not found'];
        }
        
        // Create status-specific message
        $messages = [
            'Approved' => "✅ Your waste collection request #{$request_id} has been APPROVED. We'll assign a driver soon.",
            'Rejected' => "❌ Your waste collection request #{$request_id} has been REJECTED. Please contact support for details.",
            'Assigned' => "🚛 Your waste collection request #{$request_id} has been ASSIGNED to a driver. Prepare your waste for collection.",
            'Completed' => "✅ Your waste collection request #{$request_id} has been COMPLETED. Thank you for using DRMS!"
        ];
        
        $message = $messages[$new_status] ?? "Your request #{$request_id} status has been updated to: {$new_status}";
        
        if (!empty($request['phone'])) {
            return sendTwilioSMS($request['phone'], $message);
        }
        
        return ['success' => false, 'error' => 'No phone number'];
        
    } catch (Exception $e) {
        error_log('Status SMS error: ' . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Send SMS reminders for pending collections
 */
function sendCollectionReminders($conn) {
    try {
        // Get requests scheduled for tomorrow
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        
        $stmt = $conn->prepare('
            SELECT r.id, r.waste_type, r.location, u.phone, u.username 
            FROM requests1 r 
            JOIN users u ON r.user_id = u.id 
            WHERE r.preferred_date = ? AND r.status = "Assigned" 
            AND u.phone IS NOT NULL AND u.phone != ""
        ');
        $stmt->bind_param('s', $tomorrow);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $sent_count = 0;
        while ($request = $result->fetch_assoc()) {
            $message = "🗓️ REMINDER: Your waste collection is scheduled for TOMORROW ({$tomorrow}). Please prepare your {$request['waste_type']} for collection at {$request['location']}. - DRMS";
            
            $smsResult = sendTwilioSMS($request['phone'], $message);
            if ($smsResult['success']) {
                $sent_count++;
            }
            
            // Log SMS
            $status = $smsResult['success'] ? 'sent' : 'failed';
            $error = $smsResult['success'] ? null : $smsResult['error'];
            
            $logStmt = $conn->prepare('INSERT INTO sms_logs (user_id, recipient_phone, message, status, sent_by, twilio_sid, error_message) VALUES ((SELECT user_id FROM requests1 WHERE id = ?), ?, ?, ?, ?, ?, ?)');
            $logStmt->bind_param('issssss', $request['id'], $request['phone'], $message, $status, 1, $smsResult['sid'] ?? null, $error);
            $logStmt->execute();
            
            usleep(200000); // 0.2 second delay
        }
        
        return ['success' => true, 'sent_count' => $sent_count];
        
    } catch (Exception $e) {
        error_log('Reminder SMS error: ' . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    }
}
?>
