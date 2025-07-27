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
    <title>DRMS Admin Drivers</title>
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
                    <li class="active"><a href="admin_drivers.php"><i class="fas fa-truck"></i> Drivers</a></li>
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
            <header>
                <h1>Drivers</h1>
                <div class="user-info">
                    <img src="<?php echo logoPath(); ?>" alt="Admin Avatar">
                </div>
            </header>
            <section class="admin-drivers">
                <h2>All Drivers</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Driver ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="drivers-table-body">
                        <!-- Populated by JS -->
                    </tbody>
                </table>
                <!-- View Modal -->
                <div id="driver-view-modal" class="modal" style="display:none;">
                    <div class="modal-content">
                        <span class="close" onclick="closeDriverViewModal()">&times;</span>
                        <h3>Driver Details</h3>
                        <div id="driver-view-details"></div>
                    </div>
                </div>
            </section>
        </main>
    </div>
    <script>
    // Fetch and render all drivers
    function fetchAndRenderDrivers() {
        fetch('../../backend/api/get_available_drivers.php')
            .then(res => res.json())
            .then(data => {
                const drivers = data.drivers || [];
                const tbody = document.getElementById('drivers-table-body');
                tbody.innerHTML = '';
                if (drivers.length > 0) {
                    drivers.forEach(d => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${d.id}</td>
                            <td>${d.name}</td>
                            <td>${d.email}</td>
                            <td>${d.phone}</td>
                            <td>
                                <button class="view-driver-btn" data-id="${d.id}">View</button>
                                <button class="delete-driver-btn" data-id="${d.id}">Delete</button>
                            </td>
                        `;
                        tbody.appendChild(tr);
                    });
                } else {
                    const tr = document.createElement('tr');
                    tr.innerHTML = '<td colspan="5">No drivers found.</td>';
                    tbody.appendChild(tr);
                }
                attachDriverActions(drivers);
            });
    }
    function attachDriverActions(drivers) {
        document.querySelectorAll('.view-driver-btn').forEach(btn => {
            btn.onclick = function() {
                const driver = drivers.find(d => d.id == btn.dataset.id);
                if (driver) {
                    document.getElementById('driver-view-details').innerHTML = `
                        <p><strong>ID:</strong> ${driver.id}</p>
                        <p><strong>Name:</strong> ${driver.name}</p>
                        <p><strong>Email:</strong> ${driver.email}</p>
                        <p><strong>Phone:</strong> ${driver.phone}</p>
                        <p><strong>Vehicle Type:</strong> ${driver.vehicle_type || ''}</p>
                        <p><strong>Capacity:</strong> ${driver.capacity || ''}</p>
                        <p><strong>Status:</strong> ${driver.status || ''}</p>
                    `;
                    document.getElementById('driver-view-modal').style.display = 'block';
                }
            };
        });
        document.querySelectorAll('.delete-driver-btn').forEach(btn => {
            btn.onclick = function() {
                if (confirm('Delete this driver?')) {
                    fetch('../../backend/api/delete_driver.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: btn.dataset.id })
                    }).then(res => res.json()).then(() => fetchAndRenderDrivers());
                }
            };
        });
    }
    function closeDriverViewModal() {
        document.getElementById('driver-view-modal').style.display = 'none';
    }
    fetchAndRenderDrivers();
    // Handle logout button click
    document.getElementById('logout-btn').addEventListener('click', function() {
        if (confirm('Are you sure you want to log out?')) {
            window.location.href = 'logout.php';
        }
    });
    </script>
</body>
</html> 