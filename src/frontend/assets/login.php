<?php
// Include asset helper for environment-aware paths
require_once __DIR__ . '/../../backend/config/asset_helper.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login |Page</title>
    <link rel="stylesheet" href="<?php echo cssPath('login.css'); ?>">
    <link rel="icon" href="<?php echo logoPath(); ?>">
</head>
<body>
    <div class="container">
        <div class="gradient-side">
            <img src="<?php echo logoPath(); ?>" alt="Logo" style="width: 80px; height: 80px; margin-bottom: 20px; border-radius: 50%;">
            <h1>Welcome Back!</h1>
            <p>Login to continue your journey.</p>
        </div>
        <div class="form-side">
            <h2>Login</h2>
            <form id="login-form">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter username" required>
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter password" required>
                <button type="submit">Login</button>
            </form>
            <p style="margin-top: 20px; text-align: center;">Don't have an account? <a href="register.php">Register here</a></p>
        </div>
    </div>
    <script src="<?php echo jsPath('logo-debug.js'); ?>"></script>
    <script src="<?php echo jsPath('login.js?v=2'); ?>"></script>
</body>
</html>

