<?php
// Include asset helper for environment-aware paths
require_once __DIR__ . '/../../backend/config/asset_helper.php';
session_start();

// Check authentication - temporarily bypassed for development
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'resident') {
    // Temporarily bypass authentication for development
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = 'Test User';
    $_SESSION['role'] = 'resident';
    // Uncomment for production:
    // header('Location: login.php');
    // exit();
}

$username = $_SESSION['username'] ?? 'User';
$role = $_SESSION['role'] ?? 'resident';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Waste Request - DRMS</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    
    <style>
        /* Basic body and layout styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: 
                linear-gradient(135deg, rgba(46, 125, 50, 0.85), rgba(76, 175, 80, 0.85)),
                url('../images/background.png') center/cover no-repeat fixed,
                linear-gradient(135deg, #2e7d32, #4caf50);
            min-height: 100vh;
            color: white;
            font-family: 'Roboto', sans-serif;
            position: relative;
        }
        
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                url('../images/background.png') center/cover no-repeat,
                linear-gradient(135deg, rgba(46, 125, 50, 0.7), rgba(76, 175, 80, 0.7));
            z-index: -2;
        }
        
        body::after {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(46, 125, 50, 0.3), rgba(76, 175, 80, 0.3));
            z-index: -1;
        }
        
        /* Navbar styles */
        .navbar {
            position: fixed;
            top: 0;
            width: 100%;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(10px);
            z-index: 1000;
            padding: 1rem 0;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.3);
        }
        
        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.5rem;
            font-weight: bold;
            color: white;
        }
        
        .logo-img {
            height: 40px;
        }
        
        .nav-links {
            display: flex;
            gap: 2rem;
        }
        
        .nav-link {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover,
        .nav-link.active {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }
        
        .request-container {
            max-width: 1200px;
            margin: 140px auto 40px;
            padding: 0 2rem;
            position: relative;
            z-index: 10;
        }
        
        .request-header {
            text-align: center;
            margin-bottom: 2.5rem;
            background: rgba(255, 255, 255, 0.1);
            padding: 2rem;
            border-radius: 20px;
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .request-header h1 {
            font-size: 2.8rem;
            margin-bottom: 1rem;
            color: #fff;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
            font-weight: 700;
        }
        
        .request-header p {
            font-size: 1.2rem;
            color: rgba(255, 255, 255, 0.95);
            text-shadow: 0 1px 5px rgba(0, 0, 0, 0.2);
            font-weight: 400;
        }
        
        .request-form-container {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 24px;
            padding: 2.5rem;
            box-shadow: 
                0 20px 60px rgba(0, 0, 0, 0.15),
                0 8px 32px rgba(46, 125, 50, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .request-form-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #2e7d32, #4caf50, #2e7d32);
            background-size: 200% 100%;
            animation: shimmer 3s ease-in-out infinite;
        }
        
        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
        
        .form-grid {
            display: grid;
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .form-section {
            background: rgba(255, 255, 255, 0.98);
            padding: 2rem;
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .form-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #2e7d32, #4caf50);
            border-radius: 2px;
        }
        
        .form-section:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        }
        
        .form-section h3 {
            margin-bottom: 1.5rem;
            color: #374151;
            font-size: 1.4rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .form-section h3::before {
            content: '';
            width: 4px;
            height: 20px;
            background: linear-gradient(135deg, #2e7d32, #4caf50);
            border-radius: 2px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
            position: relative;
        }
        
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        
        .form-group label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #374151;
        }
        
        .form-group label i {
            color: #2e7d32;
            font-size: 14px;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
            background: #f9fafb;
            color: #374151;
            font-family: inherit;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #2e7d32;
            outline: none;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(46, 125, 50, 0.1);
            transform: translateY(-1px);
        }
        
        .form-group input::placeholder,
        .form-group textarea::placeholder {
            color: #9ca3af;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .form-group input:valid,
        .form-group select:valid,
        .form-group textarea:valid {
            border-color: #10b981;
            background: #f0fdf4;
        }
        
        /* MAP STYLES - ENHANCED */
        #map {
            height: 450px !important;
            width: 100% !important;
            border: 2px solid #007bff !important;
            border-radius: 15px !important;
            margin: 20px 0 !important;
            box-shadow: 0 4px 20px rgba(0, 123, 255, 0.2) !important;
            display: block !important;
            position: relative !important;
            z-index: 1 !important;
            background: #f8f9fa !important;
        }
        
        .map-search-container {
            position: relative;
            margin-bottom: 0;
        }
        
        .map-search {
            width: 100% !important;
            padding: 1rem 3rem 1rem 1rem !important;
            border: 2px solid #e5e7eb !important;
            border-radius: 12px !important;
            font-size: 1rem !important;
            font-weight: 500 !important;
            transition: all 0.3s ease !important;
            background: #f9fafb !important;
            color: #374151 !important;
        }
        
        .map-search:focus {
            border-color: #2e7d32 !important;
            outline: none !important;
            background: #ffffff !important;
            box-shadow: 0 0 0 4px rgba(46, 125, 50, 0.1) !important;
        }
        
        .search-btn {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            padding: 8px 12px;
            background: #2e7d32;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .search-btn:hover {
            background: #1b5e20;
            transform: translateY(-50%) scale(1.05);
        }
        
        .map-controls {
            display: flex;
            gap: 10px;
            margin: 15px 0;
            flex-wrap: wrap;
        }
        
        .map-btn {
            padding: 10px 15px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
            background: #6c757d;
            color: white;
        }
        
        .map-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }
        
        .map-btn.primary { background: #007bff; }
        .map-btn.success { background: #28a745; }
        .map-btn.danger { background: #dc3545; }
        
        .map-status {
            padding: 12px 15px;
            margin: 10px 0;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            background: #e3f2fd;
            color: #1565c0;
            border: 1px solid #bbdefb;
        }
        
        .map-status.loading { background: #fff3cd; color: #856404; }
        .map-status.success { background: #d4edda; color: #155724; }
        .map-status.error { background: #f8d7da; color: #721c24; }
        
        .location-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-top: 15px;
            border: 1px solid #e9ecef;
            display: none;
        }
        
        .location-info h4 {
            color: #28a745;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .coordinates-display {
            font-family: monospace;
            background: #e9ecef;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
        }
        
        /* Layer Control Styling */
        .leaflet-control-layers {
            background: rgba(255, 255, 255, 0.95) !important;
            border-radius: 12px !important;
            border: 2px solid #4caf50 !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2) !important;
            padding: 8px !important;
        }
        
        .leaflet-control-layers-expanded {
            min-width: 180px !important;
        }
        
        .leaflet-control-layers label {
            font-weight: 500 !important;
            color: #2e7d32 !important;
            font-size: 14px !important;
            margin: 6px 0 !important;
            display: flex !important;
            align-items: center !important;
            gap: 8px !important;
            cursor: pointer !important;
            padding: 4px 6px !important;
            border-radius: 6px !important;
            transition: all 0.3s ease !important;
        }
        
        .leaflet-control-layers label:hover {
            background: rgba(76, 175, 80, 0.1) !important;
            transform: translateX(2px) !important;
        }
        
        .leaflet-control-layers-base {
            margin-bottom: 8px !important;
        }
        
        .leaflet-control-layers input[type="radio"] {
            margin-right: 8px !important;
            accent-color: #4caf50 !important;
        }
        
        /* Debug styles */
        .debug-section {
            background: #f8f9fa;
            border: 2px solid #dc3545;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
        }
        
        .debug-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            margin: 5px;
        }
        
        .submit-section {
            text-align: center;
            margin-top: 2rem;
        }
        
        .btn-submit {
            width: 100%;
            max-width: 400px;
            padding: 1rem 2rem;
            background: linear-gradient(135deg, #2e7d32, #4caf50);
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            margin: 0 auto;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(46, 125, 50, 0.3);
        }
        
        .btn-submit:active {
            transform: translateY(0);
        }
        
        .btn-submit:disabled {
            background: #d1d5db;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .result-message {
            margin-top: 1.5rem;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            font-weight: 500;
            font-size: 1rem;
            text-align: center;
            display: none;
            position: relative;
            overflow: hidden;
        }
        
        .result-message::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
        }
        
        .result-message.success {
            background: #f0fdf4;
            color: #059669;
            border: 1px solid #bbf7d0;
        }
        
        .result-message.success::before {
            background: linear-gradient(90deg, #10b981, #059669);
        }
        
        .result-message.error {
            background: #fef2f2;
            color: #d32f2f;
            border: 1px solid #fecaca;
        }
        
        .result-message.error::before {
            background: linear-gradient(90deg, #ef4444, #dc2626);
        }
        
        .result-message.loading {
            background: #fffbeb;
            color: #d97706;
            border: 1px solid #fed7aa;
        }
        
        .result-message.loading::before {
            background: linear-gradient(90deg, #f59e0b, #d97706);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="navbar-brand">
                <img src="<?php echo logoPath(); ?>" alt="DRMS Logo" class="logo-img">
                <span class="brand-text">DRMS</span>
            </div>
            <div class="nav-links">
                <a href="home.php" class="nav-link">🏠 Home</a>
                <a href="request.php" class="nav-link active">📝 Request</a>
                <a href="track.php" class="nav-link">📍 Track</a>
                <a href="history.php" class="nav-link">📋 History</a>
                <a href="#" class="nav-link" onclick="confirmLogout()">🚪 Logout</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="request-container">
        <div class="request-header">
            <h1>🗑️ Submit Waste Collection Request</h1>
            <p>Fill out the form below to schedule your waste collection</p>
        </div>

        <div class="request-form-container">
            <form id="request-form" method="POST" action="../../backend/api/place_request.php">
                <div class="form-grid">
                    <!-- Waste Details -->
                    <div class="form-section">
                        <h3><i class="fas fa-trash-alt"></i> Waste Details</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="waste_type">
                                    <i class="fas fa-recycle"></i>
                                    Waste Type *
                                </label>
                                <select id="waste_type" name="waste_type" required>
                                    <option value="">Select waste type</option>
                                    <option value="organic">🥬 Organic Waste</option>
                                    <option value="recyclable">♻️ Recyclable</option>
                                    <option value="hazardous">⚠️ Hazardous</option>
                                    <option value="electronic">🔌 Electronic Waste</option>
                                    <option value="general">🗑️ General Waste</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="quantity">
                                    <i class="fas fa-weight"></i>
                                    Estimated Quantity *
                                </label>
                                <select id="quantity" name="quantity" required>
                                    <option value="">Select quantity</option>
                                    <option value="small">📦 Small (1-2 bags)</option>
                                    <option value="medium">📦📦 Medium (3-5 bags)</option>
                                    <option value="large">📦📦📦 Large (6-10 bags)</option>
                                    <option value="bulk">🚛 Bulk (10+ bags)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Scheduling -->
                    <div class="form-section">
                        <h3><i class="fas fa-calendar-alt"></i> Preferred Schedule</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="preferred_date">
                                    <i class="fas fa-calendar-day"></i>
                                    Preferred Date *
                                </label>
                                <input type="date" id="preferred_date" name="preferred_date" required>
                            </div>
                            <div class="form-group">
                                <label for="preferred_time">
                                    <i class="fas fa-clock"></i>
                                    Preferred Time *
                                </label>
                                <select id="preferred_time" name="preferred_time" required>
                                    <option value="">Select time</option>
                                    <option value="morning">🌅 Morning (6:00 AM - 12:00 PM)</option>
                                    <option value="afternoon">🌞 Afternoon (12:00 PM - 6:00 PM)</option>
                                    <option value="evening">🌇 Evening (6:00 PM - 9:00 PM)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="form-section">
                        <h3><i class="fas fa-address-book"></i> Contact Information</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="phone">
                                    <i class="fas fa-phone"></i>
                                    Phone Number *
                                </label>
                                <input type="tel" id="phone" name="phone" 
                                       placeholder="e.g., +254712345678" required>
                            </div>
                            <div class="form-group">
                                <label for="email">
                                    <i class="fas fa-envelope"></i>
                                    Email Address
                                </label>
                                <input type="email" id="email" name="email" 
                                       placeholder="your.email@example.com">
                            </div>
                        </div>
                    </div>

                    <!-- Additional Notes -->
                    <div class="form-section">
                        <h3><i class="fas fa-sticky-note"></i> Additional Information</h3>
                        
                        <div class="form-group full-width">
                            <label for="notes">
                                <i class="fas fa-comment"></i>
                                Special Instructions
                            </label>
                            <textarea id="notes" name="notes" rows="4" 
                                      placeholder="Any special instructions or details about your waste collection..."></textarea>
                        </div>
                    </div>

                    <!-- Location Selection -->
                    <div class="form-section map-section">
                        <h3><i class="fas fa-map-marker-alt"></i> Location Selection</h3>
                        <p style="color: #6b7280; font-size: 0.95rem; margin-bottom: 1.5rem;">Search for an address or click on the map to select your collection location.</p>
                        
                        <!-- Map Search -->
                        <div class="form-group">
                            <label for="address-search">
                                <i class="fas fa-search"></i>
                                Search Address
                            </label>
                            <div class="map-search-container">
                                <input type="text" id="address-search" class="map-search" 
                                       placeholder="Search for an address (e.g., Westlands, Nairobi)" />
                                <button type="button" class="search-btn" onclick="searchAddress()">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Map Status -->
                        <div id="map-status" class="map-status">
                            <i class="fas fa-map-marker-alt"></i> Ready to select location
                            <button onclick="initMap()" style="
                                margin-left: 15px;
                                padding: 5px 12px;
                                background: #28a745;
                                color: white;
                                border: none;
                                border-radius: 15px;
                                cursor: pointer;
                                font-size: 12px;
                            ">🔄 Initialize Map</button>
                        </div>
                        
                        <div id="map"></div>
                        
                        <div class="map-controls">
                            <button type="button" class="map-btn success" onclick="getCurrentLocation()">
                                <i class="fas fa-crosshairs"></i> Use My Location
                            </button>
                            <button type="button" class="map-btn" onclick="centerToNairobi()">
                                <i class="fas fa-city"></i> Center to Nairobi
                            </button>
                            <button type="button" class="map-btn" onclick="switchToStreetView()" title="Switch to Street View">
                                <i class="fas fa-road"></i> Street
                            </button>
                            <button type="button" class="map-btn" onclick="switchToSatelliteView()" title="Switch to Satellite View">
                                <i class="fas fa-satellite"></i> Satellite
                            </button>
                            <button type="button" class="map-btn" onclick="switchToHybridView()" title="Switch to Hybrid View">
                                <i class="fas fa-layer-group"></i> Hybrid
                            </button>
                            <button type="button" class="map-btn danger" onclick="clearLocation()">
                                <i class="fas fa-trash"></i> Clear Location
                            </button>
                        </div>
                        
                        <div id="location-info" class="location-info">
                            <h4><i class="fas fa-check-circle"></i> Selected Location:</h4>
                            <p><strong>Address:</strong> <span id="selected-address">Not selected</span></p>
                            <p><strong>Coordinates:</strong> 
                                <span id="selected-coordinates" class="coordinates-display">Not selected</span>
                            </p>
                        </div>
                        
                        <!-- Hidden form fields for coordinates -->
                        <input type="hidden" id="latitude" name="latitude">
                        <input type="hidden" id="longitude" name="longitude">
                        <input type="hidden" id="resolved_address" name="resolved_address">
                    </div>
                    
                    <!-- Debug Section -->
                    <div class="form-section debug-section" style="display: none;" id="debug-section">
                        <h3>🔧 Debug Tools</h3>
                        <button type="button" class="debug-btn" onclick="debugFormData()">Debug Form Data</button>
                        <button type="button" class="debug-btn" onclick="debugMapState()">Debug Map State</button>
                        <button type="button" class="debug-btn" onclick="testApiConnection()">Test API</button>
                        <div id="debug-output" style="background: #000; color: #0f0; padding: 10px; margin-top: 10px; border-radius: 4px; font-family: monospace; max-height: 200px; overflow-y: auto; display: none;"></div>
                    </div>
                </div>

                <div class="submit-section">
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-paper-plane"></i>
                        <span>Submit Request</span>
                    </button>
                </div>
            </form>
            
            <div id="result-message" class="result-message" style="display: none;"></div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Enhanced map implementation
        let map = null;
        let marker = null;
        
        function updateMapStatus(message, type = 'info') {
            const statusEl = document.getElementById('map-status');
            statusEl.className = `map-status ${type}`;
            statusEl.innerHTML = `<i class="fas fa-${getStatusIcon(type)}"></i> ${message}`;
        }
        
        function getStatusIcon(type) {
            switch(type) {
                case 'success': return 'check-circle';
                case 'error': return 'exclamation-triangle';
                case 'loading': return 'spinner fa-spin';
                default: return 'map-marker-alt';
            }
        }
        
        function initMap() {
            try {
                console.log('=== STARTING MAP INITIALIZATION ===');
                
                // Check if map element exists
                const mapElement = document.getElementById('map');
                if (!mapElement) {
                    console.error('Map element not found!');
                    updateMapStatus('❌ Map container not found', 'error');
                    return;
                }
                console.log('Map element found:', mapElement);
                
                // Check if Leaflet is loaded
                if (typeof L === 'undefined') {
                    console.error('Leaflet library not loaded!');
                    updateMapStatus('❌ Map library not loaded', 'error');
                    return;
                }
                console.log('Leaflet library loaded, version:', L.version);
                
                updateMapStatus('🔄 Initializing map...', 'loading');
                
                // Clear any existing map
                if (map) {
                    console.log('Removing existing map');
                    map.remove();
                    map = null;
                    marker = null;
                }
                
                // Clear map container
                mapElement.innerHTML = '';
                console.log('Map container cleared');
                
                // Initialize map with better options
                console.log('Creating map object...');
                map = L.map('map', {
                    zoomControl: true,
                    attributionControl: true,
                    fadeAnimation: true,
                    zoomAnimation: true,
                    preferCanvas: false
                }).setView([-1.2921, 36.8219], 13);
                
                console.log('Map object created successfully');
                
                // Define different map layers
                const streetMap = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                    id: 'streetmap'
                });
                
                const satelliteMap = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                    maxZoom: 19,
                    attribution: '&copy; <a href="https://www.esri.com/">Esri</a> &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community',
                    id: 'satellite'
                });
                
                const hybridMap = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                    maxZoom: 19,
                    attribution: '&copy; <a href="https://www.esri.com/">Esri</a> &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community',
                    id: 'hybrid'
                });
                
                const hybridLabels = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '',
                    opacity: 0.5,
                    id: 'hybrid-labels'
                });
                
                const terrainMap = L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
                    maxZoom: 17,
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, <a href="http://viewfinderpanoramas.org">SRTM</a> | Map style: &copy; <a href="https://opentopomap.org">OpenTopoMap</a> (<a href="https://creativecommons.org/licenses/by-sa/3.0/">CC-BY-SA</a>)',
                    id: 'terrain'
                });
                
                // Add default street map
                streetMap.addTo(map);
                
                // Create base maps object for layer control
                const baseMaps = {
                    "<i class='fas fa-road'></i> Street View": streetMap,
                    "<i class='fas fa-satellite'></i> Satellite": satelliteMap,
                    "<i class='fas fa-layer-group'></i> Hybrid": L.layerGroup([satelliteMap, hybridLabels]),
                    "<i class='fas fa-mountain'></i> Terrain": terrainMap
                };
                
                // Add layer control to switch between map types
                const layerControl = L.control.layers(baseMaps, null, {
                    position: 'topright',
                    collapsed: false
                }).addTo(map);
                
                // Store layer references globally for button controls
                window.mapLayers = {
                    streetMap: streetMap,
                    satelliteMap: satelliteMap,
                    hybridLabels: hybridLabels,
                    terrainMap: terrainMap
                };
                
                console.log('Tile layers added');
                
                // Add click event
                map.on('click', function(e) {
                    const lat = e.latlng.lat;
                    const lng = e.latlng.lng;
                    console.log('Map clicked at:', lat, lng);
                    setMapLocation(lat, lng, 'Map click');
                });
                
                // Force map to invalidate size after a delay
                setTimeout(() => {
                    if (map) {
                        console.log('Invalidating map size...');
                        map.invalidateSize();
                        updateMapStatus('✅ Map ready! Click anywhere to select your location.', 'success');
                        console.log('=== MAP INITIALIZATION COMPLETE ===');
                    }
                }, 500);
                
            } catch (error) {
                console.error('=== MAP INITIALIZATION ERROR ===', error);
                updateMapStatus('❌ Error loading map: ' + error.message, 'error');
            }
        }
        
        function setMapLocation(lat, lng, source = 'Location set') {
            try {
                updateMapStatus('Setting location...', 'loading');
                
                // Remove existing marker
                if (marker) {
                    map.removeLayer(marker);
                }
                
                // Add new marker with enhanced popup
                marker = L.marker([lat, lng], {
                    draggable: true,
                    title: 'Drag to adjust location'
                }).addTo(map);
                
                // Create detailed popup content
                const popupContent = `
                    <div style="text-align: center;">
                        <h4 style="margin: 0 0 8px 0; color: #2e7d32;">📍 Selected Location</h4>
                        <p style="margin: 4px 0; font-size: 13px;"><strong>Lat:</strong> ${lat.toFixed(6)}</p>
                        <p style="margin: 4px 0; font-size: 13px;"><strong>Lng:</strong> ${lng.toFixed(6)}</p>
                        <p style="margin: 8px 0 0 0; font-size: 12px; color: #666;">
                            <i class="fas fa-info-circle"></i> Drag marker to adjust
                        </p>
                    </div>
                `;
                
                marker.bindPopup(popupContent, {
                    closeOnClick: false,
                    autoClose: false
                }).openPopup();
                
                // Handle marker drag
                marker.on('dragend', function(e) {
                    const pos = marker.getLatLng();
                    setMapLocation(pos.lat, pos.lng, 'Marker dragged');
                });
                
                // Update form fields
                document.getElementById('latitude').value = lat.toFixed(6);
                document.getElementById('longitude').value = lng.toFixed(6);
                
                // Try to get address via reverse geocoding
                reverseGeocode(lat, lng);
                
                // Update display immediately with coordinates
                document.getElementById('selected-coordinates').textContent = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                document.getElementById('location-info').style.display = 'block';
                
                updateMapStatus(`✅ Location selected! (${source})`, 'success');
                
            } catch (error) {
                console.error('Error setting location:', error);
                updateMapStatus('❌ Error setting location: ' + error.message, 'error');
            }
        }
        
        async function reverseGeocode(lat, lng) {
            try {
                const address = await getAddressFromCoordinates(lat, lng);
                if (address) {
                    document.getElementById('selected-address').textContent = address;
                    document.getElementById('resolved_address').value = address;
                }
            } catch (error) {
                console.warn('Reverse geocoding failed:', error);
                document.getElementById('selected-address').textContent = `Coordinates: ${lat.toFixed(4)}, ${lng.toFixed(4)}`;
            }
        }
        
        async function getAddressFromCoordinates(lat, lng) {
            try {
                const response = await fetch(
                    `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&addressdetails=1`,
                    {
                        headers: {
                            'User-Agent': 'DRMS-WasteCollection/1.0'
                        }
                    }
                );
                
                if (!response.ok) throw new Error('Reverse geocoding failed');
                
                const data = await response.json();
                
                if (data && data.address) {
                    const addr = data.address;
                    let formattedAddress = '';
                    
                    if (addr.road) formattedAddress += addr.road;
                    if (addr.suburb) formattedAddress += (formattedAddress ? ', ' : '') + addr.suburb;
                    if (addr.city) formattedAddress += (formattedAddress ? ', ' : '') + addr.city;
                    if (addr.county) formattedAddress += (formattedAddress ? ', ' : '') + addr.county;
                    if (addr.country) formattedAddress += (formattedAddress ? ', ' : '') + addr.country;
                    
                    return formattedAddress || data.display_name;
                }
                
                throw new Error('No address found');
                
            } catch (error) {
                console.warn('Reverse geocoding failed:', error);
                return null;
            }
        }
        
        // Enhanced address search function
        async function searchAddress() {
            console.log('searchAddress() called');
            
            const searchInput = document.getElementById('address-search');
            if (!searchInput) {
                console.error('Search input not found');
                return;
            }
            
            const query = searchInput.value.trim();
            if (!query) {
                updateMapStatus('❌ Please enter an address to search', 'error');
                return;
            }
            
            try {
                console.log('Searching for:', query);
                updateMapStatus('🔍 Searching for address...', 'loading');
                
                const response = await fetch(
                    `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=5&countrycodes=ke&addressdetails=1`,
                    {
                        headers: {
                            'User-Agent': 'DRMS-WasteCollection/1.0'
                        }
                    }
                );
                
                if (!response.ok) {
                    throw new Error(`Search request failed: ${response.status}`);
                }
                
                const results = await response.json();
                console.log('Search results:', results);
                
                if (results && results.length > 0) {
                    const result = results[0];
                    const lat = parseFloat(result.lat);
                    const lng = parseFloat(result.lon);
                    
                    console.log('Using first result:', { lat, lng, name: result.display_name });
                    
                    // Zoom to location
                    map.setView([lat, lng], 16);
                    setMapLocation(lat, lng, 'Address search');
                    
                    updateMapStatus(`✅ Found: ${result.display_name}`, 'success');
                } else {
                    updateMapStatus('❌ Address not found. Try a different search term.', 'error');
                    console.warn('No results found for query:', query);
                }
            } catch (error) {
                console.error('Search error:', error);
                updateMapStatus('❌ Search failed. Please try again.', 'error');
            }
        }
        
        function getCurrentLocation() {
            console.log('getCurrentLocation() called');
            
            if (!navigator.geolocation) {
                console.error('Geolocation not supported');
                updateMapStatus('❌ Geolocation is not supported by this browser', 'error');
                return;
            }
            
            console.log('Starting geolocation request...');
            updateMapStatus('📍 Getting your location...', 'loading');
            
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    
                    console.log('Geolocation success:', { lat, lng });
                    
                    // Zoom to user location
                    map.setView([lat, lng], 16);
                    setMapLocation(lat, lng, 'Current location');
                },
                function(error) {
                    console.error('Geolocation error:', error);
                    let errorMessage = 'Could not get your location. ';
                    
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            errorMessage += 'Permission denied.';
                            break;
                        case error.POSITION_UNAVAILABLE:
                            errorMessage += 'Position unavailable.';
                            break;
                        case error.TIMEOUT:
                            errorMessage += 'Request timeout.';
                            break;
                    }
                    
                    updateMapStatus('❌ ' + errorMessage + ' Please click on the map to set location manually.', 'error');
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 300000
                }
            );
        }
        
        function centerToNairobi() {
            try {
                console.log('Centering to Nairobi...');
                
                // Remove existing marker
                if (marker) {
                    map.removeLayer(marker);
                    marker = null;
                }
                
                // Add default marker at Nairobi center
                marker = L.marker([-1.2921, 36.8219], {
                    draggable: true,
                    title: 'Drag to set your location'
                }).addTo(map)
                .bindPopup('<strong>📍 Nairobi Center</strong><br>Click anywhere on the map or drag this marker to set your location!', {
                    closeOnClick: false,
                    autoClose: false
                })
                .openPopup();
                
                // Handle marker drag for default marker
                marker.on('dragend', function(e) {
                    const pos = marker.getLatLng();
                    console.log('Default marker dragged to:', pos);
                    setMapLocation(pos.lat, pos.lng, 'Marker dragged');
                });
                
                // Center map back to Nairobi
                map.setView([-1.2921, 36.8219], 13);
                
                updateMapStatus('✅ Centered to Nairobi. Click anywhere to select location.', 'info');
                console.log('Centered to Nairobi successfully');
                
            } catch (error) {
                console.error('Error in centerToNairobi:', error);
                updateMapStatus('❌ Error centering to Nairobi: ' + error.message, 'error');
            }
        }
        
        function clearLocation() {
            try {
                console.log('Clearing location...');
                
                // Remove marker
                if (marker) {
                    map.removeLayer(marker);
                    marker = null;
                }
                
                // Clear form fields
                document.getElementById('latitude').value = '';
                document.getElementById('longitude').value = '';
                document.getElementById('resolved_address').value = '';
                document.getElementById('selected-address').textContent = 'Not selected';
                document.getElementById('selected-coordinates').textContent = 'Not selected';
                document.getElementById('location-info').style.display = 'none';
                
                // Add default marker at Nairobi center
                marker = L.marker([-1.2921, 36.8219], {
                    draggable: true,
                    title: 'Drag to set your location'
                }).addTo(map)
                .bindPopup('<strong>📍 Nairobi Center</strong><br>Click anywhere on the map or drag this marker to set your location!', {
                    closeOnClick: false,
                    autoClose: false
                })
                .openPopup();
                
                // Handle marker drag for default marker
                marker.on('dragend', function(e) {
                    const pos = marker.getLatLng();
                    console.log('Default marker dragged to:', pos);
                    setMapLocation(pos.lat, pos.lng, 'Marker dragged');
                });
                
                // Center map back to Nairobi
                map.setView([-1.2921, 36.8219], 13);
                
                updateMapStatus('✅ Location cleared. Click anywhere to select new location.', 'info');
                console.log('Location cleared successfully');
                
            } catch (error) {
                console.error('Error in clearLocation:', error);
                updateMapStatus('❌ Error clearing location: ' + error.message, 'error');
            }
        }
        
        // Map layer switching functions
        function switchToStreetView() {
            if (map && window.mapLayers) {
                map.eachLayer(function(layer) {
                    if (layer.options && layer.options.id) {
                        map.removeLayer(layer);
                    }
                });
                window.mapLayers.streetMap.addTo(map);
                updateMapStatus('✅ Switched to Street View', 'success');
            }
        }
        
        function switchToSatelliteView() {
            if (map && window.mapLayers) {
                map.eachLayer(function(layer) {
                    if (layer.options && layer.options.id) {
                        map.removeLayer(layer);
                    }
                });
                window.mapLayers.satelliteMap.addTo(map);
                updateMapStatus('✅ Switched to Satellite View', 'success');
            }
        }
        
        function switchToHybridView() {
            if (map && window.mapLayers) {
                map.eachLayer(function(layer) {
                    if (layer.options && layer.options.id) {
                        map.removeLayer(layer);
                    }
                });
                window.mapLayers.satelliteMap.addTo(map);
                window.mapLayers.hybridLabels.addTo(map);
                updateMapStatus('✅ Switched to Hybrid View', 'success');
            }
        }
        
        // Initialize map when page loads
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🚀 Page initialization started');
            
            // Set minimum date to today
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('preferred_date').setAttribute('min', today);
            
            // Add keyboard support for address search
            const searchInput = document.getElementById('address-search');
            if (searchInput) {
                searchInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        searchAddress();
                    }
                });
            }
            
            // Add debug key combination (Ctrl+Shift+D)
            document.addEventListener('keydown', function(e) {
                if (e.ctrlKey && e.shiftKey && e.key === 'D') {
                    const debugSection = document.getElementById('debug-section');
                    debugSection.style.display = debugSection.style.display === 'none' ? 'block' : 'none';
                }
            });
            
            // Initialize map with longer delay to ensure everything is loaded
            console.log('Scheduling map initialization...');
            setTimeout(() => {
                console.log('Starting map initialization...');
                initMap();
            }, 2000);
            
            console.log('✅ Page initialization complete');
        });
        
        // Form submission
        document.getElementById('request-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            console.log('🚀 Form submission started');
            
            // Validate location is selected
            const lat = document.getElementById('latitude').value;
            const lng = document.getElementById('longitude').value;
            
            if (!lat || !lng) {
                alert('Please select a location on the map before submitting.');
                return;
            }
            
            // Helper function to get time from selection
            function getTimeFromSelection() {
                const timeSelect = document.getElementById('preferred_time');
                const timeValue = timeSelect ? timeSelect.value : '';
                
                switch(timeValue) {
                    case 'morning': return '09:00:00';
                    case 'afternoon': return '14:00:00';
                    case 'evening': return '18:00:00';
                    default: return '10:00:00';
                }
            }
            
            // Prepare JSON data that matches API expectations
            const requestData = {
                waste_type: document.getElementById('waste_type').value,
                preferred_date: document.getElementById('preferred_date').value + 'T' + getTimeFromSelection(),
                phone: document.getElementById('phone').value,
                email: document.getElementById('email').value || '',
                location: `${lat}, ${lng}`,
                notes: document.getElementById('notes').value || '',
                urgency: 'Normal', // Default urgency
                latitude: lat,
                longitude: lng,
                quantity: document.getElementById('quantity').value
            };
            
            console.log('📦 Prepared request data:', requestData);
            
            // Show loading
            const submitBtn = document.querySelector('.btn-submit');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Submitting...';
            submitBtn.disabled = true;
            
            // Submit form with JSON data and timeout handling
            console.log('📡 Sending request to API...');
            
            // Create a timeout promise
            const timeoutPromise = new Promise((_, reject) => {
                setTimeout(() => reject(new Error('Request timeout')), 10000); // 10 second timeout
            });
            
            // Create the fetch promise
            const fetchPromise = fetch('../../backend/api/place_request.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(requestData)
            });
            
            // Race between fetch and timeout
            Promise.race([fetchPromise, timeoutPromise])
            .then(response => {
                console.log('📨 API Response received:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('📋 API Response data:', data);
                if (data.success) {
                    showMessage('✅ Request submitted successfully!', 'success');
                    this.reset();
                    clearLocation();
                } else {
                    showMessage('❌ Error: ' + (data.error || data.message || 'Failed to submit request'), 'error');
                }
            })
            .catch(error => {
                console.error('💥 Request error:', error);
                if (error.message === 'Request timeout') {
                    showMessage('❌ Request timed out. Please check your connection and try again.', 'error');
                } else {
                    showMessage('❌ Network error. Please try again.', 'error');
                }
            })
            .finally(() => {
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
                console.log('🏁 Form submission completed');
            });
        });
        
        function showMessage(message, type) {
            const messageEl = document.getElementById('result-message');
            messageEl.textContent = message;
            messageEl.className = `result-message ${type}`;
            messageEl.style.display = 'block';
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                messageEl.style.display = 'none';
            }, 5000);
        }
        
        function confirmLogout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = '../api/logout.php';
            }
        }
        
        // Debug functions
        function debugFormData() {
            const formData = {
                waste_type: document.getElementById('waste_type').value,
                quantity: document.getElementById('quantity').value,
                preferred_date: document.getElementById('preferred_date').value,
                preferred_time: document.getElementById('preferred_time').value,
                phone: document.getElementById('phone').value,
                email: document.getElementById('email').value,
                notes: document.getElementById('notes').value,
                latitude: document.getElementById('latitude').value,
                longitude: document.getElementById('longitude').value
            };
            
            showDebugOutput('Form Data:', formData);
        }
        
        function debugMapState() {
            const mapState = {
                mapInitialized: !!map,
                markerPresent: !!marker,
                mapCenter: map ? map.getCenter() : null,
                mapZoom: map ? map.getZoom() : null,
                leafletVersion: typeof L !== 'undefined' ? L.version : 'Not loaded'
            };
            
            showDebugOutput('Map State:', mapState);
        }
        
        function testApiConnection() {
            fetch('../../backend/api/place_request.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({test: true})
            })
            .then(response => response.text())
            .then(data => {
                showDebugOutput('API Test Response:', data);
            })
            .catch(error => {
                showDebugOutput('API Test Error:', error.toString());
            });
        }
        
        function showDebugOutput(title, data) {
            const debugOutput = document.getElementById('debug-output');
            const debugSection = document.getElementById('debug-section');
            
            debugSection.style.display = 'block';
            debugOutput.style.display = 'block';
            
            const timestamp = new Date().toLocaleTimeString();
            debugOutput.innerHTML += `<div style="color: #ff0;">[${timestamp}] ${title}</div>`;
            debugOutput.innerHTML += `<pre style="color: #0f0; margin: 5px 0 15px 0;">${JSON.stringify(data, null, 2)}</pre>`;
            debugOutput.scrollTop = debugOutput.scrollHeight;
        }
    </script>
</body>
</html>
