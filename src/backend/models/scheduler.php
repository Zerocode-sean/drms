<?php
require_once __DIR__ . '/../config/db_config.php';

class Scheduler {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * Main scheduling algorithm for waste collection
     * Groups requests by proximity and optimizes routes
     */
    public function scheduleCollectionRequests($date = null) {
        if (!$date) {
            $date = date('Y-m-d');
        }
        
        try {
            // Get all pending requests for the date
            $requests = $this->getPendingRequests($date);
            
            if (empty($requests)) {
                return [
                    'date' => $date,
                    'total_requests' => 0,
                    'clusters' => 0,
                    'assignments' => [],
                    'estimated_fuel_savings' => [
                        'individual_distance' => 0,
                        'optimized_distance' => 0,
                        'distance_saved' => 0,
                        'savings_percentage' => 0
                    ],
                    'message' => 'No pending requests for ' . $date
                ];
            }
            
            // Step 1: Geographic clustering
            $clusters = $this->clusterRequestsByProximity($requests);
            
            // Step 2: Time slot assignment
            $timeSlots = $this->assignTimeSlots($clusters);
            
            // Step 3: Driver assignment
            $assignments = $this->assignDrivers($timeSlots);
            
            // Step 4: Route optimization
            $optimizedRoutes = $this->optimizeRoutes($assignments);
            
            return [
                'date' => $date,
                'total_requests' => count($requests),
                'clusters' => count($clusters),
                'assignments' => $optimizedRoutes,
                'estimated_fuel_savings' => $this->calculateFuelSavings($requests, $optimizedRoutes)
            ];
        } catch (Exception $e) {
            error_log("Scheduler error: " . $e->getMessage());
            return [
                'date' => $date,
                'total_requests' => 0,
                'clusters' => 0,
                'assignments' => [],
                'estimated_fuel_savings' => [
                    'individual_distance' => 0,
                    'optimized_distance' => 0,
                    'distance_saved' => 0,
                    'savings_percentage' => 0
                ],
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get all pending requests for a specific date
     */
    private function getPendingRequests($date) {
        $sql = "SELECT r.*, u.latitude, u.longitude, u.address 
                FROM requests1 r 
                JOIN users u ON r.user_id = u.id 
                WHERE r.preferred_date = ?
                AND r.status IN ('Pending', 'Approved')
                ORDER BY r.preferred_date ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $date);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $requests = $result->fetch_all(MYSQLI_ASSOC);
        
        // Debug: Log the query and results
        error_log("Scheduler: Query for date $date returned " . count($requests) . " requests");
        
        return $requests;
    }
    
    /**
     * Cluster requests by geographic proximity
     * Uses a simple distance-based clustering algorithm
     */
    private function clusterRequestsByProximity($requests) {
        $clusters = [];
        $processed = [];
        
        foreach ($requests as $request) {
            if (in_array($request['id'], $processed)) {
                continue;
            }
            
            // Skip requests without valid coordinates
            if (!$this->isValidCoordinate($request['latitude'], $request['longitude'])) {
                error_log("Scheduler: Skipping request {$request['id']} - invalid coordinates: {$request['latitude']}, {$request['longitude']}");
                continue;
            }
            
            $cluster = [$request];
            $processed[] = $request['id'];
            
            // Find nearby requests (within 2km radius)
            foreach ($requests as $otherRequest) {
                if (in_array($otherRequest['id'], $processed)) {
                    continue;
                }
                
                // Skip requests without valid coordinates
                if (!$this->isValidCoordinate($otherRequest['latitude'], $otherRequest['longitude'])) {
                    continue;
                }
                
                $distance = $this->calculateDistance(
                    $request['latitude'], $request['longitude'],
                    $otherRequest['latitude'], $otherRequest['longitude']
                );
                
                // If within 2km and similar time preference
                if ($distance <= 2.0 && $this->isTimeCompatible($request, $otherRequest)) {
                    $cluster[] = $otherRequest;
                    $processed[] = $otherRequest['id'];
                }
            }
            
            $clusters[] = $cluster;
        }
        
        return $clusters;
    }
    
    /**
     * Validate if coordinates are valid
     */
    private function isValidCoordinate($lat, $lon) {
        // Check if coordinates are not empty and are numeric
        if (empty($lat) || empty($lon) || !is_numeric($lat) || !is_numeric($lon)) {
            return false;
        }
        
        // Check if coordinates are within valid ranges
        if ($lat < -90 || $lat > 90 || $lon < -180 || $lon > 180) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Calculate distance between two points using Haversine formula
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2) {
        // Validate inputs
        if (!$this->isValidCoordinate($lat1, $lon1) || !$this->isValidCoordinate($lat2, $lon2)) {
            return PHP_FLOAT_MAX; // Return maximum distance for invalid coordinates
        }
        
        $earthRadius = 6371; // Earth's radius in kilometers
        
        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);
        
        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lonDelta / 2) * sin($lonDelta / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return $earthRadius * $c;
    }
    
    /**
     * Check if two requests have compatible time preferences
     * Since we only have dates, we'll consider all requests on the same date as compatible
     */
    private function isTimeCompatible($request1, $request2) {
        // Since preferred_date is just a date (no time), all requests on the same date are compatible
        return $request1['preferred_date'] === $request2['preferred_date'];
    }
    
    /**
     * Assign time slots to clusters
     * Morning (8-12), Afternoon (12-4), Evening (4-8)
     */
    private function assignTimeSlots($clusters) {
        $timeSlots = [
            'morning' => [],
            'afternoon' => [],
            'evening' => []
        ];
        
        foreach ($clusters as $cluster) {
            $avgTime = $this->getAveragePreferredTime($cluster);
            $hour = (int)date('H', strtotime($avgTime));
            
            if ($hour >= 8 && $hour < 12) {
                $timeSlots['morning'][] = $cluster;
            } elseif ($hour >= 12 && $hour < 16) {
                $timeSlots['afternoon'][] = $cluster;
            } else {
                $timeSlots['evening'][] = $cluster;
            }
        }
        
        return $timeSlots;
    }
    
    /**
     * Get average preferred time for a cluster
     * Since preferred_date is just a date, we'll default to morning (10:00)
     */
    private function getAveragePreferredTime($cluster) {
        // For now, default to 10:00 AM since we only have dates, not times
        // In the future, this could be enhanced to use a preferred_time field
        return '10:00:00';
    }
    
    /**
     * Assign drivers to time slots
     * Considers driver availability and workload
     */
    private function assignDrivers($timeSlots) {
        $assignments = [];
        $availableDrivers = $this->getAvailableDrivers();
        
        foreach ($timeSlots as $slot => $clusters) {
            if (empty($clusters)) continue;
            
            // Assign clusters to available drivers
            $driverIndex = 0;
            foreach ($clusters as $cluster) {
                if (empty($availableDrivers)) {
                    // If no drivers available, mark for manual assignment
                    $assignments[] = [
                        'time_slot' => $slot,
                        'cluster' => $cluster,
                        'driver_id' => null,
                        'status' => 'needs_assignment'
                    ];
                } else {
                    $assignments[] = [
                        'time_slot' => $slot,
                        'cluster' => $cluster,
                        'driver_id' => $availableDrivers[$driverIndex % count($availableDrivers)]['id'],
                        'driver_name' => $availableDrivers[$driverIndex % count($availableDrivers)]['name'],
                        'status' => 'assigned'
                    ];
                    $driverIndex++;
                }
            }
        }
        
        return $assignments;
    }
    
    /**
     * Get available drivers
     */
    private function getAvailableDrivers() {
        $sql = "SELECT id, name, phone, vehicle_number as vehicle_type, 'normal' as capacity 
                FROM drivers 
                WHERE status = 'available' 
                ORDER BY id ASC";
        
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Optimize routes using nearest neighbor algorithm
     * Simple but effective for small to medium clusters
     */
    private function optimizeRoutes($assignments) {
        $optimizedRoutes = [];
        
        foreach ($assignments as $assignment) {
            if ($assignment['status'] !== 'assigned') {
                $optimizedRoutes[] = $assignment;
                continue;
            }
            
            $cluster = $assignment['cluster'];
            
            // Simple nearest neighbor optimization
            $route = $this->nearestNeighborRoute($cluster);
            
            $optimizedRoutes[] = [
                'time_slot' => $assignment['time_slot'],
                'driver_id' => $assignment['driver_id'],
                'driver_name' => $assignment['driver_name'],
                'route' => $route,
                'total_distance' => $this->calculateRouteDistance($route),
                'estimated_duration' => $this->estimateRouteDuration($route),
                'status' => 'optimized'
            ];
        }
        
        return $optimizedRoutes;
    }
    
    /**
     * Nearest neighbor algorithm for route optimization
     */
    private function nearestNeighborRoute($cluster) {
        if (count($cluster) <= 1) {
            return $cluster;
        }
        
        $route = [];
        $unvisited = $cluster;
        
        // Start with the first location
        $current = array_shift($unvisited);
        $route[] = $current;
        
        while (!empty($unvisited)) {
            $nearest = null;
            $minDistance = PHP_FLOAT_MAX;
            
            foreach ($unvisited as $index => $location) {
                $distance = $this->calculateDistance(
                    $current['latitude'], $current['longitude'],
                    $location['latitude'], $location['longitude']
                );
                
                if ($distance < $minDistance) {
                    $minDistance = $distance;
                    $nearest = $index;
                }
            }
            
            $current = $unvisited[$nearest];
            $route[] = $current;
            unset($unvisited[$nearest]);
        }
        
        return $route;
    }
    
    /**
     * Calculate total distance for a route
     */
    private function calculateRouteDistance($route) {
        $totalDistance = 0;
        
        for ($i = 0; $i < count($route) - 1; $i++) {
            $totalDistance += $this->calculateDistance(
                $route[$i]['latitude'], $route[$i]['longitude'],
                $route[$i + 1]['latitude'], $route[$i + 1]['longitude']
            );
        }
        
        return round($totalDistance, 2);
    }
    
    /**
     * Estimate route duration (including pickup time)
     */
    private function estimateRouteDuration($route) {
        $totalDistance = $this->calculateRouteDistance($route);
        $avgSpeed = 30; // km/h in urban areas
        $pickupTime = 5; // minutes per pickup
        
        $travelTime = ($totalDistance / $avgSpeed) * 60; // minutes
        $pickupTimeTotal = count($route) * $pickupTime;
        
        return round($travelTime + $pickupTimeTotal);
    }
    
    /**
     * Calculate estimated fuel savings compared to individual pickups
     */
    private function calculateFuelSavings($individualRequests, $optimizedRoutes) {
        $individualDistance = 0;
        $optimizedDistance = 0;
        $validRequests = 0;
        
        // Calculate total distance if each request was picked up individually
        foreach ($individualRequests as $request) {
            // Skip requests without valid coordinates
            if (!$this->isValidCoordinate($request['latitude'], $request['longitude'])) {
                continue;
            }
            
            // Assume depot is at city center (you can adjust coordinates)
            $depotLat = -1.2921; // Nairobi coordinates
            $depotLon = 36.8219;
            
            $distance = $this->calculateDistance(
                $depotLat, $depotLon,
                $request['latitude'], $request['longitude']
            );
            
            // Only add if distance is valid (not PHP_FLOAT_MAX)
            if ($distance < PHP_FLOAT_MAX) {
                $individualDistance += $distance * 2; // Round trip
                $validRequests++;
            }
        }
        
        // Calculate optimized route distance
        foreach ($optimizedRoutes as $route) {
            if (isset($route['total_distance'])) {
                $optimizedDistance += $route['total_distance'];
            }
        }
        
        $savings = $individualDistance - $optimizedDistance;
        
        // Prevent division by zero
        if ($individualDistance > 0) {
            $savingsPercentage = ($savings / $individualDistance) * 100;
        } else {
            $savingsPercentage = 0;
        }
        
        return [
            'individual_distance' => round($individualDistance, 2),
            'optimized_distance' => round($optimizedDistance, 2),
            'distance_saved' => round($savings, 2),
            'savings_percentage' => round($savingsPercentage, 1),
            'valid_requests_count' => $validRequests
        ];
    }
    
    /**
     * Get scheduling recommendations for admin
     */
    public function getSchedulingRecommendations($date = null) {
        if (!$date) {
            $date = date('Y-m-d');
        }
        
        $schedule = $this->scheduleCollectionRequests($date);
        
        // Ensure all required keys exist with default values
        $totalRequests = isset($schedule['total_requests']) ? $schedule['total_requests'] : 0;
        $clusters = isset($schedule['clusters']) ? $schedule['clusters'] : 0;
        $fuelSavings = isset($schedule['estimated_fuel_savings']) ? $schedule['estimated_fuel_savings'] : [
            'individual_distance' => 0,
            'optimized_distance' => 0,
            'distance_saved' => 0,
            'savings_percentage' => 0
        ];
        $assignments = isset($schedule['assignments']) ? $schedule['assignments'] : [];
        
        $recommendations = [
            'date' => $date,
            'summary' => [
                'total_requests' => $totalRequests,
                'clusters_formed' => $clusters,
                'fuel_savings' => $fuelSavings
            ],
            'recommendations' => []
        ];
        
        // Generate recommendations based on schedule
        if ($totalRequests > 20) {
            $recommendations['recommendations'][] = "High volume day - Consider adding temporary drivers";
        }
        
        if (isset($fuelSavings['savings_percentage']) && $fuelSavings['savings_percentage'] < 30) {
            $recommendations['recommendations'][] = "Low clustering efficiency - Consider adjusting time windows";
        }
        
        if (is_array($assignments) && count($assignments) > 5) {
            $recommendations['recommendations'][] = "Multiple routes - Consider route consolidation";
        }
        
        // Add error message if there was an error
        if (isset($schedule['error'])) {
            $recommendations['error'] = $schedule['error'];
        }
        
        return $recommendations;
    }
}
?> 