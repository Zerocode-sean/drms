<?php
// Include asset helper for environment-aware paths
require_once __DIR__ . '/../../backend/config/asset_helper.php';
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
    <title>DRMS Admin Notifications</title>
    <link rel="stylesheet" href="<?php echo cssPath('admin.css'); ?>">
    <link rel="icon" href="<?php echo logoPath(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo">DRMS</div>
            <nav>
                <ul>
                    <li><a href="admin.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="admin_requests.php"><i class="fas fa-file-alt"></i> Request</a></li>
                    <li><a href="admin_smart_scheduling.php"><i class="fas fa-route"></i> Smart Scheduling</a></li>
                    <li><a href="admin_users.php"><i class="fas fa-users"></i> Users</a></li>
                    <li><a href="admin_drivers.php"><i class="fas fa-truck"></i> Drivers</a></li>
                    <li><a href="admin_reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
                    <li class="active"><a href="admin_notifications.php"><i class="fas fa-bell"></i> Notifications</a></li>
                    <li><a href="admin_profile.php"><i class="fas fa-user"></i> Profile</a></li>
                    <li><a href="admin_settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                    <li><a href="admin_send_sms.php"><i class="fas fa-sms"></i> Send SMS</a></li>
                    <li id="logout-btn" style="cursor:pointer;"><i class="fas fa-sign-out-alt"></i> Logout</li>
                </ul>
            </nav>
        </aside>
        <!-- Main Content -->
        <main class="main-content">
            <header>
                <h1>Notifications</h1>
                <div class="user-info">
                    <img src="<?php echo logoPath(); ?>" alt="Admin Avatar">
                </div>
            </header>
            <section class="admin-notifications">
                <h2>All Notifications</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Sender</th>
                            <th>Receiver</th>
                            <th>Message</th>
                            <th>Sent At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="notifications-table-body">
                        <!-- Populated by JS -->
                    </tbody>
                </table>
                <!-- View Modal -->
                <div id="notification-view-modal" class="modal" style="display:none;">
                    <div class="modal-content">
                        <span class="close" onclick="closeNotificationViewModal()">&times;</span>
                        <h3>Notification Details</h3>
                        <div id="notification-view-details"></div>
                    </div>
                </div>
            </section>
        </main>
    </div>
    <script>
    // Fetch and render all notifications
    function fetchAndRenderNotifications() {
        fetch('../../backend/api/get_notifications.php')
            .then(res => res.json())
            .then(data => {
                const tbody = document.getElementById('notifications-table-body');
                tbody.innerHTML = '';
                if (Array.isArray(data) && data.length > 0) {
                    data.forEach(n => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${n.sender_username || 'System'} (${n.sender_email || ''})</td>
                            <td>${n.receiver_username || ''} (${n.receiver_email || ''})</td>
                            <td>${n.message}</td>
                            <td>${n.sent_at ? new Date(n.sent_at).toLocaleString() : 'N/A'}</td>
                            <td>
                                <button class="view-notification-btn" data-id="${n.id}">View</button>
                                <button class="delete-notification-btn" data-id="${n.id}">Delete</button>
                            </td>
                        `;
                        tbody.appendChild(tr);
                    });
                } else {
                    const tr = document.createElement('tr');
                    tr.innerHTML = '<td colspan="5">No notifications found.</td>';
                    tbody.appendChild(tr);
                }
                attachNotificationActions(data);
            });
    }
    function attachNotificationActions(notifications) {
        document.querySelectorAll('.view-notification-btn').forEach(btn => {
            btn.onclick = function() {
                const n = notifications.find(n => n.id == btn.dataset.id);
                if (n) {
                    document.getElementById('notification-view-details').innerHTML = `
                        <p><strong>ID:</strong> ${n.id}</p>
                        <p><strong>Sender:</strong> ${n.sender_username || 'System'} (${n.sender_email || ''})</p>
                        <p><strong>Receiver:</strong> ${n.receiver_username || ''} (${n.receiver_email || ''})</p>
                        <p><strong>Message:</strong> ${n.message}</p>
                        <p><strong>Sent At:</strong> ${n.sent_at ? new Date(n.sent_at).toLocaleString() : 'N/A'}</p>
                    `;
                    document.getElementById('notification-view-modal').style.display = 'block';
                }
            };
        });
        document.querySelectorAll('.delete-notification-btn').forEach(btn => {
            btn.onclick = function() {
                if (confirm('Delete this notification?')) {
                    fetch('../../backend/api/delete_notification.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: btn.dataset.id })
                    }).then(res => res.json()).then(() => fetchAndRenderNotifications());
                }
            };
        });
    }
    function closeNotificationViewModal() {
        document.getElementById('notification-view-modal').style.display = 'none';
    }
    fetchAndRenderNotifications();
    // Handle logout button click
    document.getElementById('logout-btn').addEventListener('click', function() {
        if (confirm('Are you sure you want to log out?')) {
            window.location.href = 'logout.php';
        }
    });
    </script>
</body>
</html> 