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
    <title>DRMS Admin - Smart Scheduling</title>
    <link rel="stylesheet" href="<?php echo cssPath('admin.css'); ?>">
    <link rel="icon" href="<?php echo logoPath(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .scheduling-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .scheduling-controls {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .date-picker {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .btn-primary {
            background: #007bff;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary:hover {
            background: #0056b3;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .btn-success:hover {
            background: #1e7e34;
        }
        
        .btn-warning {
            background: #ffc107;
            color: #212529;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .btn-warning:hover {
            background: #e0a800;
        }
        
        .btn-info {
            background: #17a2b8;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .btn-info:hover {
            background: #138496;
        }
        
        .schedule-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .driver-schedule {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .driver-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .driver-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .driver-avatar {
            width: 40px;
            height: 40px;
            background: #007bff;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        .driver-stats {
            font-size: 12px;
            color: #666;
        }
        
        .request-item {
            background: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 12px;
            margin-bottom: 10px;
            border-radius: 0 5px 5px 0;
            position: relative;
        }
        
        .request-item.high-priority {
            border-left-color: #dc3545;
        }
        
        .request-item.urgent-priority {
            border-left-color: #fd7e14;
            background: #fff3cd;
        }
        
        .request-time {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .request-location {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .request-details {
            font-size: 12px;
            color: #666;
            display: flex;
            justify-content: space-between;
        }
        
        .priority-badge {
            position: absolute;
            top: 5px;
            right: 5px;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .priority-urgent { background: #fd7e14; color: white; }
        .priority-high { background: #dc3545; color: white; }
        .priority-medium { background: #ffc107; color: black; }
        .priority-low { background: #6c757d; color: white; }
        
        .status-indicator {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 5px;
        }
        
        .status-draft { background: #ffc107; }
        .status-confirmed { background: #28a745; }
        .status-in-progress { background: #007bff; }
        .status-completed { background: #6c757d; }
        
        .summary-card {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .summary-stats {
            display: flex;
            justify-content: space-around;
            margin-top: 15px;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
        }
        
        .stat-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
        }
        
        .loading {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
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
                    <li><a href="admin_requests.php"><i class="fas fa-file-alt"></i> Request</a></li>
                    <li class="active"><a href="admin_smart_scheduling.php"><i class="fas fa-route"></i> Smart Scheduling</a></li>
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
                <h1><i class="fas fa-calendar-alt"></i> Smart Scheduling</h1>
                <div class="user-info">
                    <img src="<?php echo logoPath(); ?>" alt="Admin Avatar">
                </div>
            </header>
            
            <section class="smart-scheduling">
                <!-- Scheduling Header -->
                <div class="scheduling-header">
                    <div>
                        <h2>Intelligent Waste Collection Scheduling</h2>
                        <p>Optimize routes and assign drivers automatically based on location, priority, and capacity</p>
                    </div>
                    <div class="scheduling-controls">
                        <input type="date" id="schedule-date" class="date-picker" value="">
                        <button id="generate-schedule-btn" class="btn-primary">
                            <i class="fas fa-magic"></i> Generate Schedule
                        </button>
                        <button id="view-schedule-btn" class="btn-success">
                            <i class="fas fa-eye"></i> View Schedule
                        </button>
                        <button id="approve-all-btn" class="btn-warning">
                            <i class="fas fa-check-double"></i> Approve All Pending
                        </button>
                        <button id="assign-all-btn" class="btn-info">
                            <i class="fas fa-truck-loading"></i> Assign All to Drivers
                        </button>
                    </div>
                </div>
                
                <!-- Messages -->
                <div id="message-container"></div>
                
                <!-- Schedule Summary -->
                <div id="schedule-summary" style="display: none;">
                    <div class="summary-card">
                        <h3>Schedule Summary</h3>
                        <div class="summary-stats">
                            <div class="stat-item">
                                <div class="stat-number" id="total-requests">0</div>
                                <div class="stat-label">Total Requests</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number" id="drivers-assigned">0</div>
                                <div class="stat-label">Drivers Assigned</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number" id="total-volume">0</div>
                                <div class="stat-label">Total Volume (L)</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number" id="schedule-date-display">-</div>
                                <div class="stat-label">Schedule Date</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Schedule Grid -->
                <div id="schedule-container">
                    <div class="loading">
                        <i class="fas fa-info-circle"></i> Select a date and click "Generate Schedule" or "View Schedule" to begin
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script>
        // Initialize all components when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            // Set default date to tomorrow
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            const dateString = tomorrow.toISOString().split('T')[0];
            const dateInput = document.getElementById('schedule-date');
            if (dateInput) {
                dateInput.value = dateString;
            }
            
            // Generate Schedule button event
            const generateBtn = document.getElementById('generate-schedule-btn');
            if (generateBtn) {
                generateBtn.addEventListener('click', function() {
                    const date = document.getElementById('schedule-date').value;
                    if (!date) {
                        showMessage('Please select a date', 'error');
                        return;
                    }
                    generateSchedule(date);
                });
            }

            // View Schedule button event
            const viewBtn = document.getElementById('view-schedule-btn');
            if (viewBtn) {
                viewBtn.addEventListener('click', function() {
                    const date = document.getElementById('schedule-date').value;
                    if (!date) {
                        showMessage('Please select a date', 'error');
                        return;
                    }
                    viewSchedule(date);
                });
            }
            
            // Logout button event
            const logoutBtn = document.getElementById('logout-btn');
            if (logoutBtn) {
                logoutBtn.addEventListener('click', function() {
                    if (confirm('Are you sure you want to log out?')) {
                        window.location.href = 'logout.php';
                    }
                });
            }
            
            // Approve All button event
            const approveAllBtn = document.getElementById('approve-all-btn');
            if (approveAllBtn) {
                approveAllBtn.addEventListener('click', function() {
                    if (confirm('Are you sure you want to approve ALL pending requests? This action cannot be undone.')) {
                        approveAllPending();
                    }
                });
            }
            
            // Assign All button event
            const assignAllBtn = document.getElementById('assign-all-btn');
            if (assignAllBtn) {
                assignAllBtn.addEventListener('click', function() {
                    const date = document.getElementById('schedule-date').value;
                    if (!date) {
                        showMessage('Please select a date for driver assignments', 'error');
                        return;
                    }
                    if (confirm('Are you sure you want to assign ALL approved requests to drivers for the selected date?')) {
                        assignAllToDrivers(date);
                    }
                });
            }
        });

        // Generate Schedule Function
        function generateSchedule(date) {
            showLoading('Generating optimized schedule...');
            
            fetch(`../../backend/api/smart_scheduling.php?action=generate&date=${date}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const driversUsed = data.drivers_used || 0;
                    const totalRequests = data.total_requests || 0;
                    const message = `Schedule generated successfully! ${driversUsed} drivers assigned to ${totalRequests} requests.`;
                    showMessage(message, 'success');
                    displaySchedule(data.schedules, date);
                    updateSummary(data);
                } else {
                    showMessage('Error generating schedule: ' + (data.error || 'Unknown error'), 'error');
                    document.getElementById('schedule-container').innerHTML = '<div class="loading"><i class="fas fa-exclamation-triangle"></i> Failed to generate schedule</div>';
                }
            })
            .catch(error => {
                showMessage('Network error: ' + error.message, 'error');
            });
        }

        // View Schedule Function
        function viewSchedule(date) {
            showLoading('Loading existing schedule...');
            
            fetch(`../../backend/api/smart_scheduling.php?action=view&date=${date}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.schedules.length === 0) {
                        showMessage('No schedule found for this date. Click "Generate Schedule" to create one.', 'error');
                        document.getElementById('schedule-container').innerHTML = '<div class="loading"><i class="fas fa-info-circle"></i> No schedule found for this date</div>';
                    } else {
                        showMessage(`Loaded schedule for ${data.date}`, 'success');
                        displayExistingSchedule(data.schedules, date);
                        updateSummaryFromView(data.schedules, date);
                    }
                } else {
                    showMessage('Error loading schedule: ' + (data.error || 'Unknown error'), 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('Network error: ' + error.message, 'error');
            });
        }

        // Display Schedule
        function displaySchedule(schedules, date) {
            const container = document.getElementById('schedule-container');
            
            if (schedules.length === 0) {
                container.innerHTML = '<div class="loading"><i class="fas fa-info-circle"></i> No schedules generated - no approved requests or available drivers</div>';
                return;
            }
            
            let html = '<div class="schedule-grid">';
            
            schedules.forEach(schedule => {
                const driver = schedule.driver;
                const requests = schedule.requests || [];
                const driverName = driver.username || driver.name || 'Unknown Driver';
                const vehicleNumber = driver.license_number || driver.vehicle_number || 'N/A';
                const vehicleType = driver.vehicle_type || 'Vehicle';
                
                html += `
                    <div class="driver-schedule">
                        <div class="driver-header">
                            <div class="driver-info">
                                <div class="driver-avatar">${driverName.charAt(0)}</div>
                                <div>
                                    <strong>${driverName}</strong><br>
                                    <small>${vehicleNumber} (${vehicleType})</small>
                                </div>
                            </div>
                            <div class="driver-stats">
                                <div><span class="status-indicator status-confirmed"></span>Confirmed</div>
                                <div>Capacity: ${schedule.total_volume || 0}/${driver.capacity || 10}L</div>
                                <div>${schedule.start_time} - ${schedule.end_time}</div>
                            </div>
                        </div>
                        
                        <div class="requests-list">
                            ${requests.map((request, index) => `
                                <div class="request-item ${(request.priority || 'normal').toLowerCase()}-priority">
                                    <div class="priority-badge priority-${(request.priority || 'normal').toLowerCase()}">${request.priority || 'Normal'}</div>
                                    <div class="request-time">${request.estimated_start_time || 'N/A'} (${request.estimated_duration || 30} min)</div>
                                    <div class="request-location">${request.pickup_location || 'Collection Location'}</div>
                                    <div class="request-details">
                                        <span>${request.waste_type || 'General Waste'} - ${request.estimated_volume || 1}L</span>
                                        <span>Seq: ${request.sequence_order || (index + 1)}</span>
                                    </div>
                                </div>
                            `).join('')}
                            
                            ${requests.length === 0 ? '<div class="loading">No requests assigned</div>' : ''}
                        </div>
                    </div>
                `;
            });
            
            html += '</div>';
            container.innerHTML = html;
        }

        // Display Existing Schedule (different format from API)
        function displayExistingSchedule(schedules, date) {
            const container = document.getElementById('schedule-container');
            
            if (schedules.length === 0) {
                container.innerHTML = '<div class="loading"><i class="fas fa-info-circle"></i> No schedules found for this date</div>';
                return;
            }
            
            let html = '<div class="schedule-grid">';
            
            schedules.forEach(schedule => {
                const assignments = schedule.assignments || [];
                const driverName = schedule.driver_name || 'Unknown Driver';
                const licenseNumber = schedule.license_number || 'N/A';
                
                html += `
                    <div class="driver-schedule">
                        <div class="driver-header">
                            <div class="driver-info">
                                <div class="driver-avatar">${driverName.charAt(0)}</div>
                                <div>
                                    <strong>${driverName}</strong><br>
                                    <small>${licenseNumber}</small>
                                </div>
                            </div>
                            <div class="driver-stats">
                                <div><span class="status-indicator status-${schedule.status || 'draft'}"></span>${schedule.status || 'Draft'}</div>
                                <div>Volume: ${schedule.total_volume || 0}L</div>
                                <div>${schedule.start_time || '08:00:00'} - ${schedule.end_time || '17:00:00'}</div>
                            </div>
                        </div>
                        
                        <div class="requests-list">
                            ${assignments.map((assignment, index) => `
                                <div class="request-item ${(assignment.priority || 'normal').toLowerCase()}-priority">
                                    <div class="priority-badge priority-${(assignment.priority || 'normal').toLowerCase()}">${assignment.priority || 'Normal'}</div>
                                    <div class="request-time">${assignment.estimated_start_time || 'N/A'} (${assignment.estimated_duration || 30} min)</div>
                                    <div class="request-location">${assignment.pickup_location || 'Collection Location'}</div>
                                    <div class="request-details">
                                        <span>${assignment.waste_type || 'General Waste'} - ${assignment.estimated_volume || 1}L</span>
                                        <span>Seq: ${assignment.sequence_order || (index + 1)}</span>
                                    </div>
                                </div>
                            `).join('')}
                            
                            ${assignments.length === 0 ? '<div class="loading">No requests assigned</div>' : ''}
                        </div>
                    </div>
                `;
            });
            
            html += '</div>';
            container.innerHTML = html;
        }

        // Update Summary
        function updateSummary(data) {
            document.getElementById('total-requests').textContent = data.total_requests || 0;
            document.getElementById('drivers-assigned').textContent = data.drivers_used || 0;
            
            let totalVolume = 0;
            data.schedules.forEach(schedule => {
                totalVolume += schedule.total_volume || 0;
            });
            
            document.getElementById('total-volume').textContent = totalVolume;
            document.getElementById('schedule-date-display').textContent = data.date;
            document.getElementById('schedule-summary').style.display = 'block';
        }

        // Update Summary from View
        function updateSummaryFromView(schedules, date) {
            let totalRequests = 0;
            let totalVolume = 0;
            
            schedules.forEach(schedule => {
                totalRequests += schedule.total_requests || 0;
                totalVolume += schedule.total_volume || 0;
            });
            
            document.getElementById('total-requests').textContent = totalRequests;
            document.getElementById('drivers-assigned').textContent = schedules.length;
            document.getElementById('total-volume').textContent = totalVolume;
            document.getElementById('schedule-date-display').textContent = date;
            document.getElementById('schedule-summary').style.display = 'block';
        }

        // Show Loading
        function showLoading(message) {
            document.getElementById('schedule-container').innerHTML = `<div class="loading"><i class="fas fa-spinner fa-spin"></i> ${message}</div>`;
            document.getElementById('schedule-summary').style.display = 'none';
        }

        // Show Message
        function showMessage(message, type) {
            const container = document.getElementById('message-container');
            const className = type === 'error' ? 'error-message' : 'success-message';
            
            container.innerHTML = `<div class="${className}"><i class="fas fa-${type === 'error' ? 'exclamation-triangle' : 'check-circle'}"></i> ${message}</div>`;
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                container.innerHTML = '';
            }, 5000);
        }

        // Approve All Pending Requests
        function approveAllPending() {
            showLoading('Approving all pending requests...');
            
            fetch('../../backend/api/smart_scheduling.php?action=approve_all', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage(`Successfully approved ${data.approved_count || 0} pending requests!`, 'success');
                    // Optionally refresh the current schedule view
                    const date = document.getElementById('schedule-date').value;
                    if (date) {
                        setTimeout(() => viewSchedule(date), 1000);
                    }
                } else {
                    showMessage('Error approving requests: ' + (data.error || 'Unknown error'), 'error');
                }
                document.getElementById('schedule-container').innerHTML = '<div class="loading"><i class="fas fa-info-circle"></i> Select a date and action to continue</div>';
            })
            .catch(error => {
                showMessage('Network error: ' + error.message, 'error');
                document.getElementById('schedule-container').innerHTML = '<div class="loading"><i class="fas fa-exclamation-triangle"></i> Error occurred</div>';
            });
        }

        // Assign All to Drivers
        function assignAllToDrivers(date) {
            showLoading('Assigning all approved requests to drivers...');
            
            fetch(`../../backend/api/smart_scheduling.php?action=assign_all&date=${date}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const message = `Successfully assigned ${data.assigned_count || 0} requests to ${data.drivers_used || 0} drivers!`;
                    showMessage(message, 'success');
                    // Automatically show the schedule after assignment
                    setTimeout(() => viewSchedule(date), 1000);
                } else {
                    showMessage('Error assigning requests: ' + (data.error || 'Unknown error'), 'error');
                    document.getElementById('schedule-container').innerHTML = '<div class="loading"><i class="fas fa-exclamation-triangle"></i> Assignment failed</div>';
                }
            })
            .catch(error => {
                showMessage('Network error: ' + error.message, 'error');
                document.getElementById('schedule-container').innerHTML = '<div class="loading"><i class="fas fa-exclamation-triangle"></i> Error occurred</div>';
            });
        }
    </script>
</body>
</html>
