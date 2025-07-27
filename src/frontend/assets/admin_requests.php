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
    <title>DRMS Admin Requests</title>
    <link rel="stylesheet" href="<?php echo cssPath('admin.css'); ?>">
    <link rel="icon" href="<?php echo logoPath(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
        }
        .modal-content {
            background: white;
            margin: 100px auto;
            padding: 20px;
            width: 80%;
            max-width: 500px;
            border-radius: 8px;
            position: relative;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .close {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 20px;
            cursor: pointer;
            color: #999;
        }
        .close:hover {
            color: #000;
        }
    </style>
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
                    <li><a href="admin_smart_scheduling.php"><i class="fas fa-route"></i> Smart Scheduling</a></li>
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
            <header>
                <h1>Requests</h1>
                <div class="user-info">
                    <img src="<?php echo logoPath(); ?>" alt="Admin Avatar">
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
                        <h3>Assign Driver to Request</h3>
                        <div style="margin: 15px 0;">
                            <label for="driver-select" style="display: block; margin-bottom: 5px; font-weight: bold;">Select Driver:</label>
                            <select id="driver-select" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                                <option value="">Loading drivers...</option>
                            </select>
                        </div>
                        <div style="margin-top: 20px;">
                            <button id="assign-confirm-btn" style="background: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;">Assign Driver</button>
                            <button onclick="closeAssignModal()" style="background: #6c757d; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; margin-left: 10px;">Cancel</button>
                        </div>
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
                console.log('Drivers API response:', data);
                const select = document.getElementById('driver-select');
                select.innerHTML = '';
                
                if (data.success && data.drivers && Array.isArray(data.drivers)) {
                    if (data.drivers.length === 0) {
                        const opt = document.createElement('option');
                        opt.value = '';
                        opt.textContent = 'No drivers available';
                        select.appendChild(opt);
                    } else {
                        data.drivers.forEach(d => {
                            const opt = document.createElement('option');
                            opt.value = d.id;
                            opt.textContent = `${d.name} (${d.email}) - Capacity: ${d.available_capacity}/${d.capacity}`;
                            select.appendChild(opt);
                        });
                    }
                } else {
                    const opt = document.createElement('option');
                    opt.value = '';
                    opt.textContent = 'Error loading drivers';
                    select.appendChild(opt);
                    console.error('Drivers API error:', data);
                }
            })
            .catch(error => {
                console.error('Error fetching drivers:', error);
                const select = document.getElementById('driver-select');
                select.innerHTML = '<option value="">Error loading drivers</option>';
            });
    }
    document.getElementById('assign-confirm-btn').onclick = function() {
        const driverId = document.getElementById('driver-select').value;
        if (!driverId) {
            alert('Please select a driver');
            return;
        }
        
        // Disable button to prevent double submission
        const btn = document.getElementById('assign-confirm-btn');
        btn.disabled = true;
        btn.textContent = 'Assigning...';
        
        fetch('../../backend/api/assign_request.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                request_id: parseInt(currentAssignRequestId), 
                driver_id: parseInt(driverId) 
            })
        })
        .then(res => res.json())
        .then(data => {
            console.log('Assignment response:', data);
            if (data.success) {
                alert('Driver assigned successfully!');
                closeAssignModal();
                fetchAndRenderRequests();
            } else {
                alert('Assignment failed: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Assignment error:', error);
            alert('Assignment failed: ' + error.message);
        })
        .finally(() => {
            // Re-enable button
            btn.disabled = false;
            btn.textContent = 'Assign';
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