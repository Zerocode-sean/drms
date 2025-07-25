<?php
session_start();

// Check authentication
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'resident') {
    // Temporarily bypass authentication for development
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = 'Test User';
    $_SESSION['role'] = 'resident';
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
            background: linear-gradient(135deg, #2e7d32, #4caf50);
            min-height: 100vh;
            color: white;
            font-family: 'Roboto', sans-serif;
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
        }
        
        .request-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .request-header h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: #fff;
        }
        
        .request-header p {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.9);
        }
        
        .request-form-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
        }
        
        .form-grid {
            display: grid;
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .form-section {
            background: linear-gradient(135deg, #f8f9fa, #ffffff);
            padding: 1.8rem;
            border-radius: 18px;
            border-left: 5px solid #4caf50;
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.08);
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
            height: 3px;
            background: linear-gradient(90deg, #4caf50, #45a049, #4caf50);
            background-size: 200% 100%;
            animation: shimmer 3s ease-in-out infinite;
        }
        
        .form-section:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.12);
        }
        
        .form-section h3 {
            margin-bottom: 1.2rem;
            color: #2e7d32;
            font-size: 1.3rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
        
        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2e7d32;
            font-size: 14px;
            transition: color 0.3s ease;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.3s ease;
            background: #ffffff;
            font-family: inherit;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #4caf50;
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
            transform: translateY(-1px);
        }
        
        .form-group input:valid,
        .form-group select:valid,
        .form-group textarea:valid {
            border-color: #28a745;
        }
        
        .form-group input:invalid:not(:placeholder-shown),
        .form-group select:invalid:not(:placeholder-shown),
        .form-group textarea:invalid:not(:placeholder-shown) {
            border-color: #dc3545;
        }
        
        /* MAP STYLES - CRITICAL */
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
            margin-bottom: 15px;
        }
        
        .map-search {
            width: 100%;
            padding: 12px 50px 12px 20px;
            border: 2px solid #ddd;
            border-radius: 25px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .map-search:focus {
            outline: none;
            border-color: #4caf50;
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
        }
        
        .search-btn {
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            padding: 8px 12px;
            background: #4caf50;
            color: white;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .search-btn:hover {
            background: #45a049;
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
        
        .submit-section {
            text-align: center;
            margin-top: 2rem;
        }
        
        .btn-submit {
            background: linear-gradient(135deg, #4caf50, #45a049);
            color: white;
            padding: 15px 40px;
            border: none;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(76, 175, 80, 0.4);
        }
        
        .result-message {
            margin-top: 1rem;
            padding: 15px;
            border-radius: 10px;
            font-weight: 500;
        }
        
        .result-message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .result-message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .result-message.loading {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="navbar-brand">
                <img src="../images/logo.png" alt="DRMS Logo" class="logo-img">
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
                        <h3>🗑️ Waste Details</h3>
                        <div class="form-group">
                            <label for="waste_type">Waste Type *</label>
                            <select id="waste_type" name="waste_type" required>
                                <option value="">Select waste type</option>
                                <option value="organic">Organic Waste</option>
                                <option value="recyclable">Recyclable</option>
                                <option value="hazardous">Hazardous</option>
                                <option value="electronic">Electronic Waste</option>
                                <option value="general">General Waste</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="quantity">Estimated Quantity *</label>
                            <select id="quantity" name="quantity" required>
                                <option value="">Select quantity</option>
                                <option value="small">Small (1-2 bags)</option>
                                <option value="medium">Medium (3-5 bags)</option>
                                <option value="large">Large (6-10 bags)</option>
                                <option value="bulk">Bulk (10+ bags)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Scheduling -->
                    <div class="form-section">
                        <h3>📅 Preferred Schedule</h3>
                        <div class="form-group">
                            <label for="preferred_date">Preferred Date *</label>
                            <input type="date" id="preferred_date" name="preferred_date" required>
                        </div>
                        <div class="form-group">
                            <label for="preferred_time">Preferred Time *</label>
                            <select id="preferred_time" name="preferred_time" required>
                                <option value="">Select time</option>
                                <option value="morning">Morning (6:00 AM - 12:00 PM)</option>
                                <option value="afternoon">Afternoon (12:00 PM - 6:00 PM)</option>
                                <option value="evening">Evening (6:00 PM - 9:00 PM)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="form-section">
                        <h3>📞 Contact Information</h3>
                        <div class="form-group">
                            <label for="phone">Phone Number *</label>
                            <input type="tel" id="phone" name="phone" 
                                   placeholder="e.g., +254712345678" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" 
                                   placeholder="your.email@example.com">
                        </div>
                    </div>

                    <!-- Additional Notes -->
                    <div class="form-section">
                        <h3>📝 Additional Information</h3>
                        <div class="form-group">
                            <label for="notes">Special Instructions</label>
                            <textarea id="notes" name="notes" rows="4" 
                                      placeholder="Any special instructions or details about your waste..."></textarea>
                        </div>
                    </div>

                    <!-- Location Selection -->
                    <div class="form-section map-section">
                        <h3>📍 Location Selection</h3>
                        <p>Search for an address or click on the map to select your collection location.</p>
                        
                        <!-- Map Search -->
                        <div class="map-search-container">
                            <input type="text" id="address-search" class="map-search" 
                                   placeholder="Search for an address (e.g., Westlands, Nairobi)" />
                            <button type="button" class="search-btn" onclick="searchAddress()">
                                <i class="fas fa-search"></i>
                            </button>
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
                </div>

                <div class="submit-section">
                    <button type="submit" class="btn-submit">
                        📤 Submit Request
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
                
                // Add tile layer
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                }).addTo(map);
                
                console.log('Tile layer added');
                
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
        
        // Initialize when page loads
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM Content Loaded - Initializing page...');
            
            // Set minimum date to today
            const today = new Date().toISOString().split('T')[0];
            const dateInput = document.getElementById('preferred_date');
            if (dateInput) {
                dateInput.setAttribute('min', today);
            }
            
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
            
            // Initialize map with longer delay to ensure everything is loaded
            console.log('Scheduling map initialization...');
            setTimeout(() => {
                console.log('Starting map initialization...');
                initMap();
            }, 2000);
            
            console.log('Page initialization complete');
        });
        
        // Enhanced form submission
        document.getElementById('request-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate location is selected
            const lat = document.getElementById('latitude').value;
            const lng = document.getElementById('longitude').value;
            
            if (!lat || !lng) {
                showMessage('Please select a location on the map before submitting.', 'error');
                updateMapStatus('Location required! Please select a location.', 'error');
                return;
            }
            
            // Prepare JSON data that matches API expectations
            const requestData = {
                waste_type: document.getElementById('waste_type').value,
                preferred_date: document.getElementById('preferred_date').value + 'T' + getTimeFromSelection(),
                phone: document.getElementById('phone').value,
                location: document.getElementById('resolved_address').value || `${lat}, ${lng}`,
                notes: document.getElementById('notes').value,
                urgency: 'Normal', // Default urgency
                resolved_address: document.getElementById('resolved_address').value,
                address_details: document.getElementById('notes').value,
                latitude: lat,
                longitude: lng
            };
            
            // Show loading state
            const submitBtn = document.querySelector('.btn-submit');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
            submitBtn.disabled = true;
            
            showMessage('<i class="fas fa-spinner fa-spin"></i> Submitting your request...', 'loading');
            
            // Submit form with JSON data
            fetch('../../backend/api/place_request.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(requestData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage('✅ Request submitted successfully! You will receive a confirmation SMS shortly.', 'success');
                    
                    // Reset form with animation
                    setTimeout(() => {
                        this.reset();
                        clearLocation();
                        
                        // Reset submit button
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    }, 2000);
                    
                } else {
                    showMessage('❌ Error: ' + (data.message || 'Failed to submit request'), 'error');
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            })
            .catch(error => {
                showMessage('❌ Network error. Please check your connection and try again.', 'error');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
                console.error('Submission error:', error);
            });
        });
        
        function showMessage(message, type) {
            const messageEl = document.getElementById('result-message');
            messageEl.innerHTML = message;
            messageEl.className = `result-message ${type}`;
            messageEl.style.display = 'block';
            
            // Auto-hide after 5 seconds for non-error messages
            if (type !== 'error') {
                setTimeout(() => {
                    messageEl.style.display = 'none';
                }, 5000);
            }
        }
        
        function confirmLogout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = '../api/logout.php';
            }
        }
    </script>
</body>
</html>
