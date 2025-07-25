<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'driver') {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="wid                            <span id="char-count">0</span>/200 charactersh=device-width, initial-scale=1.0">
    <title>Send SMS - DRMS</title>
    <link rel="stylesheet" href="../css/drm-styles.css">
    <link rel="icon" href="../images/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        .sms-container {
            max-width: 800px;
            margin: 120px auto 40px;
            padding: 0 2rem;
        }
        
        .sms-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .sms-header h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: #fff;
        }
        
        .sms-header p {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.9);
        }
        
        .sms-form-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
        }
        
        .form-grid {
            display: grid;
            gap: 1.5rem;
        }
        
        .form-group {
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #2e7d32;
            font-weight: 600;
            font-size: 1rem;
        }
        
        .form-group label i {
            margin-right: 0.5rem;
            color: #4caf50;
        }
        
        .form-control {
            width: 100%;
            padding: 1rem;
            border: 2px solid rgba(76, 175, 80, 0.3);
            border-radius: 10px;
            font-size: 1rem;
            background: white;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #4caf50;
            box-shadow: 0 0 10px rgba(76, 175, 80, 0.2);
        }
        
        select.form-control {
            cursor: pointer;
        }
        
        /* Style for disabled options (users without phone numbers) */
        select.form-control option:disabled {
            color: #999 !important;
            font-style: italic !important;
            background-color: #f5f5f5 !important;
        }
        
        /* Add visual separator between enabled and disabled options */
        select.form-control optgroup {
            font-weight: bold;
            color: #333;
        }
        
        textarea.form-control {
            resize: vertical;
            min-height: 120px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #4caf50, #45a049);
            color: white;
            border: none;
            padding: 1rem 3rem;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            justify-content: center;
            margin: 2rem auto 0;
            max-width: 200px;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(76, 175, 80, 0.4);
        }
        
        .btn-primary:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .feedback {
            margin-top: 1.5rem;
            padding: 1rem;
            border-radius: 10px;
            display: none;
            text-align: center;
            font-weight: 500;
        }
        
        .feedback.success {
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #4caf50;
        }
        
        .feedback.error {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #f44336;
        }
        
        .feedback.loading {
            background: #e3f2fd;
            color: #1976d2;
            border: 1px solid #2196f3;
        }
        
        .feature-highlights {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .feature-card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .feature-card i {
            font-size: 2rem;
            color: #4caf50;
            margin-bottom: 1rem;
        }
        
        .feature-card h3 {
            color: white;
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
        }
        
        .feature-card p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
        }
        
        @media (max-width: 768px) {
            .sms-container {
                padding: 0 1rem;
            }
            
            .sms-header h1 {
                font-size: 2rem;
            }
            
            .feature-highlights {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <img src="../images/logo.png" alt="DRMS Logo" class="logo-img">
                <h2>DRMS</h2>
            </div>
            <ul class="nav-menu">
                <li><a href="driver.php" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="home.php" class="nav-link"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="driver_send_sms.php" class="nav-link" style="color: #fff;"><i class="fas fa-sms"></i> Send SMS</a></li>
                <li><a href="sms_logs.php" class="nav-link"><i class="fas fa-history"></i> SMS Logs</a></li>
                <li><a href="#" onclick="confirmLogout()" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
    </nav>
    
    <div class="sms-container">
        <div class="sms-header">
            <h1><i class="fas fa-paper-plane"></i> Send SMS Notification</h1>
            <p>Send instant SMS messages to residents about waste collection updates via BlessedText SMS</p>
        </div>
        
        <div class="sms-form-container">
            <form id="send-sms-form">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="sms-user">
                            <i class="fas fa-user"></i> Select Recipient
                        </label>
                        <select id="sms-user" name="user_id" class="form-control" required>
                            <option value="">Choose a resident...</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="sms-message">
                            <i class="fas fa-comment"></i> Message Content
                        </label>
                        <textarea 
                            id="sms-message" 
                            name="message" 
                            class="form-control" 
                            placeholder="Type your SMS message here... (max 200 characters recommended)"
                            required
                        ></textarea>
                        <small style="color: #666; margin-top: 0.5rem; display: block;">
                            <span id="char-count">0</span>/200 characters
                        </small>
                    </div>
                </div>
                
                <button type="submit" class="btn-primary" id="send-btn">
                    <i class="fas fa-paper-plane"></i>
                    <span id="btn-text">Send SMS</span>
                    <i class="fas fa-spinner fa-spin" id="btn-loading" style="display: none;"></i>
                </button>
            </form>
            
            <div id="sms-feedback" class="feedback"></div>
        </div>
        
        <div class="feature-highlights">
            <div class="feature-card">
                <i class="fas fa-bolt"></i>
                <h3>Instant Delivery</h3>
                <p>Messages are delivered instantly to residents' mobile phones</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-shield-alt"></i>
                <h3>Secure & Reliable</h3>
                <p>Messages are sent through BlessedText API with high delivery rate</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-history"></i>
                <h3>Message History</h3>
                <p>All sent messages are logged with delivery status and timestamps</p>
            </div>
        </div>
    </div>
    <script>
    function confirmLogout() {
        if (confirm('Are you sure you want to logout?')) {
            logout();
        }
    }
    
    function logout() {
        fetch('../../backend/api/logout.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = 'login.php';
            }
        })
        .catch(error => {
            console.error('Logout error:', error);
            window.location.href = 'login.php';
        });
    }

    // Character counter for SMS message
    const messageTextarea = document.getElementById('sms-message');
    const charCount = document.getElementById('char-count');
    
    messageTextarea.addEventListener('input', function() {
        const length = this.value.length;
        charCount.textContent = length;
        
        // Change color based on character count
        if (length > 200) {
            charCount.style.color = '#f44336';
        } else if (length > 180) {
            charCount.style.color = '#ff9800';
        } else {
            charCount.style.color = '#4caf50';
        }
    });

    // Populate user dropdown
    fetch('../../backend/api/get_users.php?role=resident')
        .then(res => res.json())
        .then(users => {
            const select = document.getElementById('sms-user');
            users.forEach(u => {
                const opt = document.createElement('option');
                opt.value = u.id;
                // Show username and phone number instead of email
                const phoneDisplay = u.phone ? u.phone : 'No phone';
                opt.textContent = `${u.username} (${phoneDisplay})`;
                
                // Disable users without phone numbers and add visual indicator
                if (!u.phone) {
                    opt.disabled = true;
                    opt.style.color = '#999';
                    opt.style.fontStyle = 'italic';
                    opt.textContent = `${u.username} (No phone - cannot send SMS)`;
                }
                
                select.appendChild(opt);
            });
        })
        .catch(error => {
            console.error('Error loading users:', error);
            showFeedback('error', 'Failed to load user list. Please refresh the page.');
        });

    // Handle form submit
    document.getElementById('send-sms-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const user_id = document.getElementById('sms-user').value;
        const message = document.getElementById('sms-message').value;
        const sendBtn = document.getElementById('send-btn');
        const btnText = document.getElementById('btn-text');
        const btnLoading = document.getElementById('btn-loading');
        
        // Validation
        if (!user_id) {
            showFeedback('error', 'Please select a recipient.');
            return;
        }
        
        if (!message.trim()) {
            showFeedback('error', 'Please enter a message.');
            return;
        }
        
        // Show loading state
        sendBtn.disabled = true;
        btnText.style.display = 'none';
        btnLoading.style.display = 'inline-block';
        showFeedback('loading', 'Sending SMS message...');
        
        fetch('../../backend/api/send_sms_v2.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ user_id, message })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showFeedback('success', `SMS sent successfully! The message has been delivered to ${data.recipient}. <br><a href="sms_logs.php" style="color: #2e7d32; text-decoration: underline;">View SMS logs</a> to see all sent messages.`);
                document.getElementById('send-sms-form').reset();
                charCount.textContent = '0';
                charCount.style.color = '#4caf50';
            } else {
                showFeedback('error', data.error || 'Failed to send SMS. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showFeedback('error', 'Network error occurred. Please check your connection and try again.');
        })
        .finally(() => {
            // Reset button state
            sendBtn.disabled = false;
            btnText.style.display = 'inline';
            btnLoading.style.display = 'none';
        });
    });
    
    function showFeedback(type, message) {
        const feedback = document.getElementById('sms-feedback');
        feedback.className = `feedback ${type}`;
        feedback.style.display = 'block';
        
        let icon = '';
        switch(type) {
            case 'success':
                icon = '<i class="fas fa-check-circle"></i> ';
                break;
            case 'error':
                icon = '<i class="fas fa-exclamation-circle"></i> ';
                break;
            case 'loading':
                icon = '<i class="fas fa-spinner fa-spin"></i> ';
                break;
        }
        
        feedback.innerHTML = icon + message;
        
        // Auto-hide success messages after 5 seconds
        if (type === 'success') {
            setTimeout(() => {
                feedback.style.display = 'none';
            }, 5000);
        }
    }
    </script>
</body>
</html>