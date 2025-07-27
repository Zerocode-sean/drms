<?php
// Fresh Smart Scheduling API for DRMS2 - RESTORED VERSION
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

class SmartScheduler {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    /**
     * Generate optimized schedule for a specific date
     */
    public function generateSchedule($date = null) {
        if (!$date) {
            $date = date('Y-m-d', strtotime('+1 day')); // Default to tomorrow
        }
        
        try {
            // Step 1: Get approved requests for the date
            $requests = $this->getApprovedRequests($date);
            if (empty($requests)) {
                return [
                    'success' => true,
                    'message' => 'No approved requests found for ' . $date,
                    'schedules' => [],
                    'date' => $date
                ];
            }
            
            // Step 2: Get available drivers
            $drivers = $this->getAvailableDrivers();
            if (empty($drivers)) {
                return [
                    'success' => false,
                    'error' => 'No available drivers found',
                    'date' => $date
                ];
            }
            
            // Step 3: Clear existing schedules for the date
            $this->clearExistingSchedules($date);
            
            // Step 4: Generate optimized assignments
            $schedules = $this->optimizeAndAssign($requests, $drivers, $date);
            
            return [
                'success' => true,
                'message' => 'Schedule generated successfully',
                'schedules' => $schedules,
                'date' => $date,
                'total_requests' => count($requests),
                'drivers_used' => count($schedules)
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'date' => $date
            ];
        }
    }
    
    /**
     * Get approved requests for a specific date
     */
    private function getApprovedRequests($date) {
        // First check what columns exist in requests table
        $columns_check = $this->conn->query("DESCRIBE requests");
        $available_columns = [];
        while ($col = $columns_check->fetch_assoc()) {
            $available_columns[] = $col['Field'];
        }
        
        // Build query based on available columns
        $select_fields = "r.id, r.user_id, r.status, r.created_at, u.username";
        
        if (in_array('document', $available_columns)) {
            $select_fields .= ", r.document";
        }
        if (in_array('location', $available_columns)) {
            $select_fields .= ", r.location as pickup_location";
        } else {
            $select_fields .= ", 'Collection Location' as pickup_location";
        }
        if (in_array('waste_type', $available_columns)) {
            $select_fields .= ", r.waste_type";
        } else {
            $select_fields .= ", 'General Waste' as waste_type";
        }
        if (in_array('urgency', $available_columns)) {
            $select_fields .= ", r.urgency";
        } else {
            $select_fields .= ", 'Normal' as urgency";
        }
        if (in_array('preferred_date', $available_columns)) {
            $select_fields .= ", r.preferred_date";
        }
        
        $where_clause = "r.status = 'Approved'";
        if (in_array('preferred_date', $available_columns)) {
            $where_clause .= " AND r.preferred_date = '$date'";
        } else {
            $where_clause .= " AND DATE(r.created_at) = '$date'";
        }
        
        $sql = "SELECT $select_fields
                FROM requests r
                JOIN users u ON r.user_id = u.id
                WHERE $where_clause
                ORDER BY r.created_at ASC";
        
        $result = $this->conn->query($sql);
        $requests = [];
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $requests[] = $row;
            }
        }
        
        return $requests;
    }
    
    /**
     * Get available drivers
     */
    private function getAvailableDrivers() {
        $sql = "SELECT d.*, u.username 
                FROM drivers d
                JOIN users u ON d.user_id = u.id
                WHERE d.is_active = 1 
                AND (d.status IS NULL OR d.status = 'available')
                ORDER BY d.id ASC";
        
        $result = $this->conn->query($sql);
        $drivers = [];
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $drivers[] = $row;
            }
        }
        
        return $drivers;
    }
    
    /**
     * Clear existing schedules for a date
     */
    private function clearExistingSchedules($date) {
        // Check if schedules and schedule_assignments tables exist
        $schedules_check = $this->conn->query("SHOW TABLES LIKE 'schedules'");
        if ($schedules_check->num_rows > 0) {
            $assignments_check = $this->conn->query("SHOW TABLES LIKE 'schedule_assignments'");
            if ($assignments_check->num_rows > 0) {
                $this->conn->query("DELETE sa FROM schedule_assignments sa 
                                   JOIN schedules s ON sa.schedule_id = s.id 
                                   WHERE s.schedule_date = '$date'");
            }
            $this->conn->query("DELETE FROM schedules WHERE schedule_date = '$date'");
        }
        
        // Reset request status for this date
        $columns_check = $this->conn->query("DESCRIBE requests");
        $has_preferred_date = false;
        while ($col = $columns_check->fetch_assoc()) {
            if ($col['Field'] === 'preferred_date') {
                $has_preferred_date = true;
                break;
            }
        }
        
        if ($has_preferred_date) {
            $this->conn->query("UPDATE requests SET status = 'Approved' 
                               WHERE preferred_date = '$date' 
                               AND status IN ('Scheduled', 'Assigned')");
        } else {
            // If no preferred_date, reset recent requests
            $this->conn->query("UPDATE requests SET status = 'Approved' 
                               WHERE status IN ('Scheduled', 'Assigned')
                               AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
        }
    }
    
    /**
     * Optimize and assign requests to drivers
     */
    private function optimizeAndAssign($requests, $drivers, $date) {
        $schedules = [];
        
        // Group requests by priority and location proximity
        $prioritized_requests = $this->prioritizeRequests($requests);
        
        foreach ($drivers as $driver) {
            if (empty($prioritized_requests)) break;
            
            // Create schedule for this driver
            $schedule = $this->createDriverSchedule($driver, $date);
            $schedule_id = $schedule['id'];
            
            // Assign requests to this driver based on capacity and time
            $assigned_requests = $this->assignRequestsToDriver(
                $prioritized_requests, 
                $driver, 
                $schedule_id,
                $date
            );
            
            if (!empty($assigned_requests)) {
                $schedule['requests'] = $assigned_requests;
                $schedule['total_requests'] = count($assigned_requests);
                $schedule['total_volume'] = count($assigned_requests); // Use count instead of volume sum
                
                // Update schedule with totals
                $this->updateScheduleTotals($schedule_id, $schedule['total_requests'], $schedule['total_volume']);
                
                $schedules[] = $schedule;
            }
        }
        
        return $schedules;
    }
    
    /**
     * Prioritize requests by urgency, time, and location
     */
    private function prioritizeRequests($requests) {
        // Sort by urgency (High > Normal > Low) then by creation time
        usort($requests, function($a, $b) {
            $urgency_order = ['High' => 3, 'Normal' => 2, 'Low' => 1];
            
            $a_urgency = isset($a['urgency']) ? ($urgency_order[$a['urgency']] ?? 2) : 2; // Default to Normal
            $b_urgency = isset($b['urgency']) ? ($urgency_order[$b['urgency']] ?? 2) : 2;
            
            if ($a_urgency !== $b_urgency) {
                return $b_urgency - $a_urgency; // Higher urgency first
            }
            
            // Same urgency, sort by creation time (earlier first)
            $a_created = isset($a['created_at']) ? $a['created_at'] : '';
            $b_created = isset($b['created_at']) ? $b['created_at'] : '';
            return strcmp($a_created, $b_created);
        });
        
        return $requests;
    }
    
    /**
     * Create schedule for a driver
     */
    private function createDriverSchedule($driver, $date) {
        // Ensure schedules table exists
        $this->ensureSchedulesTableExists();
        
        $start_time = '08:00:00';
        $end_time = '17:00:00';
        
        $sql = "INSERT INTO schedules (driver_id, schedule_date, start_time, end_time, status) 
                VALUES (?, ?, ?, ?, 'draft')";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('isss', $driver['id'], $date, $start_time, $end_time);
        $stmt->execute();
        
        $schedule_id = $this->conn->insert_id;
        
        return [
            'id' => $schedule_id,
            'driver' => $driver,
            'date' => $date,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'requests' => []
        ];
    }
    
    /**
     * Assign requests to a specific driver
     */
    private function assignRequestsToDriver(&$available_requests, $driver, $schedule_id, $date) {
        // Check if schedule_assignments table exists, if not create it
        $tables_check = $this->conn->query("SHOW TABLES LIKE 'schedule_assignments'");
        if ($tables_check->num_rows === 0) {
            $this->conn->query("CREATE TABLE schedule_assignments (
                id INT AUTO_INCREMENT PRIMARY KEY,
                schedule_id INT NOT NULL,
                request_id INT NOT NULL,
                sequence_order INT NOT NULL,
                estimated_start_time TIME,
                estimated_duration INT DEFAULT 30,
                status ENUM('scheduled', 'in_progress', 'completed', 'skipped') DEFAULT 'scheduled',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");
        }
        
        $assigned = [];
        $current_volume = 0;
        $current_time = strtotime($driver['working_hours_start'] ?? '08:00:00');
        $end_time = strtotime($driver['working_hours_end'] ?? '17:00:00');
        $capacity = $driver['capacity'] ?? 10; // Use 10 as default to match actual driver capacity
        
        // Use a simple foreach loop instead of reverse loop with array manipulation
        foreach ($available_requests as $index => $request) {
            // Check if driver has capacity - FORCE volume to 1 since estimated_volume doesn't exist
            $volume = 1; // Always use 1 regardless of what's in the request
            if ($current_volume + $volume > $capacity) {
                continue;
            }
            
            // Check if there's time (estimate 30 minutes per request + 15 minutes travel)
            $estimated_duration = 45 * 60; // 45 minutes in seconds
            if ($current_time + $estimated_duration > $end_time) {
                continue;
            }
            
            // Assign this request
            $sequence = count($assigned) + 1;
            $estimated_start = date('H:i:s', $current_time);
            
            $assignment_sql = "INSERT INTO schedule_assignments 
                              (schedule_id, request_id, sequence_order, estimated_start_time, estimated_duration) 
                              VALUES (?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($assignment_sql);
            $duration_minutes = 30;
            $stmt->bind_param('iiisi', $schedule_id, $request['id'], $sequence, $estimated_start, $duration_minutes);
            
            if ($stmt->execute()) {
                // Update request status
                $this->conn->query("UPDATE requests SET status = 'Scheduled' WHERE id = " . $request['id']);
                
                // Add to assigned list
                $request['estimated_start_time'] = $estimated_start;
                $request['estimated_duration'] = $duration_minutes;
                $request['sequence_order'] = $sequence;
                $assigned[] = $request;
                
                // Update tracking variables
                $current_volume += $volume;
                $current_time += $estimated_duration;
                
                // Remove from available requests by unsetting
                unset($available_requests[$index]);
            }
        }
        
        // Re-index the array after unsetting elements
        $available_requests = array_values($available_requests);
        
        return $assigned;
    }
    
    /**
     * Update schedule with final totals
     */
    private function updateScheduleTotals($schedule_id, $total_requests, $total_volume) {
        $sql = "UPDATE schedules 
                SET total_requests = ?, total_volume = ?, status = 'confirmed' 
                WHERE id = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('iii', $total_requests, $total_volume, $schedule_id);
        $stmt->execute();
    }
    
    /**
     * Ensure schedules table exists
     */
    private function ensureSchedulesTableExists() {
        $tables_check = $this->conn->query("SHOW TABLES LIKE 'schedules'");
        if ($tables_check->num_rows === 0) {
            $this->conn->query("CREATE TABLE schedules (
                id INT AUTO_INCREMENT PRIMARY KEY,
                driver_id INT NOT NULL,
                schedule_date DATE NOT NULL,
                start_time TIME DEFAULT '08:00:00',
                end_time TIME DEFAULT '17:00:00',
                total_requests INT DEFAULT 0,
                total_volume INT DEFAULT 0,
                status ENUM('draft', 'confirmed', 'in_progress', 'completed') DEFAULT 'draft',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY unique_driver_date (driver_id, schedule_date)
            )");
        }
    }
    
    /**
     * View existing schedules for a date
     */
    public function viewSchedules($date) {
        $sql = "SELECT s.*, d.license_number, u.username as driver_name
                FROM schedules s
                JOIN drivers d ON s.driver_id = d.id
                JOIN users u ON d.user_id = u.id
                WHERE s.schedule_date = ?
                ORDER BY s.start_time ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $date);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $schedules = [];
        while ($schedule = $result->fetch_assoc()) {
            // First check what columns exist in requests table for assignments
            $columns_check = $this->conn->query("DESCRIBE requests");
            $available_columns = [];
            while ($col = $columns_check->fetch_assoc()) {
                $available_columns[] = $col['Field'];
            }
            
            // Build assignment query based on available columns
            $assignment_fields = "sa.*, r.id as request_id, r.user_id, r.document, r.status";
            
            if (in_array('pickup_location', $available_columns)) {
                $assignment_fields .= ", r.pickup_location";
            } else if (in_array('location', $available_columns)) {
                $assignment_fields .= ", r.location as pickup_location";
            } else {
                $assignment_fields .= ", 'Collection Location' as pickup_location";
            }
            
            if (in_array('waste_type', $available_columns)) {
                $assignment_fields .= ", r.waste_type";
            } else {
                $assignment_fields .= ", 'General Waste' as waste_type";
            }
            
            if (in_array('priority', $available_columns)) {
                $assignment_fields .= ", r.priority";
            } else {
                $assignment_fields .= ", 'Normal' as priority";
            }
            
            if (in_array('estimated_volume', $available_columns)) {
                $assignment_fields .= ", r.estimated_volume";
            } else {
                $assignment_fields .= ", 1 as estimated_volume";
            }
            
            // Get assignments for this schedule
            $assignments_sql = "SELECT $assignment_fields
                               FROM schedule_assignments sa
                               JOIN requests r ON sa.request_id = r.id
                               WHERE sa.schedule_id = ?
                               ORDER BY sa.sequence_order ASC";
            
            $stmt2 = $this->conn->prepare($assignments_sql);
            $stmt2->bind_param('i', $schedule['id']);
            $stmt2->execute();
            $assignments_result = $stmt2->get_result();
            
            $assignments = [];
            while ($assignment = $assignments_result->fetch_assoc()) {
                $assignments[] = $assignment;
            }
            
            $schedule['assignments'] = $assignments;
            $schedules[] = $schedule;
        }
        
        return [
            'success' => true,
            'schedules' => $schedules,
            'date' => $date
        ];
    }
}

// Initialize database connection
$conn = new mysqli('localhost', 'root', '', 'drms2');
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'error' => 'Database connection failed: ' . $conn->connect_error]));
}

// Create scheduler instance
$scheduler = new SmartScheduler($conn);

// Handle requests
$action = $_GET['action'] ?? 'generate';
$date = $_GET['date'] ?? date('Y-m-d', strtotime('+1 day'));

if ($action === 'view') {
    $response = $scheduler->viewSchedules($date);
} else {
    $response = $scheduler->generateSchedule($date);
}

// Close connection
$conn->close();

// Return JSON response
echo json_encode($response);
?>
