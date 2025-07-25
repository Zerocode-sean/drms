<?php
// Debug login redirect issue
// Visit: https://drms-app.onrender.com/debug_login.php

echo "<h2>🔍 Login Debug Information</h2>";
echo "<hr>";

// Check if we can access the login API
echo "<h3>1. Testing Login API Access</h3>";
$login_api_url = "/src/backend/api/login.php";
if (file_exists(__DIR__ . $login_api_url)) {
    echo "✅ Login API exists at: " . __DIR__ . $login_api_url . "<br>";
} else {
    echo "❌ Login API not found at: " . __DIR__ . $login_api_url . "<br>";
}

// Check if admin page exists
echo "<h3>2. Testing Admin Page Access</h3>";
$admin_page_url = "/src/frontend/assets/admin.php";
if (file_exists(__DIR__ . $admin_page_url)) {
    echo "✅ Admin page exists at: " . __DIR__ . $admin_page_url . "<br>";
} else {
    echo "❌ Admin page not found at: " . __DIR__ . $admin_page_url . "<br>";
}

// Check current URL structure
echo "<h3>3. Current URL Information</h3>";
echo "Request URI: " . $_SERVER['REQUEST_URI'] . "<br>";
echo "Script Name: " . $_SERVER['SCRIPT_NAME'] . "<br>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Current Dir: " . __DIR__ . "<br>";

// Test redirect URLs
echo "<h3>4. Test Redirect URLs</h3>";
echo '<a href="/src/frontend/assets/admin.php" target="_blank">Test Admin Page Link</a><br>';
echo '<a href="/src/frontend/assets/driver.php" target="_blank">Test Driver Page Link</a><br>';
echo '<a href="/src/frontend/assets/home.php" target="_blank">Test Home Page Link</a><br>';

echo "<hr>";
echo "<h3>5. Manual Login Test</h3>";
echo '<form id="test-login" style="margin: 20px 0;">
    <input type="text" placeholder="Username" value="admin" style="margin: 5px; padding: 8px;"><br>
    <input type="password" placeholder="Password" value="admin123" style="margin: 5px; padding: 8px;"><br>
    <button type="button" onclick="testLogin()" style="margin: 5px; padding: 8px;">Test Login</button>
    <div id="login-result" style="margin: 10px 0; padding: 10px; border: 1px solid #ccc;"></div>
</form>';

echo '<script>
function testLogin() {
    const resultDiv = document.getElementById("login-result");
    resultDiv.innerHTML = "Testing login...";
    
    fetch("/src/backend/api/login.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ username: "admin", password: "admin123" })
    })
    .then(response => {
        console.log("Response status:", response.status);
        return response.json();
    })
    .then(data => {
        console.log("Response data:", data);
        resultDiv.innerHTML = "Login Response: " + JSON.stringify(data, null, 2);
        
        if (data.success && data.user && data.user.role === "admin") {
            resultDiv.innerHTML += "<br><br>✅ Login successful! Role: " + data.user.role;
            resultDiv.innerHTML += "<br>🔄 Testing redirect to admin page...";
            
            // Test redirect
            setTimeout(() => {
                window.location.href = "/src/frontend/assets/admin.php";
            }, 2000);
        }
    })
    .catch(error => {
        console.error("Login error:", error);
        resultDiv.innerHTML = "Error: " + error.message;
    });
}
</script>';

echo "<br><p><strong>Instructions:</strong></p>";
echo "<ol>";
echo "<li>Click the test links above to see if pages load</li>";
echo "<li>Use the manual login test to see the exact response</li>";
echo "<li>Check browser console for JavaScript errors</li>";
echo "</ol>";
?>
