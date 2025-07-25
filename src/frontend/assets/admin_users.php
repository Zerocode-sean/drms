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
    <title>DRMS Admin Users</title>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="icon" href="../images/logo.png">
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
                    <li class="active"><a href="admin_users.php"><i class="fas fa-users"></i> Users</a></li>
                    <li><a href="admin_drivers.php"><i class="fas fa-truck"></i> Drivers</a></li>
                    <li><a href="admin_reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
                    <li><a href="admin_notifications.php"><i class="fas fa-bell"></i> Notifications</a></li>
                    <li><a href="admin_profile.php"><i class="fas fa-user"></i> Profile</a></li>
                    <li><a href="admin_settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                    <li id="logout-btn" style="cursor:pointer;"><i class="fas fa-sign-out-alt"></i> Logout</li>
                </ul>
            </nav>
        </aside>
        <!-- Main Content -->
        <main class="main-content">
            <header>
                <h1>Users</h1>
                <div class="user-info">
                    <img src="../images/logo.png" alt="Admin Avatar">
                </div>
            </header>
            <section class="admin-users">
                <h2>All Users</h2>
                <table>
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="users-table-body">
                        <!-- Populated by JS -->
                    </tbody>
                </table>
                <!-- View Modal -->
                <div id="user-view-modal" class="modal" style="display:none;">
                    <div class="modal-content">
                        <span class="close" onclick="closeUserViewModal()">&times;</span>
                        <h3>User Details</h3>
                        <div id="user-view-details"></div>
                    </div>
                </div>
            </section>
        </main>
    </div>
    <script>
    // Fetch and render all users
    function fetchAndRenderUsers() {
        fetch('../../backend/api/get_users.php')
            .then(res => res.json())
            .then(data => {
                const tbody = document.getElementById('users-table-body');
                tbody.innerHTML = '';
                if (Array.isArray(data) && data.length > 0) {
                    data.forEach(u => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${u.id}</td>
                            <td>${u.username}</td>
                            <td>${u.email}</td>
                            <td>${u.role}</td>
                            <td>
                                <button class="view-user-btn" data-id="${u.id}">View</button>
                                <button class="delete-user-btn" data-id="${u.id}">Delete</button>
                            </td>
                        `;
                        tbody.appendChild(tr);
                    });
                } else {
                    const tr = document.createElement('tr');
                    tr.innerHTML = '<td colspan="5">No users found.</td>';
                    tbody.appendChild(tr);
                }
                attachUserActions(data);
            });
    }
    function attachUserActions(users) {
        document.querySelectorAll('.view-user-btn').forEach(btn => {
            btn.onclick = function() {
                const user = users.find(u => u.id == btn.dataset.id);
                if (user) {
                    document.getElementById('user-view-details').innerHTML = `
                        <p><strong>ID:</strong> ${user.id}</p>
                        <p><strong>Username:</strong> ${user.username}</p>
                        <p><strong>Email:</strong> ${user.email}</p>
                        <p><strong>Role:</strong> ${user.role}</p>
                    `;
                    document.getElementById('user-view-modal').style.display = 'block';
                }
            };
        });
        document.querySelectorAll('.delete-user-btn').forEach(btn => {
            btn.onclick = function() {
                if (confirm('Delete this user?')) {
                    fetch('../../backend/api/delete_user.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: btn.dataset.id })
                    }).then(res => res.json()).then(() => fetchAndRenderUsers());
                }
            };
        });
    }
    function closeUserViewModal() {
        document.getElementById('user-view-modal').style.display = 'none';
    }
    fetchAndRenderUsers();
    // Handle logout button click
    document.getElementById('logout-btn').addEventListener('click', function() {
        if (confirm('Are you sure you want to log out?')) {
            window.location.href = 'logout.php';
        }
    });
    </script>
</body>
</html> 