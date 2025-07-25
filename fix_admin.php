<?php
// Fix admin user - Visit: https://drms-app.onrender.com/fix_admin.php
require_once 'src/backend/config/db_config.php';

echo "<h2>🔧 Admin User Fix Tool</h2>";
echo "<hr>";

try {
    // Check if admin user exists
    echo "<h3>1. Checking Current Admin User</h3>";
    $stmt = $mysqli->prepare("SELECT * FROM users WHERE username = 'admin' OR role = 'admin'");
    $stmt->execute();
    $result = $stmt->get_result();
    $admins = $result->fetch_all(MYSQLI_ASSOC);
    
    if (empty($admins)) {
        echo "❌ No admin user found<br>";
    } else {
        echo "✅ Found " . count($admins) . " admin user(s):<br>";
        foreach ($admins as $admin) {
            echo "- ID: {$admin['id']}, Username: {$admin['username']}, Email: {$admin['email']}, Role: {$admin['role']}<br>";
        }
    }
    
    echo "<h3>2. Fix/Create Admin User</h3>";
    
    // Check if we need to create or update
    $adminExists = false;
    foreach ($admins as $admin) {
        if ($admin['username'] === 'admin') {
            $adminExists = true;
            break;
        }
    }
    
    if (!$adminExists) {
        // Create new admin user
        echo "Creating new admin user...<br>";
        $newPassword = password_hash('admin123', PASSWORD_BCRYPT);
        $stmt = $mysqli->prepare("INSERT INTO users (username, email, password, role) VALUES ('admin', 'admin@drms.com', ?, 'admin')");
        $stmt->bind_param('s', $newPassword);
        
        if ($stmt->execute()) {
            echo "✅ Admin user created successfully!<br>";
        } else {
            echo "❌ Failed to create admin user: " . $stmt->error . "<br>";
        }
    } else {
        // Update existing admin user password
        echo "Updating existing admin user password...<br>";
        $newPassword = password_hash('admin123', PASSWORD_BCRYPT);
        $stmt = $mysqli->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
        $stmt->bind_param('s', $newPassword);
        
        if ($stmt->execute()) {
            echo "✅ Admin password updated successfully!<br>";
        } else {
            echo "❌ Failed to update admin password: " . $stmt->error . "<br>";
        }
    }
    
    echo "<h3>3. Test Admin Login</h3>";
    echo '<button onclick="testAdminLogin()" style="padding: 10px; margin: 10px;">Test Admin Login</button>';
    echo '<div id="login-test-result" style="margin: 10px; padding: 10px; border: 1px solid #ccc;"></div>';
    
    echo '<script>
    function testAdminLogin() {
        const resultDiv = document.getElementById("login-test-result");
        resultDiv.innerHTML = "Testing admin login...";
        
        fetch("/src/backend/api/login.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ username: "admin", password: "admin123" })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                resultDiv.innerHTML = "✅ Admin login successful!<br>User: " + JSON.stringify(data.user, null, 2);
                resultDiv.style.color = "green";
            } else {
                resultDiv.innerHTML = "❌ Admin login failed: " + (data.error || "Unknown error");
                resultDiv.style.color = "red";
            }
        })
        .catch(error => {
            resultDiv.innerHTML = "❌ Error: " + error.message;
            resultDiv.style.color = "red";
        });
    }
    </script>';
    
    echo "<hr>";
    echo "<h3>4. Verification</h3>";
    echo "After running this tool:<br>";
    echo "• Username: <strong>admin</strong><br>";
    echo "• Password: <strong>admin123</strong><br>";
    echo "• Role: <strong>admin</strong><br>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
