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
    <title>DRMS Admin - Smart Scheduling Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { background: #007bff; color: white; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
        .controls { background: #f5f5f5; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
        .btn { background: #007bff; color: white; border: none; padding: 12px 24px; margin: 5px; border-radius: 3px; cursor: pointer; font-size: 14px; }
        .btn:hover { background: #0056b3; }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #1e7e34; }
        .date-input { padding: 10px; border: 1px solid #ddd; border-radius: 3px; margin: 5px; }
        .result { background: #fff; padding: 15px; border: 1px solid #ddd; border-radius: 5px; margin: 15px 0; }
        .success { color: green; background: #e6ffe6; }
        .error { color: red; background: #ffe6e6; }
        .debug { background: #f0f0f0; padding: 10px; border-radius: 3px; font-family: monospace; font-size: 12px; white-space: pre-wrap; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🧪 Smart Scheduling - Minimal Test Version</h1>
        <p>This is a simplified version to test if buttons work</p>
    </div>
    
    <div class="controls">
        <h2>📅 Schedule Controls</h2>
        <label for="test-date">Select Date:</label>
        <input type="date" id="test-date" class="date-input">
        <br><br>
        <button id="test-generate-btn" class="btn">🔄 Test Generate Schedule</button>
        <button id="test-view-btn" class="btn btn-success">👁️ Test View Schedule</button>
        <button id="test-api-btn" class="btn" style="background: #6c757d;">🔍 Test API Direct</button>
    </div>
    
    <div id="test-results" class="result" style="display: none;">
        <h3>Results</h3>
        <div id="test-output"></div>
    </div>
    
    <div class="result">
        <h3>🐛 Debug Console</h3>
        <div id="debug-console" class="debug">Waiting for button clicks...\n</div>
    </div>
    
    <div class="controls">
        <h3>🔗 Quick Links</h3>
        <a href="admin_smart_scheduling.php" target="_blank">Original Admin Smart Scheduling Page</a> |
        <a href="../../debug_schedule_button.html" target="_blank">Debug Tools</a> |
        <a href="../../final_scheduling_status.php" target="_blank">System Status</a>
    </div>

    <script>
        // Debug console
        function debugLog(message) {
            const debugConsole = document.getElementById('debug-console');
            const timestamp = new Date().toLocaleTimeString();
            debugConsole.textContent += `[${timestamp}] ${message}\n`;
            debugConsole.scrollTop = debugConsole.scrollHeight;
            console.log(message);
        }

        // Show results
        function showResults(title, content, isSuccess = true) {
            const resultsDiv = document.getElementById('test-results');
            const outputDiv = document.getElementById('test-output');
            resultsDiv.style.display = 'block';
            resultsDiv.className = `result ${isSuccess ? 'success' : 'error'}`;
            outputDiv.innerHTML = `<h4>${title}</h4><div>${content}</div>`;
        }

        // Wait for DOM to be ready
        document.addEventListener('DOMContentLoaded', function() {
            debugLog('🚀 DOMContentLoaded fired');
            
            // Set default date to tomorrow
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            const dateString = tomorrow.toISOString().split('T')[0];
            document.getElementById('test-date').value = dateString;
            debugLog(`📅 Date set to: ${dateString}`);
            
            // Test Generate Button
            const generateBtn = document.getElementById('test-generate-btn');
            if (generateBtn) {
                debugLog('✅ Generate button found, adding event listener');
                generateBtn.addEventListener('click', function() {
                    debugLog('🖱️ Generate button clicked!');
                    testGenerateSchedule();
                });
            } else {
                debugLog('❌ Generate button NOT found!');
            }
            
            // Test View Button
            const viewBtn = document.getElementById('test-view-btn');
            if (viewBtn) {
                debugLog('✅ View button found, adding event listener');
                viewBtn.addEventListener('click', function() {
                    debugLog('🖱️ View button clicked!');
                    testViewSchedule();
                });
            } else {
                debugLog('❌ View button NOT found!');
            }
            
            // Test API Button
            const apiBtn = document.getElementById('test-api-btn');
            if (apiBtn) {
                debugLog('✅ API test button found, adding event listener');
                apiBtn.addEventListener('click', function() {
                    debugLog('🖱️ API test button clicked!');
                    testAPIDirect();
                });
            } else {
                debugLog('❌ API test button NOT found!');
            }
            
            debugLog('🎯 All event listeners attached successfully');
        });

        async function testGenerateSchedule() {
            const date = document.getElementById('test-date').value;
            debugLog(`🔄 Testing generate schedule for date: ${date}`);
            
            if (!date) {
                showResults('Error', 'Please select a date', false);
                return;
            }
            
            showResults('Testing...', 'Calling generate schedule API...', true);
            
            try {
                const response = await fetch(`../../backend/api/smart_scheduling.php?action=generate&date=${date}`);
                const data = await response.json();
                
                debugLog(`📊 Generate API response: ${JSON.stringify(data)}`);
                
                if (data.success) {
                    const message = `Schedule generated! ${data.drivers_used || 0} drivers assigned to ${data.total_requests || 0} requests.`;
                    showResults('Generate Success', message, true);
                } else {
                    showResults('Generate Error', data.error || 'Unknown error', false);
                }
            } catch (error) {
                debugLog(`💥 Generate API error: ${error.message}`);
                showResults('Network Error', error.message, false);
            }
        }

        async function testViewSchedule() {
            const date = document.getElementById('test-date').value;
            debugLog(`👁️ Testing view schedule for date: ${date}`);
            
            if (!date) {
                showResults('Error', 'Please select a date', false);
                return;
            }
            
            showResults('Testing...', 'Calling view schedule API...', true);
            
            try {
                const response = await fetch(`../../backend/api/smart_scheduling.php?action=view&date=${date}`);
                const data = await response.json();
                
                debugLog(`📊 View API response: ${JSON.stringify(data)}`);
                
                if (data.success) {
                    const message = `Found ${data.schedules.length} schedules for ${date}`;
                    showResults('View Success', message, true);
                } else {
                    showResults('View Error', data.error || 'Unknown error', false);
                }
            } catch (error) {
                debugLog(`💥 View API error: ${error.message}`);
                showResults('Network Error', error.message, false);
            }
        }

        async function testAPIDirect() {
            debugLog('🔍 Testing API direct connection...');
            showResults('Testing...', 'Testing API endpoint directly...', true);
            
            try {
                const response = await fetch('../../backend/api/smart_scheduling.php?action=test');
                const text = await response.text();
                
                debugLog(`📡 Direct API response: ${text}`);
                showResults('API Direct Test', `Response: ${text.substring(0, 200)}...`, true);
            } catch (error) {
                debugLog(`💥 Direct API error: ${error.message}`);
                showResults('API Direct Error', error.message, false);
            }
        }
    </script>
</body>
</html>
