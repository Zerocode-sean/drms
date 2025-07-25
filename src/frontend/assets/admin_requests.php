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
    <title>DRMS Admin Requests</title>
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
                    <li class="active"><a href="admin_requests.php"><i class="fas fa-file-alt"></i> Request</a></li>
                    <li><a href="admin_users.php"><i class="fas fa-users"></i> Users</a></li>
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
                <h1>Requests</h1>
                <div class="user-info">
                    <img src="../images/logo.png" alt="Admin Avatar">
                </div>
            </header>
            <section class="admin-requests">
                <h2>All Requests</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Request ID</th>
                            <th>User</th>
                            <th>Document</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="requests-table-body">
                        <!-- Populated by JS -->
                    </tbody>
                </table>
                <!-- Modals -->
                <div id="assign-modal" class="modal" style="display:none;">
                    <div class="modal-content">
                        <span class="close" onclick="closeAssignModal()">&times;</span>
                        <h3>Assign Driver</h3>
                        <select id="driver-select"></select>
                        <button id="assign-confirm-btn">Assign</button>
                    </div>
                </div>
                <div id="view-modal" class="modal" style="display:none;">
                    <div class="modal-content">
                        <span class="close" onclick="closeViewModal()">&times;</span>
                        <h3>Request Details</h3>
                        <div id="view-details"></div>
                    </div>
                </div>
            </section>
        </main>
    </div>
    <script>
    // Fetch and render all requests
    function fetchAndRenderRequests() {
        fetch('../../backend/api/get_recent_requests.php')
            .then(res => res.json())
            .then(data => {
                const tbody = document.getElementById('requests-table-body');
                tbody.innerHTML = '';
                if (Array.isArray(data) && data.length > 0) {
                    data.forEach(r => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${r.id}</td>
                            <td>${r.username || 'Unknown'}</td>
                            <td>${r.document || r.waste_type || 'Waste Collection'}</td>
                            <td>${r.status || 'Pending'}</td>
                            <td>${r.created_at ? new Date(r.created_at).toLocaleString() : 'N/A'}</td>
                            <td>
                                <button class="approve-btn" data-id="${r.id}">Approve</button>
                                <button class="reject-btn" data-id="${r.id}">Reject</button>
                                <button class="assign-btn" data-id="${r.id}">Assign</button>
                                <button class="view-btn" data-id="${r.id}">View</button>
                            </td>
                        `;
                        tbody.appendChild(tr);
                    });
                } else {
                    const tr = document.createElement('tr');
                    tr.innerHTML = '<td colspan="6">No requests found.</td>';
                    tbody.appendChild(tr);
                }
                attachRequestActions();
            });
    }
    function attachRequestActions() {
        document.querySelectorAll('.approve-btn').forEach(btn => {
            btn.onclick = function() {
                if (confirm('Approve this request?')) {
                    fetch('../../backend/api/approve_request.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: btn.dataset.id })
                    }).then(res => res.json()).then(() => fetchAndRenderRequests());
                }
            };
        });
        document.querySelectorAll('.reject-btn').forEach(btn => {
            btn.onclick = function() {
                if (confirm('Reject this request?')) {
                    fetch('../../backend/api/reject_request.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: btn.dataset.id })
                    }).then(res => res.json()).then(() => fetchAndRenderRequests());
                }
            };
        });
        document.querySelectorAll('.assign-btn').forEach(btn => {
            btn.onclick = function() {
                openAssignModal(btn.dataset.id);
            };
        });
        document.querySelectorAll('.view-btn').forEach(btn => {
            btn.onclick = function() {
                openViewModal(btn.dataset.id);
            };
        });
    }
    // Assign Modal Logic
    let currentAssignRequestId = null;
    function openAssignModal(requestId) {
        currentAssignRequestId = requestId;
        document.getElementById('assign-modal').style.display = 'block';
        // Fetch drivers
        fetch('../../backend/api/get_available_drivers.php')
            .then(res => res.json())
            .then(data => {
                const select = document.getElementById('driver-select');
                select.innerHTML = '';
                (data.drivers || []).forEach(d => {
                    const opt = document.createElement('option');
                    opt.value = d.id;
                    opt.textContent = d.name + ' (' + d.email + ')';
                    select.appendChild(opt);
                });
            });
    }
    document.getElementById('assign-confirm-btn').onclick = function() {
        const driverId = document.getElementById('driver-select').value;
        if (!driverId) return alert('Select a driver');
        fetch('../../backend/api/assign_request.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ request_id: currentAssignRequestId, driver_id: driverId })
        }).then(res => res.json()).then(() => {
            closeAssignModal();
            fetchAndRenderRequests();
        });
    };
    function closeAssignModal() {
        document.getElementById('assign-modal').style.display = 'none';
    }
    // View Modal Logic
    function openViewModal(requestId) {
        document.getElementById('view-modal').style.display = 'block';
        // Find request in table (or fetch again if needed)
        fetch('../../backend/api/get_recent_requests.php')
            .then(res => res.json())
            .then(data => {
                const req = (data || []).find(r => r.id == requestId);
                if (req) {
                    document.getElementById('view-details').innerHTML = `
                        <p><strong>ID:</strong> ${req.id}</p>
                        <p><strong>User:</strong> ${req.username || 'Unknown'}</p>
                        <p><strong>Phone:</strong> ${req.phone || 'Not provided'}</p>
                        <p><strong>Document:</strong> ${req.document || req.waste_type || 'Waste Collection'}</p>
                        <p><strong>Status:</strong> ${req.status || 'Pending'}</p>
                        <p><strong>Date:</strong> ${req.created_at ? new Date(req.created_at).toLocaleString() : 'N/A'}</p>
                        <p><strong>Location:</strong> ${req.location || ''}</p>
                        <p><strong>Notes:</strong> ${req.notes || ''}</p>
                        <p><strong>Address Details:</strong> ${req.address_details || 'None'}</p>
                    `;
                }
            });
    }
    function closeViewModal() {
        document.getElementById('view-modal').style.display = 'none';
    }
    fetchAndRenderRequests();
    // Handle logout button click
    document.getElementById('logout-btn').addEventListener('click', function() {
        if (confirm('Are you sure you want to log out?')) {
            window.location.href = 'logout.php';
        }
    });
    </script>
</body>
</html> 