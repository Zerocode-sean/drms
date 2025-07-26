<?php
// Include performance optimizations
require_once __DIR__ . '/../../backend/config/performance.php';
require_once __DIR__ . '/../../backend/config/session.php';

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
    <title>DRMS Admin Dashboard</title>
    <link rel="stylesheet" href="/src/frontend/css/admin.css">
    <link rel="icon" href="/src/frontend/images/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="/src/frontend/js/admin.js" defer></script>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo">DRMS</div>
            <nav>
                <ul>
                    <li class="active"><a href="admin.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="admin_requests.php"><i class="fas fa-file-alt"></i>Request</a></li>
                    <li><a href="admin_users.php"><i class="fas fa-users"></i> Users</a></li>
                    <li><a href="admin_drivers.php"><i class="fas fa-truck"></i> Drivers</a></li>
                    <li><a href="admin_reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
                    <li><a href="admin_notifications.php"><i class="fas fa-bell"></i> Notifications</a></li>
                    <li><a href="admin_profile.php"><i class="fas fa-user"></i> Profile</a></li>
                    <li><a href="admin_settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                    <li><a href="admin_send_sms.php"><i class="fas fa-sms"></i> Send SMS</a></li>
                    <li id="logout-btn" style="cursor:pointer;"><i class="fas fa-sign-out-alt"></i> Logout</li>
                </ul>
            </nav>
        </aside>
        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header>
                <div class="search-bar">
                    <input type="text" placeholder="Search...">
                    <i class="fas fa-search"></i>
                </div>
                <h1>Admin Dashboard</h1>
                <div class="user-info">
                    <i class="fas fa-bell"></i>
                    <img src="/src/frontend/images/logo.png" alt="Admin Avatar">
                </div>
            </header>
            <!-- Dashboard Metrics -->
            <section class="metrics">
                <div class="card">
                    <h3 id="total-requests">250</h3>
                    <p>Total Requests</p>
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="card">
                    <h3 id="pending-approvals">15</h3>
                    <p>Pending Approvals</p>
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <div class="card">
                    <h3 id="active-drivers">50</h3>
                    <p>Active Drivers</p>
                    <i class="fas fa-truck"></i>
                </div>
                <div class="card">
                    <h3 id="active-users">120</h3>
                    <p>Active Users</p>
                    <i class="fas fa-users"></i>
                </div>
            </section>
            <!-- Recent Requests Table (Summary) -->
            <section class="recent-requests">
                <h2>Recent Requests <a href="admin_requests.php" class="view-all" style="float:right;">View All</a></h2>
                <table>
                    <thead>
                        <tr>
                            <th>Request ID</th>
                            <th>User</th>
                            <th>Document</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody id="requests-summary-body">
                        <!-- Populated by JS -->
                    </tbody>
                </table>
            </section>
            <!-- Task Management Section -->
            <section class="task-management">
                <div class="section-header">
                    <h2><i class="fas fa-tasks"></i> Task Management</h2>
                    <div class="task-controls">
                        <button id="refresh-tasks" class="btn-secondary">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                </div>
                
                <!-- Pending Tasks -->
                <div class="task-section">
                    <h3>Pending Tasks</h3>
                    <div class="task-grid" id="pending-tasks">
                        <!-- Tasks will be populated here -->
                    </div>
                </div>
                
                <!-- Assigned Tasks -->
                <div class="task-section">
                    <h3>Assigned Tasks</h3>
                    <div class="task-grid" id="assigned-tasks">
                        <!-- Assigned tasks will be populated here -->
                    </div>
                </div>
            </section>
            
            <!-- Driver Assignments -->
            <section class="driver-assignments">
                <h2>Driver Assignments</h2>
                <button class="view-all">View All</button>
                <table>
                    <thead>
                        <tr>
                            <th>Task ID</th>
                            <th>Driver</th>
                            <th>Request ID</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="assignments-table-body">
                        <!-- Populated by JS -->
                    </tbody>
                </table>
            </section>
            
            <!-- Smart Scheduling Section -->
            <section class="smart-scheduling">
                <div class="section-header">
                    <h2><i class="fas fa-route"></i> Smart Scheduling</h2>
                    <div class="schedule-controls">
                        <input type="date" id="schedule-date" value="<?php echo date('Y-m-d'); ?>">
                        <button id="generate-schedule" class="btn-primary">
                            <i class="fas fa-magic"></i> Generate Schedule
                        </button>
                    </div>
                </div>
                
                <div class="schedule-summary" id="schedule-summary" style="display: none;">
                    <div class="summary-cards">
                        <div class="summary-card">
                            <h3 id="total-requests-count">0</h3>
                            <p>Total Requests</p>
                        </div>
                        <div class="summary-card">
                            <h3 id="clusters-formed">0</h3>
                            <p>Clusters Formed</p>
                        </div>
                        <div class="summary-card">
                            <h3 id="fuel-savings">0%</h3>
                            <p>Fuel Savings</p>
                        </div>
                        <div class="summary-card">
                            <h3 id="estimated-time">0 min</h3>
                            <p>Total Time</p>
                        </div>
                    </div>
                </div>
                
                <div class="schedule-details" id="schedule-details" style="display: none;">
                    <h3>Optimized Routes</h3>
                    <div id="routes-container">
                        <!-- Routes will be populated here -->
                    </div>
                </div>
                
                <div class="schedule-recommendations" id="schedule-recommendations" style="display: none;">
                    <h3><i class="fas fa-lightbulb"></i> Recommendations</h3>
                    <ul id="recommendations-list">
                        <!-- Recommendations will be populated here -->
                    </ul>
                </div>
            </section>
            <!-- Notifications Sent by Drivers -->
            <section class="driver-notifications">
                <h2>Driver-to-User Notifications</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Sender (Driver)</th>
                            <th>Receiver (User)</th>
                            <th>Message</th>
                            <th>Sent At</th>
                        </tr>
                    </thead>
                    <tbody id="driver-notifications-table-body">
                        <!-- Populated by JS -->
                    </tbody>
                </table>
            </section>
            <!-- Admin Send Message to User -->
            <section class="admin-send-message" style="margin: 30px 0; padding: 20px; background: rgba(255,255,255,0.8); border-radius: 12px; max-width: 500px;">
                <h2>Send Message to User</h2>
                <form id="admin-send-message-form">
                    <label for="admin-receiver-id">Select User:</label>
                    <select id="admin-receiver-id" name="receiver_id" required></select>
                    <br><br>
                    <label for="admin-message">Message:</label>
                    <textarea id="admin-message" name="message" rows="3" required style="width:100%;"></textarea>
                    <br><br>
                    <button type="submit">Send Message</button>
                </form>
                <div id="admin-send-feedback" style="margin-top:10px;"></div>
            </section>
            <!-- Admin Sent Messages -->
            <section class="admin-sent-messages">
                <h2>Sent Messages</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Receiver (User)</th>
                            <th>Message</th>
                            <th>Sent At</th>
                        </tr>
                    </thead>
                    <tbody id="admin-sent-messages-table-body">
                        <!-- Populated by JS -->
                    </tbody>
                </table>
            </section>
            <!-- User Reports Section (Summary) -->
            <section class="user-reports">
                <h2>User Reports <a href="admin_reports.php" class="view-all" style="float:right;">View All</a></h2>
                <table>
                    <thead>
                        <tr>
                            <th>Report ID</th>
                            <th>User</th>
                            <th>Title</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody id="user-reports-summary-body">
                        <!-- Populated by JS -->
                    </tbody>
                </table>
            </section>
        </main>
    </div>
    
    <!-- Driver Selection Modal -->
    <div id="driver-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Select Driver for Task</h3>
                <button class="close-modal" onclick="closeDriverModal()">&times;</button>
            </div>
            <div class="driver-list" id="driver-list">
                <!-- Drivers will be populated here -->
            </div>
        </div>
    </div>
    <script>
    // Fetch and render driver-to-user notifications
    fetch('../../backend/api/get_driver_notifications.php')
        .then(res => res.json())
        .then(data => {
            const tbody = document.getElementById('driver-notifications-table-body');
            tbody.innerHTML = '';
            if (Array.isArray(data) && data.length > 0) {
                data.forEach(n => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${n.sender_username} (${n.sender_email})</td>
                        <td>${n.receiver_username} (${n.receiver_email})</td>
                        <td>${n.message}</td>
                        <td>${n.sent_at}</td>
                    `;
                    tbody.appendChild(tr);
                });
            } else {
                const tr = document.createElement('tr');
                tr.innerHTML = '<td colspan="4">No notifications found.</td>';
                tbody.appendChild(tr);
            }
        });
    // Populate user dropdown for admin message form
    fetch('../../backend/api/get_users.php?role=resident')
        .then(res => res.json())
        .then(users => {
            const select = document.getElementById('admin-receiver-id');
            users.forEach(u => {
                const opt = document.createElement('option');
                opt.value = u.id;
                opt.textContent = u.username + ' (' + u.email + ')';
                select.appendChild(opt);
            });
        });
    // Handle admin send message form submit
    document.getElementById('admin-send-message-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const receiver_id = document.getElementById('admin-receiver-id').value;
        const message = document.getElementById('admin-message').value;
        fetch('../../backend/api/admin_send_message.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ receiver_id, message })
        })
        .then(res => res.json())
        .then(data => {
            const feedback = document.getElementById('admin-send-feedback');
            if (data.success) {
                feedback.textContent = 'Message sent!';
                feedback.style.color = 'green';
                document.getElementById('admin-send-message-form').reset();
                fetchAndRenderAdminSentMessages();
            } else {
                feedback.textContent = data.error || 'Failed to send message.';
                feedback.style.color = 'red';
            }
        })
        .catch(() => {
            const feedback = document.getElementById('admin-send-feedback');
            feedback.textContent = 'Error sending message.';
            feedback.style.color = 'red';
        });
    });
    // Fetch and render admin sent messages
    function fetchAndRenderAdminSentMessages() {
        fetch('../../backend/api/admin_sent_messages.php')
            .then(res => res.json())
            .then(data => {
                const tbody = document.getElementById('admin-sent-messages-table-body');
                tbody.innerHTML = '';
                if (Array.isArray(data) && data.length > 0) {
                    data.forEach(m => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${m.receiver_username} (${m.receiver_email})</td>
                            <td>${m.message}</td>
                            <td>${m.sent_at && !isNaN(new Date(m.sent_at)) ? new Date(m.sent_at).toLocaleString() : 'N/A'}</td>
                        `;
                        tbody.appendChild(tr);
                    });
                } else {
                    const tr = document.createElement('tr');
                    tr.innerHTML = '<td colspan="3">No sent messages found.</td>';
                    tbody.appendChild(tr);
                }
            });
    }
    // Initial fetch
    fetchAndRenderAdminSentMessages();
    // Fetch and render user reports summary (latest 3)
    function fetchAndRenderUserReportsSummary() {
        fetch('../../backend/api/get_reports.php')
            .then(res => res.json())
            .then(data => {
                const tbody = document.getElementById('user-reports-summary-body');
                tbody.innerHTML = '';
                if (Array.isArray(data) && data.length > 0) {
                    data.slice(0, 3).forEach(r => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${r.id}</td>
                            <td>${r.username} (${r.email})</td>
                            <td>${r.title}</td>
                            <td>${r.created_at ? new Date(r.created_at).toLocaleDateString() : 'N/A'}</td>
                        `;
                        tbody.appendChild(tr);
                    });
                } else {
                    const tr = document.createElement('tr');
                    tr.innerHTML = '<td colspan="4">No reports found.</td>';
                    tbody.appendChild(tr);
                }
            });
    }
    fetchAndRenderUserReportsSummary();
    // Fetch and render requests summary (latest 3)
    function fetchAndRenderRequestsSummary() {
        fetch('../../backend/api/get_recent_requests.php')
            .then(res => res.json())
            .then(data => {
                const tbody = document.getElementById('requests-summary-body');
                tbody.innerHTML = '';
                if (Array.isArray(data) && data.length > 0) {
                    data.slice(0, 3).forEach(r => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${r.id}</td>
                            <td>${r.username || 'Unknown'}</td>
                            <td>${r.document || r.waste_type || 'Waste Collection'}</td>
                            <td>${r.status || 'Pending'}</td>
                            <td>${r.created_at ? new Date(r.created_at).toLocaleDateString() : 'N/A'}</td>
                        `;
                        tbody.appendChild(tr);
                    });
                } else {
                    const tr = document.createElement('tr');
                    tr.innerHTML = '<td colspan="5">No requests found.</td>';
                    tbody.appendChild(tr);
                }
            });
    }
    fetchAndRenderRequestsSummary();
    // Handle logout button click
    document.getElementById('logout-btn').addEventListener('click', function() {
        if (confirm('Are you sure you want to log out?')) {
            window.location.href = 'logout.php';
        }
    });
    </script>
</body>
</html> 