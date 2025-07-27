<?php
// Bypass version for testing - no login required
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DRMS Admin - Smart Scheduling (Test)</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .header { background: #007bff; color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
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
        #schedule-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
            min-height: 200px;
        }
        #message-container {
            margin: 15px 0;
        }
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 5px;
            border: 1px solid #c3e6cb;
        }
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 5px;
            border: 1px solid #f5c6cb;
        }
        .loading {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        .debug {
            background: #fff3cd;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            border: 1px solid #ffeaa7;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🧪 Smart Scheduling - Debug Version</h1>
        <p>This version bypasses login for testing button functionality</p>
    </div>

    <div class="scheduling-header">
        <div>
            <h2>Intelligent Waste Collection Scheduling</h2>
            <p>Test version - buttons should work here</p>
        </div>
        <div class="scheduling-controls">
            <input type="date" id="schedule-date" class="date-picker" value="">
            <button id="generate-schedule-btn" class="btn-primary">
                <i class="fas fa-magic"></i> Generate Schedule
            </button>
            <button id="view-schedule-btn" class="btn-success">
                <i class="fas fa-eye"></i> View Schedule
            </button>
        </div>
    </div>

    <div id="message-container"></div>
    
    <div id="schedule-container">
        <div class="loading">
            <i class="fas fa-info-circle"></i> Select a date and click "Generate Schedule" or "View Schedule" to begin
        </div>
    </div>

    <div class="debug">
        <h3>🐛 Debug Info</h3>
        <div id="debug-output">Initializing...</div>
    </div>

    <script>
        function debugLog(message) {
            const debugOutput = document.getElementById('debug-output');
            const timestamp = new Date().toLocaleTimeString();
            debugOutput.innerHTML += `<br>[${timestamp}] ${message}`;
            console.log(message);
        }

        // Initialize when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            debugLog('🚀 DOMContentLoaded fired');
            
            // Set default date to tomorrow
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            const dateString = tomorrow.toISOString().split('T')[0];
            const dateInput = document.getElementById('schedule-date');
            
            if (dateInput) {
                dateInput.value = dateString;
                debugLog('✅ Date input set to: ' + dateString);
            } else {
                debugLog('❌ Date input not found');
            }
            
            // Generate Schedule button
            const generateBtn = document.getElementById('generate-schedule-btn');
            if (generateBtn) {
                debugLog('✅ Generate button found');
                generateBtn.addEventListener('click', function() {
                    debugLog('🖱️ Generate button clicked!');
                    const date = document.getElementById('schedule-date').value;
                    if (!date) {
                        showMessage('Please select a date', 'error');
                        return;
                    }
                    testGenerateSchedule(date);
                });
                debugLog('✅ Generate button event listener attached');
            } else {
                debugLog('❌ Generate button NOT found');
            }

            // View Schedule button
            const viewBtn = document.getElementById('view-schedule-btn');
            if (viewBtn) {
                debugLog('✅ View button found');
                viewBtn.addEventListener('click', function() {
                    debugLog('🖱️ View button clicked!');
                    const date = document.getElementById('schedule-date').value;
                    if (!date) {
                        showMessage('Please select a date', 'error');
                        return;
                    }
                    testViewSchedule(date);
                });
                debugLog('✅ View button event listener attached');
            } else {
                debugLog('❌ View button NOT found');
            }
            
            debugLog('🎯 Initialization complete');
        });

        function showMessage(message, type) {
            const container = document.getElementById('message-container');
            const className = type === 'error' ? 'error-message' : 'success-message';
            container.innerHTML = `<div class="${className}">${message}</div>`;
            
            setTimeout(() => {
                container.innerHTML = '';
            }, 5000);
        }

        function showLoading(message) {
            document.getElementById('schedule-container').innerHTML = `<div class="loading">${message}</div>`;
        }

        async function testGenerateSchedule(date) {
            debugLog(`🔄 Testing generate schedule for: ${date}`);
            showLoading('Testing generate schedule API...');
            
            try {
                const response = await fetch(`../../backend/api/smart_scheduling.php?action=generate&date=${date}`);
                const data = await response.json();
                
                debugLog(`📊 API Response: ${JSON.stringify(data)}`);
                
                if (data.success) {
                    const driversUsed = data.drivers_used || 0;
                    const totalRequests = data.total_requests || 0;
                    const message = `Schedule generated successfully! ${driversUsed} drivers assigned to ${totalRequests} requests.`;
                    showMessage(message, 'success');
                    
                    document.getElementById('schedule-container').innerHTML = `
                        <h3>✅ Generate Test Results</h3>
                        <p><strong>Success:</strong> ${data.success}</p>
                        <p><strong>Drivers Used:</strong> ${driversUsed}</p>
                        <p><strong>Total Requests:</strong> ${totalRequests}</p>
                        <p><strong>Schedules:</strong> ${data.schedules ? data.schedules.length : 0}</p>
                        <details>
                            <summary>Full Response</summary>
                            <pre>${JSON.stringify(data, null, 2)}</pre>
                        </details>
                    `;
                } else {
                    showMessage('Error: ' + (data.error || 'Unknown error'), 'error');
                    document.getElementById('schedule-container').innerHTML = `
                        <h3>❌ Generate Test Failed</h3>
                        <p><strong>Error:</strong> ${data.error || 'Unknown error'}</p>
                    `;
                }
            } catch (error) {
                debugLog(`💥 Generate API error: ${error.message}`);
                showMessage('Network error: ' + error.message, 'error');
            }
        }

        async function testViewSchedule(date) {
            debugLog(`👁️ Testing view schedule for: ${date}`);
            showLoading('Testing view schedule API...');
            
            try {
                const response = await fetch(`../../backend/api/smart_scheduling.php?action=view&date=${date}`);
                const data = await response.json();
                
                debugLog(`📊 View API Response: ${JSON.stringify(data)}`);
                
                if (data.success) {
                    showMessage(`Found ${data.schedules.length} schedules`, 'success');
                    
                    document.getElementById('schedule-container').innerHTML = `
                        <h3>✅ View Test Results</h3>
                        <p><strong>Success:</strong> ${data.success}</p>
                        <p><strong>Schedules Found:</strong> ${data.schedules.length}</p>
                        <p><strong>Date:</strong> ${data.date}</p>
                        <details>
                            <summary>Full Response</summary>
                            <pre>${JSON.stringify(data, null, 2)}</pre>
                        </details>
                    `;
                } else {
                    showMessage('Error: ' + (data.error || 'Unknown error'), 'error');
                }
            } catch (error) {
                debugLog(`💥 View API error: ${error.message}`);
                showMessage('Network error: ' + error.message, 'error');
            }
        }
    </script>
</body>
</html>
