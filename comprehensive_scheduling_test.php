<?php
// Comprehensive Smart Scheduling Test - 50+ Requests with Geographic Clustering

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "drms2";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h1>🧪 Comprehensive Smart Scheduling Test - Geographic Clustering</h1>";

// First, approve ALL pending requests
echo "<h2>✅ Step 1: Approve All Pending Requests</h2>";

$approveQuery = "UPDATE requests SET status = 'Approved' WHERE status IN ('Pending', 'pending', 'Submitted', 'submitted')";
$approveResult = $conn->query($approveQuery);

if ($approveResult) {
    $approvedCount = $conn->affected_rows;
    echo "<div style='color: green;'>✅ Approved $approvedCount existing requests</div>";
} else {
    echo "<div style='color: red;'>❌ Error approving requests: " . $conn->error . "</div>";
}

// Generate 50+ diverse requests with realistic locations across different areas
echo "<h2>🗺️ Step 2: Seed 50+ Requests with Geographic Diversity</h2>";

$locations = [
    // Central Business District (CBD) - Cluster 1
    ['Downtown Office Complex', 'CBD', -1.2864, 36.8172],
    ['City Center Plaza', 'CBD', -1.2869, 36.8175],
    ['Business Park Tower', 'CBD', -1.2860, 36.8170],
    ['Financial District', 'CBD', -1.2867, 36.8174],
    ['Central Mall', 'CBD', -1.2862, 36.8171],
    
    // Westlands Area - Cluster 2  
    ['Westlands Shopping Center', 'Westlands', -1.2676, 36.8108],
    ['Sarit Centre', 'Westlands', -1.2680, 36.8112],
    ['ABC Place', 'Westlands', -1.2674, 36.8106],
    ['Westgate Mall', 'Westlands', -1.2678, 36.8110],
    ['Parklands Estate', 'Westlands', -1.2672, 36.8104],
    
    // Kilimani Area - Cluster 3
    ['Kilimani Apartments', 'Kilimani', -1.2980, 36.7876],
    ['Yaya Centre', 'Kilimani', -1.2985, 36.7880],
    ['Prestige Plaza', 'Kilimani', -1.2978, 36.7874],
    ['Galana Plaza', 'Kilimani', -1.2982, 36.7878],
    ['Kilimani Heights', 'Kilimani', -1.2976, 36.7872],
    
    // Karen Area - Cluster 4
    ['Karen Shopping Centre', 'Karen', -1.3197, 36.6850],
    ['Karen Country Club', 'Karen', -1.3200, 36.6855],
    ['Karen C Estate', 'Karen', -1.3195, 36.6848],
    ['Dagoretti Corner', 'Karen', -1.3202, 36.6857],
    ['Karen Blixen', 'Karen', -1.3193, 36.6846],
    
    // Eastlands - Cluster 5
    ['Eastleigh Shopping', 'Eastleigh', -1.2833, 36.8444],
    ['Garissa Lodge', 'Eastleigh', -1.2836, 36.8448],
    ['First Avenue', 'Eastleigh', -1.2831, 36.8442],
    ['Eastleigh Market', 'Eastleigh', -1.2838, 36.8450],
    ['Pumwani Estate', 'Eastleigh', -1.2829, 36.8440],
    
    // Industrial Area - Cluster 6
    ['Industrial Complex A', 'Industrial Area', -1.3236, 36.8283],
    ['Factory Zone B', 'Industrial Area', -1.3240, 36.8287],
    ['Warehouse District', 'Industrial Area', -1.3234, 36.8281],
    ['Manufacturing Hub', 'Industrial Area', -1.3242, 36.8289],
    ['Logistics Center', 'Industrial Area', -1.3232, 36.8279],
    
    // Ngong Road Area - Cluster 7
    ['Ngong Road Plaza', 'Ngong Road', -1.3100, 36.7650],
    ['Junction Mall', 'Ngong Road', -1.3105, 36.7655],
    ['Ngong Racecourse', 'Ngong Road', -1.3098, 36.7648],
    ['Adams Arcade', 'Ngong Road', -1.3107, 36.7657],
    ['Cooperative House', 'Ngong Road', -1.3096, 36.7646],
    
    // Kasarani Area - Cluster 8
    ['Kasarani Stadium', 'Kasarani', -1.2167, 36.8917],
    ['Thika Road Mall', 'Kasarani', -1.2170, 36.8920],
    ['Roysambu', 'Kasarani', -1.2165, 36.8915],
    ['Kasarani Sports Club', 'Kasarani', -1.2172, 36.8922],
    ['Garden City Mall', 'Kasarani', -1.2163, 36.8913],
    
    // Langata Area - Cluster 9
    ['Langata Shopping', 'Langata', -1.3667, 36.7333],
    ['Galleria Mall', 'Langata', -1.3670, 36.7337],
    ['Nairobi National Park', 'Langata', -1.3665, 36.7331],
    ['Wilson Airport', 'Langata', -1.3672, 36.7339],
    ['Langata Cemetery', 'Langata', -1.3663, 36.7329],
    
    // Upperhill Area - Cluster 10
    ['Upperhill Medical', 'Upperhill', -1.2925, 36.8206],
    ['Nairobi Hospital', 'Upperhill', -1.2928, 36.8210],
    ['CBA Building', 'Upperhill', -1.2923, 36.8204],
    ['Britam Tower', 'Upperhill', -1.2930, 36.8212],
    ['Anniversary Towers', 'Upperhill', -1.2921, 36.8202],
];

$wasteTypes = [
    'General Waste', 'Organic Waste', 'Recyclable Materials', 'Electronic Waste', 
    'Hazardous Waste', 'Construction Debris', 'Garden Waste', 'Plastic Waste',
    'Paper Waste', 'Glass Waste', 'Metal Scrap', 'Textile Waste'
];

$priorities = ['Low', 'Normal', 'High', 'Urgent'];
$volumes = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]; // Volume in cubic meters

$today = date('Y-m-d');
$requestsAdded = 0;

// Get existing user IDs for assignment
$userQuery = "SELECT id FROM users WHERE role IN ('resident', 'user') LIMIT 10";
$userResult = $conn->query($userQuery);
$userIds = [];
while ($row = $userResult->fetch_assoc()) {
    $userIds[] = $row['id'];
}

if (empty($userIds)) {
    // Create some test users if none exist
    for ($i = 1; $i <= 5; $i++) {
        $insertUser = "INSERT INTO users (username, email, password, role) VALUES 
                      ('test_user_$i', 'user$i@test.com', 'password123', 'resident')";
        $conn->query($insertUser);
        $userIds[] = $conn->insert_id;
    }
}

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<strong>🎯 Testing Geographic Clustering Algorithm:</strong><br>";
echo "• Creating requests across 10 distinct geographic clusters<br>";
echo "• Each cluster contains 5+ locations within 2km radius<br>";
echo "• Testing driver assignment optimization based on proximity<br>";
echo "• Varied collection times, waste types, and priorities<br>";
echo "</div>";

foreach ($locations as $index => $location) {
    $userId = $userIds[array_rand($userIds)];
    $wasteType = $wasteTypes[array_rand($wasteTypes)];
    $priority = $priorities[array_rand($priorities)];
    $volume = $volumes[array_rand($volumes)];
    
    // Vary collection times throughout the day
    $hour = 8 + ($index % 9); // 8 AM to 4 PM
    $minute = [0, 15, 30, 45][array_rand([0, 15, 30, 45])];
    $preferredTime = sprintf("%02d:%02d:00", $hour, $minute);
    
    $document = "Waste Collection Request - " . $location[0];
    $notes = "Collection of $wasteType from {$location[0]} in {$location[1]} area. Volume: {$volume}m³, Priority: $priority. Lat: {$location[2]}, Lng: {$location[3]}";
    
    $insertQuery = "INSERT INTO requests (
        user_id, document, location, waste_type, urgency, 
        preferred_date, status, created_at, notes
    ) VALUES (
        ?, ?, ?, ?, ?, ?, 'Approved', NOW(), ?
    )";
    
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param("issssss", 
        $userId, $document, $location[0], $wasteType, $priority,
        $today, $notes
    );
    
    if ($stmt->execute()) {
        $requestsAdded++;
        echo "<span style='color: green;'>✅</span> ";
    } else {
        echo "<span style='color: red;'>❌</span> ";
    }
    
    // Add some visual progress
    if ($requestsAdded % 10 == 0) {
        echo "<br><strong>Progress: $requestsAdded requests added...</strong><br>";
    }
}

echo "<br><div style='color: green; font-weight: bold;'>🎉 Successfully added $requestsAdded geographic requests!</div>";

// Now test the scheduling algorithm
echo "<h2>🚀 Step 3: Test Smart Scheduling Algorithm</h2>";

echo "<div style='margin: 20px 0;'>";
echo "<a href='../../backend/api/smart_scheduling.php?action=generate&date=$today' target='_blank' style='display: inline-block; padding: 15px 30px; background: #007bff; color: white; text-decoration: none; border-radius: 8px; margin: 10px;'>🚀 Generate Today's Schedule</a>";
echo "<a href='../../backend/api/smart_scheduling.php?action=view&date=$today' target='_blank' style='display: inline-block; padding: 15px 30px; background: #28a745; color: white; text-decoration: none; border-radius: 8px; margin: 10px;'>👁️ View Generated Schedule</a>";
echo "</div>";

echo "<h2>📊 Step 4: Test Admin Interface</h2>";
echo "<div style='margin: 20px 0;'>";
echo "<a href='admin_smart_scheduling.php?date=$today' target='_blank' style='display: inline-block; padding: 15px 30px; background: #6f42c1; color: white; text-decoration: none; border-radius: 8px; margin: 10px;'>🎯 Main Admin Interface</a>";
echo "<a href='admin_smart_scheduling_debug.php?date=$today' target='_blank' style='display: inline-block; padding: 15px 30px; background: #fd7e14; color: white; text-decoration: none; border-radius: 8px; margin: 10px;'>🔧 Debug Interface</a>";
echo "</div>";

// Display summary statistics
$statsQuery = "SELECT 
    COUNT(*) as total_requests,
    COUNT(DISTINCT location) as unique_locations,
    COUNT(DISTINCT waste_type) as waste_types
FROM requests 
WHERE preferred_date = '$today' AND status = 'Approved'";

$statsResult = $conn->query($statsQuery);
$stats = $statsResult->fetch_assoc();

echo "<h2>📈 Test Data Summary</h2>";
echo "<div style='background: #e9ecef; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<div style='display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;'>";
echo "<div><strong>Total Requests:</strong> " . $stats['total_requests'] . "</div>";
echo "<div><strong>Unique Locations:</strong> " . $stats['unique_locations'] . "</div>";
echo "<div><strong>Waste Types:</strong> " . $stats['waste_types'] . "</div>";
echo "<div><strong>Date:</strong> $today</div>";
echo "</div></div>";

echo "<h2>🎯 Expected Algorithm Behavior</h2>";
echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<strong>Geographic Clustering:</strong><br>";
echo "• Drivers should be assigned to clusters within 2km radius<br>";
echo "• Routes should minimize travel distance between locations<br>";
echo "• Priority requests should be scheduled earlier in the day<br>";
echo "• High-volume requests should be distributed across drivers<br>";
echo "• Time preferences should be respected where possible<br>";
echo "</div>";

echo "<h2>🏁 Next Steps</h2>";
echo "<ol>";
echo "<li><strong>Generate Schedule:</strong> Click 'Generate Today's Schedule' to see the algorithm in action</li>";
echo "<li><strong>Analyze Clustering:</strong> Check if drivers are assigned to geographic clusters</li>";
echo "<li><strong>Verify Optimization:</strong> Ensure routes minimize travel distance</li>";
echo "<li><strong>Test Interface:</strong> Use the admin interface to generate and view schedules</li>";
echo "<li><strong>Performance Review:</strong> Evaluate how well the 2km clustering works</li>";
echo "</ol>";

$conn->close();
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f8f9fa; }
h1, h2 { color: #333; }
a { text-decoration: none; }
.highlight { background: yellow; padding: 2px 4px; }
</style>
