<!DOCTYPE html>
<html>
<head>
    <title>Simple Map Test for Request Page</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="../css/drm-styles.css">
    <style>
        body { margin: 20px; font-family: Arial, sans-serif; }
        #simple-map { 
            height: 400px !important; 
            width: 100% !important; 
            border: 3px solid #007bff !important;
            margin: 20px 0 !important;
            background: #f8f9fa !important;
            display: block !important;
            position: relative !important;
            z-index: 1 !important;
        }
        .status { 
            padding: 10px; 
            margin: 10px 0; 
            border-radius: 5px;
            background: #f0f8ff;
            border: 1px solid #007bff;
        }
        button {
            padding: 8px 16px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin: 5px;
        }
    </style>
</head>
<body>
    <h1>🗺️ Simple Map Test for Request Page</h1>
    
    <div class="status">
        <strong>Status:</strong> <span id="status">Loading...</span>
    </div>
    
    <div id="simple-map"></div>
    
    <button onclick="testSimpleMap()">🔄 Initialize Map</button>
    <button onclick="testLocation()">📍 Test Location</button>
    
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        let testMap = null;
        let testMarker = null;
        
        function updateStatus(msg) {
            document.getElementById('status').textContent = msg;
            console.log('STATUS:', msg);
        }
        
        function testSimpleMap() {
            try {
                console.log('=== TESTING SIMPLE MAP ===');
                updateStatus('Initializing...');
                
                // Check basics
                if (typeof L === 'undefined') {
                    throw new Error('Leaflet not loaded');
                }
                
                const mapEl = document.getElementById('simple-map');
                if (!mapEl) {
                    throw new Error('Map element not found');
                }
                
                // Clear existing
                if (testMap) {
                    testMap.remove();
                    testMap = null;
                }
                mapEl.innerHTML = '';
                
                // Create map
                console.log('Creating map...');
                testMap = L.map('simple-map').setView([-1.2921, 36.8219], 13);
                
                console.log('Adding tiles...');
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors'
                }).addTo(testMap);
                
                console.log('Adding marker...');
                testMarker = L.marker([-1.2921, 36.8219])
                    .addTo(testMap)
                    .bindPopup('Test marker!')
                    .openPopup();
                
                testMap.on('click', function(e) {
                    updateStatus(`Clicked: ${e.latlng.lat.toFixed(4)}, ${e.latlng.lng.toFixed(4)}`);
                });
                
                setTimeout(() => {
                    if (testMap) {
                        testMap.invalidateSize();
                        updateStatus('✅ Map loaded successfully!');
                    }
                }, 500);
                
            } catch (error) {
                console.error('Simple map error:', error);
                updateStatus('❌ Error: ' + error.message);
            }
        }
        
        function testLocation() {
            if (navigator.geolocation) {
                updateStatus('Getting location...');
                navigator.geolocation.getCurrentPosition(
                    function(pos) {
                        const lat = pos.coords.latitude;
                        const lng = pos.coords.longitude;
                        updateStatus(`Location: ${lat.toFixed(4)}, ${lng.toFixed(4)}`);
                        if (testMap) {
                            testMap.setView([lat, lng], 15);
                        }
                    },
                    function(error) {
                        updateStatus('Location error: ' + error.message);
                    }
                );
            } else {
                updateStatus('Geolocation not supported');
            }
        }
        
        // Auto-initialize
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM ready, testing in 2 seconds...');
            setTimeout(testSimpleMap, 2000);
        });
    </script>
</body>
</html>
