<?php
/**
 * BlessedText SMS Setup Script
 * Easy configuration for BlessedText SMS gateway
 */

$envFile = '.env';
$envPath = __DIR__ . '/' . $envFile;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_config') {
        $apiKey = trim($_POST['api_key']);
        $username = trim($_POST['username']);
        $senderId = trim($_POST['sender_id']);
        $primaryGateway = $_POST['primary_gateway'];
        $fallbackGateway = $_POST['fallback_gateway'];
        
        if ($apiKey && $username && $senderId) {
            // Read current .env file
            $envContent = file_exists($envPath) ? file_get_contents($envPath) : '';
            
            // Update BlessedText configuration
            $envContent = updateEnvVariable($envContent, 'BLESSEDTEXT_API_KEY', $apiKey);
            $envContent = updateEnvVariable($envContent, 'BLESSEDTEXT_USERNAME', $username);
            $envContent = updateEnvVariable($envContent, 'BLESSEDTEXT_SENDER_ID', $senderId);
            $envContent = updateEnvVariable($envContent, 'SMS_PRIMARY_GATEWAY', $primaryGateway);
            $envContent = updateEnvVariable($envContent, 'SMS_FALLBACK_GATEWAY', $fallbackGateway);
            
            // Save updated .env file
            if (file_put_contents($envPath, $envContent)) {
                $success = "Configuration updated successfully!";
            } else {
                $error = "Failed to update configuration file.";
            }
        } else {
            $error = "Please fill in all required fields.";
        }
    }
}

function updateEnvVariable($content, $key, $value) {
    $pattern = '/^' . preg_quote($key, '/') . '=.*$/m';
    $replacement = $key . '=' . $value;
    
    if (preg_match($pattern, $content)) {
        return preg_replace($pattern, $replacement, $content);
    } else {
        return $content . "\n" . $replacement;
    }
}

// Read current values
$currentEnv = [];
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && !str_starts_with(trim($line), '#')) {
            [$key, $value] = explode('=', $line, 2);
            $currentEnv[trim($key)] = trim($value);
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BlessedText SMS Setup - DRMS</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
            font-size: 2.5em;
        }

        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }

        .alert-error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }

        .alert-info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
        }

        .section {
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            border-left: 4px solid #007bff;
        }

        .section h2 {
            color: #495057;
            margin-bottom: 15px;
            font-size: 1.4em;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #495057;
        }

        input, select, textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #007bff;
        }

        .btn {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: transform 0.2s;
            margin-right: 10px;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .btn-success { background: linear-gradient(135deg, #28a745, #1e7e34); }

        .help-text {
            font-size: 0.9em;
            color: #6c757d;
            margin-top: 5px;
        }

        .current-config {
            background: white;
            padding: 15px;
            border-radius: 6px;
            border: 1px solid #dee2e6;
            font-family: monospace;
            white-space: pre-wrap;
        }

        .step {
            counter-increment: step-counter;
            margin-bottom: 20px;
            padding: 15px;
            background: white;
            border-radius: 8px;
            border-left: 4px solid #28a745;
        }

        .step::before {
            content: "Step " counter(step-counter);
            font-weight: bold;
            color: #28a745;
            display: block;
            margin-bottom: 10px;
        }

        .steps {
            counter-reset: step-counter;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 BlessedText SMS Setup</h1>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                ✅ <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                ❌ <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Current Configuration -->
        <div class="section">
            <h2>📋 Current Configuration</h2>
            <div class="current-config">BlessedText API Key: <?php echo isset($currentEnv['BLESSEDTEXT_API_KEY']) ? (strlen($currentEnv['BLESSEDTEXT_API_KEY']) > 10 ? substr($currentEnv['BLESSEDTEXT_API_KEY'], 0, 8) . '...' : $currentEnv['BLESSEDTEXT_API_KEY']) : 'Not set'; ?>
BlessedText Username: <?php echo $currentEnv['BLESSEDTEXT_USERNAME'] ?? 'Not set'; ?>
BlessedText Sender ID: <?php echo $currentEnv['BLESSEDTEXT_SENDER_ID'] ?? 'Not set'; ?>
Primary Gateway: <?php echo $currentEnv['SMS_PRIMARY_GATEWAY'] ?? 'Not set'; ?>
Fallback Gateway: <?php echo $currentEnv['SMS_FALLBACK_GATEWAY'] ?? 'Not set'; ?></div>
        </div>

        <!-- Setup Instructions -->
        <div class="section">
            <h2>📚 Setup Instructions</h2>
            <div class="steps">
                <div class="step">
                    <strong>Get BlessedText Account</strong><br>
                    Visit <a href="https://www.blessedtextsms.com" target="_blank">BlessedText SMS</a> and create an account.
                    Get your API credentials from the dashboard.
                </div>
                
                <div class="step">
                    <strong>Configure Credentials</strong><br>
                    Enter your BlessedText API key, username, and sender ID in the form below.
                    The sender ID should be approved by BlessedText for best delivery rates.
                </div>
                
                <div class="step">
                    <strong>Set Gateway Priority</strong><br>
                    Choose which SMS gateway to use as primary and which as fallback.
                    The system will automatically try the fallback if the primary fails.
                </div>
                
                <div class="step">
                    <strong>Test Configuration</strong><br>
                    Use the test page to verify your SMS configuration is working correctly.
                </div>
            </div>
        </div>

        <!-- Configuration Form -->
        <div class="section">
            <h2>⚙️ Update Configuration</h2>
            <form method="POST">
                <input type="hidden" name="action" value="update_config">
                
                <div class="form-group">
                    <label for="api_key">BlessedText API Key *</label>
                    <input type="password" id="api_key" name="api_key" 
                           value="<?php echo htmlspecialchars($currentEnv['BLESSEDTEXT_API_KEY'] ?? ''); ?>" 
                           required>
                    <div class="help-text">Your BlessedText API key from the dashboard</div>
                </div>
                
                <div class="form-group">
                    <label for="username">BlessedText Username *</label>
                    <input type="text" id="username" name="username" 
                           value="<?php echo htmlspecialchars($currentEnv['BLESSEDTEXT_USERNAME'] ?? ''); ?>" 
                           required>
                    <div class="help-text">Your BlessedText account username</div>
                </div>
                
                <div class="form-group">
                    <label for="sender_id">Sender ID *</label>
                    <input type="text" id="sender_id" name="sender_id" 
                           value="<?php echo htmlspecialchars($currentEnv['BLESSEDTEXT_SENDER_ID'] ?? 'DRMS'); ?>" 
                           maxlength="11" required>
                    <div class="help-text">Sender ID shown in SMS (max 11 characters, should be approved)</div>
                </div>
                
                <div class="form-group">
                    <label for="primary_gateway">Primary SMS Gateway</label>
                    <select id="primary_gateway" name="primary_gateway">
                        <option value="africastalking" <?php echo ($currentEnv['SMS_PRIMARY_GATEWAY'] ?? '') === 'africastalking' ? 'selected' : ''; ?>>Africa's Talking (Recommended)</option>
                        <option value="blessedtext" <?php echo ($currentEnv['SMS_PRIMARY_GATEWAY'] ?? '') === 'blessedtext' ? 'selected' : ''; ?>>BlessedText</option>
                        <option value="twilio" <?php echo ($currentEnv['SMS_PRIMARY_GATEWAY'] ?? '') === 'twilio' ? 'selected' : ''; ?>>Twilio</option>
                    </select>
                    <div class="help-text">Primary gateway to use for sending SMS</div>
                </div>
                
                <div class="form-group">
                    <label for="fallback_gateway">Fallback SMS Gateway</label>
                    <select id="fallback_gateway" name="fallback_gateway">
                        <option value="blessedtext" <?php echo ($currentEnv['SMS_FALLBACK_GATEWAY'] ?? '') === 'blessedtext' ? 'selected' : ''; ?>>BlessedText</option>
                        <option value="africastalking" <?php echo ($currentEnv['SMS_FALLBACK_GATEWAY'] ?? '') === 'africastalking' ? 'selected' : ''; ?>>Africa's Talking</option>
                        <option value="twilio" <?php echo ($currentEnv['SMS_FALLBACK_GATEWAY'] ?? '') === 'twilio' ? 'selected' : ''; ?>>Twilio</option>
                        <option value="">None (no fallback)</option>
                    </select>
                    <div class="help-text">Backup gateway to use if primary fails</div>
                </div>
                
                <button type="submit" class="btn btn-success">💾 Save Configuration</button>
            </form>
        </div>

        <!-- Quick Actions -->
        <div class="section">
            <h2>🚀 Next Steps</h2>
            <a href="test_blessedtext_integration.php" class="btn">🧪 Test SMS Integration</a>
            <a href="src/frontend/assets/admin_send_sms.php" class="btn btn-success">📤 Send SMS</a>
            <a href="src/frontend/assets/sms_logs.php" class="btn">📋 View SMS Logs</a>
        </div>

        <!-- Important Notes -->
        <div class="alert alert-info">
            <strong>📝 Important Notes:</strong><br>
            • Make sure your BlessedText account has sufficient credits<br>
            • Test with your own phone number first<br>
            • The sender ID should be approved by BlessedText for best delivery rates<br>
            • Keep your API credentials secure and never share them<br>
            • The system will automatically use fallback gateway if primary fails
        </div>
    </div>
</body>
</html>
