<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up </title>
    <link rel="stylesheet" href="/src/frontend/css/register.css">
    <link rel="icon" href="/src/frontend/images/logo.png">
</head>
<body onload="console.log('Page loaded successfully')">
    <div class="container">
        <div class="gradient-side">
            <img src="/src/frontend/images/logo.png" alt="Logo" style="width: 80px; height: 80px; margin-bottom: 20px; border-radius: 50%;">
            <h1>Join Us!</h1>
            <p>Create an account to start your journey.</p>
        </div>
        <div class="form-side">
            <h2>Sign Up</h2>
            <form id="register-form">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter username" required>
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Enter email" required>
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter password" required>
                <label for="confirm-password">Confirm Password</label>
                <input type="password" id="confirm-password" name="confirm-password" placeholder="Confirm password" required>
                <button type="submit">Sign Up</button>
            </form>
            <p style="margin-top: 20px; text-align: center;">Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>
    <script src="/src/frontend/js/register.js?v=4"></script>
</body>
</html>

