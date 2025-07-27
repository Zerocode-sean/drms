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
    <title>DRMS Admin Reports</title>
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
                    <li class="active"><a href="admin_reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
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
            <header>
                <h1>User Reports</h1>
                <div class="user-info">
                    <img src="<?php echo logoPath(); ?>" alt="Admin Avatar">
                </div>
            </header>
            <section class="user-reports">
                <h2>User Reports</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Report ID</th>
                            <th>User</th>
                            <th>Title</th>
                            <th>Content</th>
                            <th>Date Submitted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="user-reports-table-body">
                        <!-- Populated by JS -->
                    </tbody>
                </table>
            </section>
        </main>
    </div>
    <script>
    // Fetch and render user reports
    function fetchAndRenderUserReports() {
        fetch('../../backend/api/get_reports.php')
            .then(res => res.json())
            .then(data => {
                const tbody = document.getElementById('user-reports-table-body');
                tbody.innerHTML = '';
                if (Array.isArray(data) && data.length > 0) {
                    data.forEach(r => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${r.id}</td>
                            <td>${r.username} (${r.email})</td>
                            <td>${r.report_type}</td>
                            <td>${r.description}</td>
                            <td>${r.created_at && !isNaN(new Date(r.created_at)) ? new Date(r.created_at).toLocaleString() : 'N/A'}</td>
                            <td>
                                <button class='view-report-btn' data-id='${r.id}'>View</button>
                                <button class='delete-report-btn' data-id='${r.id}'>Delete</button>
                            </td>
                        `;
                        tbody.appendChild(tr);
                    });
                } else {
                    const tr = document.createElement('tr');
                    tr.innerHTML = '<td colspan="5">No reports found.</td>';
                    tbody.appendChild(tr);
                }
                attachReportActions(data);
            });
    }
    // Initial fetch
    fetchAndRenderUserReports();
    // Handle logout button click
    document.getElementById('logout-btn').addEventListener('click', function() {
        if (confirm('Are you sure you want to log out?')) {
            window.location.href = 'logout.php';
        }
    });

    function attachReportActions(reports) {
        document.querySelectorAll('.view-report-btn').forEach(btn => {
            btn.onclick = function() {
                const report = reports.find(r => r.id == btn.dataset.id);
                if (report) {
                    document.getElementById('report-view-details').innerHTML = `
                        <p><strong>ID:</strong> ${report.id}</p>
                        <p><strong>User:</strong> ${report.username} (${report.email})</p>
                        <p><strong>User Role:</strong> ${report.role}</p>
                        <p><strong>Report Type:</strong> ${report.report_type}</p>
                        <p><strong>Description:</strong> ${report.description}</p>
                        <p><strong>Date:</strong> ${report.created_at ? new Date(report.created_at).toLocaleString() : 'N/A'}</p>
                    `;
                    document.getElementById('report-view-modal').style.display = 'block';
                }
            };
        });
        document.querySelectorAll('.delete-report-btn').forEach(btn => {
            btn.onclick = function() {
                if (confirm('Delete this report?')) {
                    fetch('../../backend/api/delete_report.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: btn.dataset.id })
                    }).then(res => res.json()).then(() => fetchAndRenderUserReports());
                }
            };
        });
    }
    function closeReportViewModal() {
        document.getElementById('report-view-modal').style.display = 'none';
    }

    // Add the view modal HTML
    document.write(`
        <div id="report-view-modal" class="modal" style="display:none;">
            <div class="modal-content">
                <span class="close" onclick="closeReportViewModal()">&times;</span>
                <h3>Report Details</h3>
                <div id="report-view-details"></div>
            </div>
        </div>
    `);
    </script>
</body>
</html> 