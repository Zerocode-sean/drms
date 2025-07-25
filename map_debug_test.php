<!DOCTYPE html>
<html>
<head>
    <title>Map Debug Test</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        body { margin: 20px; font-family: Arial, sans-serif; }
        #debug-map { 
            height: 400px; 
            width: 100%; 
            border: 3px solid red; 
            margin: 20px 0;
            background: lightblue;
        }
        .info { 
            padding: 10px; 
            background: #f0f0f0; 
            margin: 10px 0; 
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <h1>🛠️ Map Debug Test</h1>
    
    <div class="info">
        <strong>Status:</strong> <span id="status">Loading...</span>
    </div>
    
    <div class="info">
        <strong>Leaflet Available:</strong> <span id="leaflet-status">Checking...</span>
    </div>
    
    <div id="debug-map"></div>
    
    <button onclick="testMap()">🔄 Test Map</button>
    <button onclick="checkLeaflet()">📋 Check Leaflet</button>
    
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        function updateStatus(msg) {
            document.getElementById('status').textContent = msg;
            console.log(msg);
        }
        
        function checkLeaflet() {
            const available = typeof L !== 'undefined';
            document.getElementById('leaflet-status').textContent = available ? `Yes (v${L.version})` : 'No';
            updateStatus(available ? 'Leaflet is available' : 'Leaflet is NOT available');
        }
        
        function testMap() {
            try {
                updateStatus('Testing map...');
                
                // Clear any existing map
                const container = document.getElementById('debug-map');
                container.innerHTML = '';
                
                if (typeof L === 'undefined') {
                    throw new Error('Leaflet not loaded');
                }
                
                // Create map
                const map = L.map('debug-map').setView([-1.2921, 36.8219], 13);
                
                // Add tiles
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors'
                }).addTo(map);
                
                // Add marker
                L.marker([-1.2921, 36.8219])
                    .addTo(map)
                    .bindPopup('Test marker!')
                    .openPopup();
                
                updateStatus('✅ Map created successfully!');
                
            } catch (error) {
                updateStatus('❌ Error: ' + error.message);
                console.error('Map test error:', error);
            }
        }
        
        // Auto-run tests
        setTimeout(() => {
            checkLeaflet();
            setTimeout(testMap, 1000);
        }, 500);
    </script>
</body>
</html>
