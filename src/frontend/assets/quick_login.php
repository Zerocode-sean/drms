<?php
// Quick login for testing
session_start();

if ($_POST['action'] ?? '' === 'login') {
    // Set admin session manually for testing
    $_SESSION['user_id'] = 1;
    $_SESSION['role'] = 'admin';
    $_SESSION['username'] = 'admin';
    
    header('Location: admin_smart_scheduling.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quick Admin Login</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 50px; }
        .login-box { max-width: 400px; margin: 0 auto; padding: 30px; border: 1px solid #ddd; border-radius: 8px; }
        button { background: #007bff; color: white; border: none; padding: 12px 24px; border-radius: 5px; cursor: pointer; width: 100%; }
        .status { padding: 15px; margin: 15px 0; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; }
        .info { background: #d1ecf1; color: #0c5460; }
    </style>
</head>
<body>
    <div class="login-box">
        <h1>🔐 Quick Admin Login</h1>
        
        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="status success">
                ✅ Already logged in as: <?= $_SESSION['username'] ?? 'User' ?> (<?= $_SESSION['role'] ?? 'unknown' ?>)
            </div>
            <p><a href="admin_smart_scheduling.php">Go to Smart Scheduling</a></p>
            <p><a href="admin_smart_scheduling_debug.php">Go to Debug Version</a></p>
        <?php else: ?>
            <div class="status info">
                ℹ️ Not logged in. Click below to login as admin for testing.
            </div>
            <form method="post">
                <input type="hidden" name="action" value="login">
                <button type="submit">Login as Admin</button>
            </form>
        <?php endif; ?>
        
        <hr>
        <h3>Session Info:</h3>
        <pre><?php print_r($_SESSION); ?></pre>
        
        <h3>Quick Links:</h3>
        <ul>
            <li><a href="admin_smart_scheduling.php">Original Admin Page</a></li>
            <li><a href="admin_smart_scheduling_debug.php">Debug Version (No Login Required)</a></li>
            <li><a href="../../test_admin_page_load.php">Page Load Test</a></li>
        </ul>
    </div>
</body>
</html>
