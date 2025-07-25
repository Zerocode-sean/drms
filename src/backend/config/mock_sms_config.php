<?php
// Local Mock SMS Service for Development/Testing
// This service simulates SMS sending without actually sending real SMS messages
// All "sent" messages are logged to files and can be viewed via web interface

class MockSMSService {
    private $logFile;
    private $logDir;
    
    public function __construct() {
        $this->logDir = __DIR__ . '/../../logs';
        $this->logFile = $this->logDir . '/sms_mock.log';
        
        // Create logs directory if it doesn't exist
        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0755, true);
        }
    }
    
    /**
     * Send a mock SMS message
     * @param string $to Phone number
     * @param string $message Message content
     * @param int $userId User ID sending the message
     * @return array Response with success status and message ID
     */
    public function sendSMS($to, $message, $userId = null) {
        try {
            // Generate a mock message ID
            $messageId = 'MOCK_' . uniqid() . '_' . time();
            
            // Prepare log entry
            $logEntry = [
                'id' => $messageId,
                'to' => $this->formatPhoneNumber($to),
                'message' => $message,
                'user_id' => $userId,
                'timestamp' => date('Y-m-d H:i:s'),
                'status' => 'sent',
                'service' => 'MockSMS'
            ];
            
            // Log to file
            $this->logToFile($logEntry);
            
            // Log to database if available
            $this->logToDatabase($logEntry);
            
            return [
                'success' => true,
                'message_id' => $messageId,
                'to' => $logEntry['to'],
                'status' => 'sent',
                'message' => 'Mock SMS sent successfully'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to send mock SMS: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Format phone number for display
     */
    private function formatPhoneNumber($phone) {
        // Remove any non-digit characters
        $phone = preg_replace('/\D/', '', $phone);
        
        // Add +254 prefix if it's a Kenyan number starting with 0
        if (strlen($phone) == 10 && substr($phone, 0, 1) == '0') {
            $phone = '+254' . substr($phone, 1);
        } elseif (strlen($phone) == 9) {
            $phone = '+254' . $phone;
        } elseif (strlen($phone) > 10 && substr($phone, 0, 3) != '+254') {
            $phone = '+' . $phone;
        }
        
        return $phone;
    }
    
    /**
     * Log SMS to file
     */
    private function logToFile($logEntry) {
        $logLine = json_encode($logEntry) . "\n";
        file_put_contents($this->logFile, $logLine, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Log SMS to database
     */
    private function logToDatabase($logEntry) {
        try {
            require_once __DIR__ . '/db_config.php';
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            if ($conn->connect_error) {
                return; // Fail silently if DB is not available
            }
            
            $stmt = $conn->prepare("INSERT INTO sms_logs (user_id, recipient_phone, message, status, sent_by, twilio_sid, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
            
            if ($stmt) {
                $stmt->bind_param('issssss', 
                    $logEntry['user_id'], 
                    $logEntry['to'], 
                    $logEntry['message'], 
                    $logEntry['status'], 
                    $logEntry['user_id'], 
                    $logEntry['id'], 
                    $logEntry['timestamp']
                );
                $stmt->execute();
                $stmt->close();
            }
            
            $conn->close();
        } catch (Exception $e) {
            // Fail silently - file logging is the backup
        }
    }
    
    /**
     * Get recent SMS logs
     */
    public function getRecentLogs($limit = 10) {
        if (!file_exists($this->logFile)) {
            return [];
        }
        
        $lines = file($this->logFile, FILE_IGNORE_NEW_LINES);
        $logs = [];
        
        // Get the last $limit lines
        $lines = array_slice($lines, -$limit);
        
        foreach (array_reverse($lines) as $line) {
            $log = json_decode($line, true);
            if ($log) {
                $logs[] = $log;
            }
        }
        
        return $logs;
    }
    
    /**
     * Clear all logs
     */
    public function clearLogs() {
        if (file_exists($this->logFile)) {
            unlink($this->logFile);
        }
        return true;
    }
}

// Helper function for backward compatibility
function sendMockSMS($to, $message, $userId = null) {
    $smsService = new MockSMSService();
    return $smsService->sendSMS($to, $message, $userId);
}
?>
