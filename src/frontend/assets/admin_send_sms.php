<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send SMS - DRMS Admin</title>
    <link rel="stylesheet" href="/src/frontend/css/drm-styles.css">
    <link rel="icon" href="/src/frontend/images/logo.png">
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
            display: flex;
            flex-direction: column;
        }
        
        .form-group label {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #333;
            font-size: 1rem;
        }
        
        .form-group select,
        .form-group textarea {
            padding: 0.75rem;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            font-family: inherit;
        }
        
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #4caf50;
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
        }
        
        /* Style for disabled options (users without phone numbers) */
        .form-group select option:disabled {
            color: #999 !important;
            font-style: italic !important;
            background-color: #f5f5f5 !important;
        }
        
        .textarea-container {
            position: relative;
        }
        
        .char-counter {
            position: absolute;
            bottom: 0.5rem;
            right: 0.75rem;
            font-size: 0.8rem;
            color: #4caf50;
            font-weight: 600;
        }
        
        .sms-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }
        
        .btn-send {
            background: linear-gradient(135deg, #4caf50, #45a049);
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex: 1;
            justify-content: center;
        }
        
        .btn-send:hover:not(:disabled) {
            background: linear-gradient(135deg, #45a049, #3d8b40);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.3);
        }
        
        .btn-send:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }
        
        .btn-logs {
            background: linear-gradient(135deg, #2196f3, #1976d2);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }
        
        .btn-logs:hover {
            background: linear-gradient(135deg, #1976d2, #1565c0);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(33, 150, 243, 0.3);
            color: white;
            text-decoration: none;
        }
        
        .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid transparent;
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .feedback {
            padding: 1rem;
            border-radius: 10px;
            margin-top: 1rem;
            display: none;
            font-weight: 500;
        }
        
        .feedback.success {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .feedback.error {
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .feedback.loading {
            background: linear-gradient(135deg, #d1ecf1, #bee5eb);
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        
        .admin-nav {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1000;
        }
        
        .nav-btn {
            background: rgba(255, 255, 255, 0.9);
            border: none;
            padding: 0.75rem 1rem;
            border-radius: 10px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            color: #333;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .nav-btn:hover {
            background: rgba(255, 255, 255, 1);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
            color: #333;
            text-decoration: none;
        }
        
        .logout-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            background: rgba(244, 67, 54, 0.9);
            color: white;
            border: none;
            padding: 0.75rem 1rem;
            border-radius: 10px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            z-index: 1000;
        }
        
        .logout-btn:hover {
            background: rgba(244, 67, 54, 1);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(244, 67, 54, 0.3);
        }
        
        .feature-highlight {
            background: linear-gradient(135deg, #e8f5e8, #f0f8f0);
            border: 2px solid #4caf50;
            border-radius: 15px;
            padding: 1.5rem;
            margin: 1rem 0;
            text-align: center;
        }
        
        .feature-highlight h3 {
            color: #2e7d32;
            margin-bottom: 0.5rem;
            font-size: 1.2rem;
        }
        
        .feature-highlight p {
            color: #388e3c;
            margin: 0;
            font-size: 0.95rem;
        }
        
        @media (max-width: 768px) {
            .sms-container {
                margin: 100px 1rem 20px;
                padding: 0 1rem;
            }
            
            .sms-header h1 {
                font-size: 2rem;
            }
            
            .sms-form-container {
                padding: 1.5rem;
            }
            
            .sms-actions {
                flex-direction: column;
            }
            
            .admin-nav,
            .logout-btn {
                position: static;
                margin: 1rem;
                width: fit-content;
            }
        }
    </style>
</head>
<body>
    <!-- Admin Navigation -->
    <div class="admin-nav">
        <a href="admin.php" class="nav-btn">
            <i class="fas fa-arrow-left"></i>
            Back to Dashboard
        </a>
    </div>
    
    <!-- Logout Button -->
    <button class="logout-btn" onclick="handleLogout()">
        <i class="fas fa-sign-out-alt"></i>
        Logout
    </button>
    
    <div class="sms-container">
        <div class="sms-header">
            <h1><i class="fas fa-sms"></i> Send SMS Notification</h1>
            <p>Send important notifications and updates to residents via SMS</p>
        </div>
        
        <div class="feature-highlight">
            <h3><i class="fas fa-shield-alt"></i> Powered by BlessedText SMS</h3>
            <p>Reliable SMS delivery across Kenya with real-time status tracking and logging</p>
        </div>
        
        <div class="sms-form-container">
            <form id="send-sms-form" class="form-grid">
                <div class="form-group">
                    <label for="sms-user">
                        <i class="fas fa-user"></i>
                        Select Recipient
                    </label>
                    <select id="sms-user" name="user_id" required>
                        <option value="">Choose a resident...</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="sms-message">
                        <i class="fas fa-comment"></i>
                        Message Content
                    </label>
                    <div class="textarea-container">
                        <textarea 
                            id="sms-message" 
                            name="message" 
                            rows="4" 
                            placeholder="Type your SMS message here... (recommended: keep under 200 characters)"
                            required
                            maxlength="500"></textarea>
                        <div class="char-counter">
                            <span id="char-count">0</span>/200
                        </div>
                    </div>
                </div>
                
                <div class="sms-actions">
                    <button type="submit" class="btn-send" id="send-btn">
                        <span id="btn-text">
                            <i class="fas fa-paper-plane"></i>
                            Send SMS
                        </span>
                        <div class="spinner" id="btn-loading"></div>
                    </button>
                    <a href="sms_logs.php" class="btn-logs">
                        <i class="fas fa-history"></i>
                        View SMS Logs
                    </a>
                </div>
            </form>
            
            <div id="sms-feedback" class="feedback"></div>
        </div>
    </div>

    <script>
    // Handle logout functionality
    function handleLogout() {
        if (confirm('Are you sure you want to logout?')) {
            fetch('../../backend/api/logout.php', { method: 'POST' })
            .then(() => window.location.href = 'login.php')
            .catch(error => {
                console.error('Logout error:', error);
                window.location.href = 'login.php';
            });
        }
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
        showFeedback('loading', 'Sending SMS notification...');
        
        fetch('../../backend/api/send_sms_v2.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ user_id, message })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showFeedback('success', `SMS notification sent successfully! The message has been delivered to ${data.recipient}. <br><a href="sms_logs.php" style="color: #2e7d32; text-decoration: underline;">View SMS logs</a> to see all sent messages.`);
                document.getElementById('send-sms-form').reset();
                charCount.textContent = '0';
                charCount.style.color = '#4caf50';
            } else {
                showFeedback('error', data.error || 'Failed to send SMS notification. Please try again.');
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