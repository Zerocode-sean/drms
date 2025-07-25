<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'driver'])) {
    header('Location: login.php');
    exit();
}

// Add cache-busting headers to ensure fresh content
header('Cache-Control: no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

// No need for mock SMS config anymore - using real SMS gateway manager
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>SMS Logs - DRMS</title>
    <link rel="stylesheet" href="../css/drm-styles.css">
    <link rel="icon" href="../images/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        .logs-container {
            max-width: 1200px;
            margin: 120px auto 40px;
            padding: 0 2rem;
        }
        
        .logs-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .logs-header h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: #fff;
        }
        
        .logs-content {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
        }
        
        .logs-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #4caf50, #45a049);
            color: white;
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #6c757d, #5a6268);
            color: white;
            box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #f44336, #d32f2f);
            color: white;
            box-shadow: 0 4px 15px rgba(244, 67, 54, 0.3);
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
        }
        
        .logs-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        
        .logs-table th {
            background: #2e7d32;
            color: white;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
        }
        
        .logs-table td {
            padding: 1rem;
            border-bottom: 1px solid #e0e0e0;
            vertical-align: top;
        }
        
        .logs-table tr:hover {
            background: #f5f5f5;
        }
        
        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-sent {
            background: #e8f5e9;
            color: #2e7d32;
        }
        
        .status-failed {
            background: #ffebee;
            color: #c62828;
        }
        
        .message-preview {
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #666;
        }
        
        .empty-state i {
            font-size: 3rem;
            color: #ccc;
            margin-bottom: 1rem;
        }
        
        .info-card {
            background: #e3f2fd;
            border: 1px solid #2196f3;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .info-card h3 {
            color: #1976d2;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .info-card p {
            color: #1976d2;
            margin: 0;
        }
        
        @media (max-width: 768px) {
            .logs-container {
                padding: 0 1rem;
            }
            
            .logs-actions {
                flex-direction: column;
                align-items: stretch;
            }
            
            .logs-table {
                font-size: 0.9rem;
            }
            
            .logs-table th,
            .logs-table td {
                padding: 0.5rem;
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
                <?php if ($_SESSION['role'] === 'driver'): ?>
                    <li><a href="driver.php" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="driver_send_sms.php" class="nav-link"><i class="fas fa-sms"></i> Send SMS</a></li>
                <?php else: ?>
                    <li><a href="admin.php" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="admin_send_sms.php" class="nav-link"><i class="fas fa-sms"></i> Send SMS</a></li>
                <?php endif; ?>
                <li><a href="home.php" class="nav-link"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="#" onclick="confirmLogout()" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
    </nav>
    
    <div class="logs-container">
        <div class="logs-header">
            <h1><i class="fas fa-history"></i> SMS Logs</h1>
            <p>View all sent SMS messages via BlessedText SMS Gateway</p>
        </div>
        
        <div class="logs-content">
            <div class="info-card">
                <h3><i class="fas fa-satellite-dish"></i> BlessedText SMS Service</h3>
                <p>All SMS messages are sent via <strong>BlessedText API</strong> to real phone numbers. Messages are logged here for tracking and accountability.</p>
                <small class="text-muted">Last updated: <?php echo date('Y-m-d H:i:s'); ?></small>
            </div>
            
            <div class="logs-actions">
                <div>
                    <button class="btn btn-primary" onclick="refreshLogs()">
                        <i class="fas fa-refresh"></i> Refresh
                    </button>
                    <button class="btn btn-secondary" onclick="exportLogs()">
                        <i class="fas fa-download"></i> Export
                    </button>
                </div>
                <button class="btn btn-danger" onclick="clearLogs()">
                    <i class="fas fa-trash"></i> Clear All Logs
                </button>
            </div>
            
            <div id="logs-content">
                <!-- Logs will be loaded here -->
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
        fetch('../backend/api/logout.php', {
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
    
    function loadLogs() {
        fetch('../backend/api/get_sms_logs.php')
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById('logs-content');
                
                if (data.success && data.logs && data.logs.length > 0) {
                    let html = `
                        <table class="logs-table">
                            <thead>
                                <tr>
                                    <th>Date/Time</th>
                                    <th>Recipient</th>
                                    <th>Phone Number</th>
                                    <th>Message</th>
                                    <th>Status</th>
                                    <th>Sent By</th>
                                </tr>
                            </thead>
                            <tbody>
                    `;
                    
                    data.logs.forEach(log => {
                        html += `
                            <tr>
                                <td>${log.timestamp}</td>
                                <td>${log.recipient_name || 'Unknown'}</td>
                                <td>${log.to}</td>
                                <td>
                                    <div class="message-preview" title="${log.message}">
                                        ${log.message}
                                    </div>
                                </td>
                                <td>
                                    <span class="status-badge status-${log.status}">
                                        ${log.status.toUpperCase()}
                                    </span>
                                    ${log.error ? `<br><small style="color: #f44336;">${log.error}</small>` : ''}
                                </td>
                                <td>${log.sent_by}</td>
                            </tr>
                        `;
                    });
                    
                    html += '</tbody></table>';
                    container.innerHTML = html;
                } else {
                    container.innerHTML = `
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <h3>No SMS logs found</h3>
                            <p>Send some SMS messages to see them here.</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error loading logs:', error);
                document.getElementById('logs-content').innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-exclamation-triangle"></i>
                        <h3>Error loading logs</h3>
                        <p>Please try refreshing the page.</p>
                    </div>
                `;
            });
    }
    
    function refreshLogs() {
        loadLogs();
    }
    
    function clearLogs() {
        if (confirm('Are you sure you want to clear all SMS logs? This action cannot be undone.')) {
            fetch('../backend/api/clear_sms_logs.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('SMS logs cleared successfully!');
                    loadLogs();
                } else {
                    alert('Error clearing logs: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error clearing logs. Please try again.');
            });
        }
    }
    
    function exportLogs() {
        window.open('../backend/api/export_sms_logs.php', '_blank');
    }
    
    // Load logs on page load
    document.addEventListener('DOMContentLoaded', loadLogs);
    </script>
</body>
</html>
