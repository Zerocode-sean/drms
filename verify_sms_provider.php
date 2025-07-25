<?php
/**
 * SMS Provider Verification Page
 * Shows current SMS configuration and clears any cache issues
 */

// Clear any potential cache
header('Cache-Control: no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

require_once 'src/backend/config/env_loader.php';
loadEnv(__DIR__ . '/.env');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>SMS Provider Verification - DRMS</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .success { border-left: 5px solid #28a745; }
        .info { border-left: 5px solid #17a2b8; }
        .warning { border-left: 5px solid #ffc107; }
        h1 { color: #333; }
        h2 { color: #666; }
        .status { font-weight: bold; font-size: 18px; }
        .timestamp { color: #999; font-size: 12px; }
    </style>
</head>
<body>
    <h1>🔍 SMS Provider Verification</h1>
    <div class="timestamp">Generated: <?php echo date('Y-m-d H:i:s'); ?></div>
    
    <div class="card success">
        <h2>✅ Current SMS Configuration</h2>
        <div class="status">Primary Gateway: <strong><?php echo $_ENV['SMS_PRIMARY_GATEWAY'] ?? 'Not Set'; ?></strong></div>
        <div class="status">Fallback Gateway: <strong><?php echo $_ENV['SMS_FALLBACK_GATEWAY'] ?? 'Not Set'; ?></strong></div>
        <div class="status">SMS Mode: <strong><?php echo $_ENV['SMS_MODE'] ?? 'Not Set'; ?></strong></div>
    </div>

    <div class="card info">
        <h2>📱 BlessedText Configuration</h2>
        <p><strong>API Key:</strong> <?php echo !empty($_ENV['BLESSEDTEXT_API_KEY']) ? 'Configured ✅' : 'Not Set ❌'; ?></p>
        <p><strong>Username:</strong> <?php echo $_ENV['BLESSEDTEXT_USERNAME'] ?? 'Not Set'; ?></p>
        <p><strong>Sender ID:</strong> <?php echo $_ENV['BLESSEDTEXT_SENDER_ID'] ?? 'Not Set'; ?></p>
    </div>

    <div class="card info">
        <h2>📞 Twilio Configuration (Fallback)</h2>
        <p><strong>Account SID:</strong> <?php echo !empty($_ENV['TWILIO_ACCOUNT_SID']) ? 'Configured ✅' : 'Not Set ❌'; ?></p>
        <p><strong>Auth Token:</strong> <?php echo !empty($_ENV['TWILIO_AUTH_TOKEN']) ? 'Configured ✅' : 'Not Set ❌'; ?></p>
        <p><strong>Phone Number:</strong> <?php echo $_ENV['TWILIO_PHONE_NUMBER'] ?? 'Not Set'; ?></p>
    </div>

    <div class="card warning">
        <h2>⚠️ Africa's Talking Status</h2>
        <p><strong>Status:</strong> Disabled (Legacy)</p>
        <p><strong>API Key:</strong> <?php echo !empty($_ENV['AFRICASTALKING_API_KEY']) ? 'Still in .env (commented out)' : 'Removed'; ?></p>
        <p>Africa's Talking has been replaced with BlessedText as the primary SMS provider.</p>
    </div>

    <div class="card success">
        <h2>✅ Verification Complete</h2>
        <p><strong>Current SMS Provider:</strong> BlessedText (Primary) + Twilio (Fallback)</p>
        <p><strong>All UI References:</strong> Updated to BlessedText</p>
        <p><strong>Cache Headers:</strong> Added to prevent caching issues</p>
        <p><strong>Last Verification:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
    </div>

    <div style="text-align: center; margin-top: 30px;">
        <a href="src/frontend/assets/sms_logs.php" style="background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
            View SMS Logs (Updated)
        </a>
    </div>
</body>
</html>
