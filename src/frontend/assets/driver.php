<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'driver') {
    header('Location: login.php');
    exit;
}
// Redirect admins and residents to their dashboards
if ($_SESSION['role'] === 'admin') {
    header('Location: admin.php');
    exit;
} elseif ($_SESSION['role'] === 'resident') {
    header('Location: home.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Dashboard - DRMS</title>
    <link rel="stylesheet" href="../css/drm-styles.css">
    <link rel="icon" href="../images/logo.png">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        .driver-container {
            max-width: 1200px;
            margin: 120px auto 40px;
            padding: 0 2rem;
        }
        
        .driver-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .driver-header h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: #fff;
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .notification-section {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
        }
        
        .notification-section h2 {
            color: #2e7d32;
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #2e7d32;
            font-weight: 600;
        }
        
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 1rem;
            border: 2px solid rgba(76, 175, 80, 0.3);
            border-radius: 10px;
            font-size: 1rem;
            background: white;
            transition: all 0.3s ease;
        }
        
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #4caf50;
            box-shadow: 0 0 10px rgba(76, 175, 80, 0.2);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #4caf50, #45a049);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(76, 175, 80, 0.4);
        }
        
        .tasks-section {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
            grid-column: 1 / -1;
        }
        
        .tasks-section h2 {
            color: #2e7d32;
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
        }
        
        /* Tasks Table Styling */
        .tasks-section table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .tasks-section table th {
            background: linear-gradient(135deg, #2e7d32, #4caf50);
            color: white;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            font-size: 0.95rem;
            border: none;
        }
        
        .tasks-section table td {
            padding: 1rem;
            border-bottom: 1px solid #e0e0e0;
            color: #333;
            font-size: 0.9rem;
            vertical-align: top;
        }
        
        .tasks-section table tr:last-child td {
            border-bottom: none;
        }
        
        .tasks-section table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .tasks-section table tr:hover {
            background-color: #f0f8f0;
            transition: background-color 0.2s ease;
        }
        
        /* Task Status Select Styling */
        .tasks-section select {
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: white;
            color: #333;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .tasks-section select:focus {
            outline: none;
            border-color: #4caf50;
            box-shadow: 0 0 5px rgba(76, 175, 80, 0.3);
        }
        
        /* Task Action Buttons */
        .tasks-section button {
            padding: 0.5rem 1rem;
            margin: 0.2rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.8rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .tasks-section button:first-of-type {
            background: #2196f3;
            color: white;
        }
        
        .tasks-section button:first-of-type:hover {
            background: #1976d2;
            transform: translateY(-1px);
        }
        
        .tasks-section button:last-of-type {
            background: #ff9800;
            color: white;
        }
        
        .tasks-section button:last-of-type:hover {
            background: #f57c00;
            transform: translateY(-1px);
        }
        
        /* No tasks message styling */
        .tasks-section p {
            color: #666;
            font-style: italic;
            text-align: center;
            padding: 2rem;
            margin: 0;
        }
        
        .map-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
            margin-top: 2rem;
        }
        
        .map-container h2 {
            color: #2e7d32;
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
        }
        
        #map {
            height: 400px;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .feedback {
            margin-top: 1rem;
            padding: 1rem;
            border-radius: 10px;
            display: none;
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
        
        @media (max-width: 768px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .driver-container {
                padding: 0 1rem;
            }
            
            /* Mobile table responsiveness */
            .tasks-section table {
                font-size: 0.8rem;
                border-radius: 5px;
            }
            
            .tasks-section table th,
            .tasks-section table td {
                padding: 0.75rem 0.5rem;
            }
            
            .tasks-section table th {
                font-size: 0.85rem;
            }
            
            .tasks-section button {
                padding: 0.4rem 0.8rem;
                font-size: 0.75rem;
                margin: 0.1rem;
            }
            
            .tasks-section select {
                font-size: 0.8rem;
                padding: 0.4rem;
            }
        }
        
        /* Route info control styles */
        .route-info {
            background: white !important;
            padding: 10px !important;
            border-radius: 5px !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1) !important;
            font-family: Arial, sans-serif !important;
            font-size: 12px !important;
            max-width: 200px !important;
        }
        
        /* Map marker and route styles */
        .leaflet-popup-content {
            margin: 8px 12px !important;
            line-height: 1.4 !important;
        }
        
        .leaflet-popup-content strong {
            color: #2c3e50 !important;
        }
        
        /* Notification Modal Styles */
        .modal {
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(5px);
        }
        
        .modal-content {
            background: white;
            margin: 5% auto;
            padding: 0;
            border-radius: 15px;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: modalSlideIn 0.3s ease;
        }
        
        @keyframes modalSlideIn {
            from { opacity: 0; transform: translateY(-50px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .modal-header {
            background: linear-gradient(135deg, #2e7d32, #4caf50);
            color: white;
            padding: 1.5rem;
            border-radius: 15px 15px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-header h3 {
            margin: 0;
            font-size: 1.3rem;
        }
        
        .close-modal {
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 50%;
            transition: background-color 0.2s;
        }
        
        .close-modal:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }
        
        .modal-body {
            padding: 2rem;
        }
        
        .notification-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .user-info h4 {
            color: #2e7d32;
            margin: 0 0 1rem 0;
            font-size: 1.1rem;
        }
        
        .user-info p {
            margin: 0.5rem 0;
            color: #666;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .message-templates h4 {
            color: #333;
            margin-bottom: 1rem;
            font-size: 1rem;
        }
        
        .template-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }
        
        .template-btn {
            padding: 0.75rem 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            background: white;
            color: #333;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-align: left;
        }
        
        .template-btn:hover {
            border-color: #4caf50;
            background: #f8fdf8;
            transform: translateY(-1px);
        }
        
        .template-btn.selected {
            border-color: #4caf50;
            background: #4caf50;
            color: white;
        }
        
        .message-composer {
            margin-bottom: 1.5rem;
        }
        
        .message-composer label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 600;
        }
        
        .message-composer textarea {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-family: inherit;
            font-size: 0.9rem;
            resize: vertical;
            transition: border-color 0.2s;
        }
        
        .message-composer textarea:focus {
            outline: none;
            border-color: #4caf50;
        }
        
        .character-count {
            text-align: right;
            margin-top: 0.5rem;
            font-size: 0.8rem;
            color: #666;
        }
        
        .character-count.over-limit {
            color: #f44336;
        }
        
        .notification-options {
            margin-bottom: 1rem;
        }
        
        .option-group {
            display: flex;
            gap: 2rem;
        }
        
        .option-group label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            color: #333;
            font-size: 0.9rem;
        }
        
        .option-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #4caf50;
        }
        
        .modal-footer {
            padding: 1.5rem 2rem;
            border-top: 1px solid #e0e0e0;
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-1px);
        }
        
        /* Mobile modal adjustments */
        @media (max-width: 768px) {
            .modal-content {
                width: 95%;
                margin: 2% auto;
            }
            
            .template-buttons {
                grid-template-columns: 1fr;
            }
            
            .option-group {
                flex-direction: column;
                gap: 1rem;
            }
            
            .modal-footer {
                flex-direction: column;
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
                <li><a href="home.php" class="nav-link"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="driver_send_sms.php" class="nav-link"><i class="fas fa-sms"></i> Send SMS</a></li>
                <li><a href="#" onclick="confirmLogout()" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
    </nav>
    
    <div class="driver-container">
        <div class="driver-header">
            <h1><i class="fas fa-truck"></i> Driver Dashboard</h1>
            <p>Manage your tasks and communicate with users</p>
        </div>
        
        <div class="dashboard-grid">
            <!-- Notification Form -->
            <div class="notification-section">
                <h2><i class="fas fa-bell"></i> Send Notification</h2>
                <form id="notify-user-form">
                    <div class="form-group">
                        <label for="receiver_id"><i class="fas fa-user"></i> Select User:</label>
                        <select id="receiver_id" name="receiver_id" required>
                            <option value="">Choose a user...</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="message"><i class="fas fa-message"></i> Message:</label>
                        <textarea id="message" name="message" rows="4" placeholder="Enter your message here..." required></textarea>
                    </div>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-paper-plane"></i> Send Notification
                    </button>
                </form>
                <div id="notify-feedback" class="feedback"></div>
            </div>
            
            <div class="notification-section">
                <h2><i class="fas fa-info-circle"></i> Quick Stats</h2>
                <div style="text-align: center; padding: 2rem;">
                    <div style="font-size: 2rem; color: #4caf50; margin-bottom: 1rem;">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <p style="color: #666; font-size: 1.1rem;">Your assigned tasks will appear below</p>
                </div>
            </div>
        </div>
        
        <div class="tasks-section">
            <h2><i class="fas fa-clipboard-list"></i> My Tasks</h2>
            <div id="tasks-container">
                <!-- Tasks will be loaded here -->
            </div>
        </div>
        
        <div class="map-container">
            <h2><i class="fas fa-map-marked-alt"></i> Route Map</h2>
            <div id="map"></div>
        </div>
    </div>
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script src="../js/driver.js"></script>
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

    // Fetch users for the dropdown
    fetch('../../backend/api/get_users.php')
        .then(res => res.json())
        .then(users => {
            const select = document.getElementById('receiver_id');
            users.forEach(u => {
                const opt = document.createElement('option');
                opt.value = u.id;
                opt.textContent = `${u.username} (${u.email})`;
                select.appendChild(opt);
            });
        })
        .catch(error => {
            console.error('Error loading users:', error);
        });

    // Handle notification form submit
    document.getElementById('notify-user-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const receiver_id = document.getElementById('receiver_id').value;
        const message = document.getElementById('message').value;
        const feedback = document.getElementById('notify-feedback');
        
        // Show loading state
        feedback.style.display = 'block';
        feedback.className = 'feedback';
        feedback.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending notification...';
        
        fetch('../../backend/api/send_notification.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ receiver_id, message })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                feedback.className = 'feedback success';
                feedback.innerHTML = '<i class="fas fa-check-circle"></i> Notification sent successfully!';
                document.getElementById('notify-user-form').reset();
            } else {
                feedback.className = 'feedback error';
                feedback.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${data.error || 'Failed to send notification.'}`;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            feedback.className = 'feedback error';
            feedback.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Error sending notification. Please try again.';
        });
    });
    </script>
    
    <!-- Notification Modal -->
    <div id="notificationModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-comment-dots"></i> Send Notification</h3>
                <span class="close-modal" onclick="closeNotificationModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="notification-info">
                    <div class="user-info">
                        <h4 id="modalUserName"></h4>
                        <p><i class="fas fa-phone"></i> <span id="modalUserPhone"></span></p>
                        <p><i class="fas fa-map-marker-alt"></i> <span id="modalUserAddress"></span></p>
                    </div>
                </div>
                
                <div class="message-templates">
                    <h4><i class="fas fa-templates"></i> Quick Templates</h4>
                    <div class="template-buttons">
                        <button class="template-btn" onclick="selectTemplate('missed')">
                            <i class="fas fa-clock"></i> Missed Collection
                        </button>
                        <button class="template-btn" onclick="selectTemplate('delayed')">
                            <i class="fas fa-hourglass-half"></i> Delayed Collection
                        </button>
                        <button class="template-btn" onclick="selectTemplate('rescheduled')">
                            <i class="fas fa-calendar-alt"></i> Rescheduled
                        </button>
                        <button class="template-btn" onclick="selectTemplate('completed')">
                            <i class="fas fa-check-circle"></i> Completed
                        </button>
                        <button class="template-btn" onclick="selectTemplate('issue')">
                            <i class="fas fa-exclamation-triangle"></i> Collection Issue
                        </button>
                        <button class="template-btn" onclick="selectTemplate('custom')">
                            <i class="fas fa-edit"></i> Custom Message
                        </button>
                    </div>
                </div>
                
                <div class="message-composer">
                    <label for="notificationMessage"><i class="fas fa-envelope"></i> Message</label>
                    <textarea id="notificationMessage" rows="5" placeholder="Select a template or write your custom message..."></textarea>
                    <div class="character-count">
                        <span id="charCount">0</span>/200 characters
                    </div>
                </div>
                
                <div class="notification-options">
                    <div class="option-group">
                        <label>
                            <input type="checkbox" id="sendSMS" checked>
                            <i class="fas fa-sms"></i> Send SMS
                        </label>
                        <label>
                            <input type="checkbox" id="sendInApp" checked>
                            <i class="fas fa-bell"></i> In-App Notification
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-secondary" onclick="closeNotificationModal()">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button class="btn-primary" onclick="sendCustomNotification()">
                    <i class="fas fa-paper-plane"></i> Send Notification
                </button>
            </div>
        </div>
    </div>
</body>
</html>