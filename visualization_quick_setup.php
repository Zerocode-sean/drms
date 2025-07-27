<?php
echo "<h2>🔧 Visualization Debug & Quick Setup</h2>";

// Database connection
$conn = new mysqli('localhost', 'root', '', 'drms2');
if ($conn->connect_error) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $conn->connect_error . "</p>";
    exit;
}

echo "<p style='color: green;'>✅ Database connected</p>";

// Check for tomorrow's date (default in visualization)
$tomorrow = date('Y-m-d', strtotime('+1 day'));
echo "<p>📅 Checking date: <strong>$tomorrow</strong></p>";

// Check if we have any schedules for tomorrow
$schedules_check = $conn->query("SELECT COUNT(*) as count FROM schedules WHERE schedule_date = '$tomorrow'");
$schedule_count = $schedules_check ? $schedules_check->fetch_assoc()['count'] : 0;

echo "<p>📊 Existing schedules for $tomorrow: <strong>$schedule_count</strong></p>";

if ($schedule_count == 0) {
    echo "<p style='color: orange;'>⚠️ No schedules found for tomorrow. Let's generate some...</p>";
    
    // First, let's check if we have any approved requests
    $requests_check = $conn->query("SELECT COUNT(*) as count FROM requests WHERE status = 'Approved'");
    $approved_count = $requests_check ? $requests_check->fetch_assoc()['count'] : 0;
    
    echo "<p>📋 Approved requests: <strong>$approved_count</strong></p>";
    
    if ($approved_count == 0) {
        echo "<p style='color: orange;'>⚠️ No approved requests. Let's approve some pending ones...</p>";
        
        // Approve pending requests
        $approve_result = $conn->query("UPDATE requests SET status = 'Approved' WHERE status = 'Pending' OR status IS NULL LIMIT 20");
        if ($approve_result) {
            $approved_new = $conn->affected_rows;
            echo "<p style='color: green;'>✅ Approved $approved_new requests</p>";
        }
    }
    
    // Now generate a schedule using the API
    echo "<p>⚡ Generating schedule...</p>";
    
    $api_url = "http://localhost/project/src/backend/api/smart_scheduling.php?action=generate&date=$tomorrow";
    $response = file_get_contents($api_url);
    $data = json_decode($response, true);
    
    if ($data && $data['success']) {
        echo "<p style='color: green;'>✅ Schedule generated successfully!</p>";
        echo "<p>📊 Generated: {$data['drivers_used']} drivers, {$data['total_requests']} requests</p>";
    } else {
        echo "<p style='color: red;'>❌ Failed to generate schedule</p>";
        echo "<pre>" . htmlspecialchars($response) . "</pre>";
    }
} else {
    echo "<p style='color: green;'>✅ Schedules already exist for tomorrow</p>";
}

// Test the view API
echo "<hr><h3>🧪 Testing View API</h3>";
$view_url = "http://localhost/project/src/backend/api/smart_scheduling.php?action=view&date=$tomorrow";
$view_response = file_get_contents($view_url);
$view_data = json_decode($view_response, true);

if ($view_data && $view_data['success']) {
    echo "<p style='color: green;'>✅ View API working correctly</p>";
    echo "<p>📊 Found " . count($view_data['schedules']) . " driver schedules</p>";
    
    foreach ($view_data['schedules'] as $i => $schedule) {
        $assignment_count = count($schedule['assignments'] ?? []);
        echo "<p>🚛 Driver " . ($i + 1) . ": {$schedule['driver_name']} ({$assignment_count} assignments)</p>";
    }
} else {
    echo "<p style='color: red;'>❌ View API failed</p>";
    echo "<pre>" . htmlspecialchars($view_response) . "</pre>";
}

echo "<hr>";
echo "<h3>🎯 Ready to Test!</h3>";
echo "<p>✅ <a href='visualize_clusters.html' target='_blank' style='color: #667eea; font-weight: bold;'>Open Visualization Map</a></p>";
echo "<p>📅 Date picker should default to: <strong>$tomorrow</strong></p>";
echo "<p>🔄 Click 'Load Data' to see the routes on the map</p>";

$conn->close();
?>
