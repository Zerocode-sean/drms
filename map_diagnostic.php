<?php
session_start();
// Simulate user session for testing
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'testuser';
$_SESSION['role'] = 'resident';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Map Diagnostic Tool</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            margin: 0;
            font-size: 2.5rem;
        }
        
        .content {
            padding: 30px;
        }
        
        .test-section {
            background: #f8f9fa;
            margin: 20px 0;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid #007bff;
        }
        
        .test-section h3 {
            margin-top: 0;
            color: #333;
        }
        
        .status {
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
        }
        
        .status.info { background: #d1ecf1; color: #0c5460; border-left: 4px solid #17a2b8; }
        .status.success { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }
        .status.error { background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545; }
        .status.warning { background: #fff3cd; color: #856404; border-left: 4px solid #ffc107; }
        
        #map {
            height: 400px;
            width: 100%;
            border: 3px solid #007bff;
            border-radius: 10px;
            margin: 20px 0;
            background: #e9ecef;
        }
        
        .controls {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin: 20px 0;
        }
        
        button {
            padding: 12px 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        button:hover {
            background: #0056b3;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,123,255,0.3);
        }
        
        button.success { background: #28a745; }
        button.success:hover { background: #218838; }
        
        button.danger { background: #dc3545; }
        button.danger:hover { background: #c82333; }
        
        .results {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .step {
            display: flex;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        
        .step:last-child { border-bottom: none; }
        
        .step-icon {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-weight: bold;
            color: white;
        }
        
        .step-icon.pass { background: #28a745; }
        .step-icon.fail { background: #dc3545; }
        .step-icon.pending { background: #6c757d; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 Map Diagnostic Tool</h1>
            <p>Comprehensive testing for map loading issues</p>
        </div>
        
        <div class="content">
            <div class="test-section">
                <h3>🚀 Quick Actions</h3>
                <div class="controls">
                    <button onclick="runFullDiagnostic()">🔍 Full Diagnostic</button>
                    <button onclick="testMapInit()" class="success">🗺️ Test Map</button>
                    <button onclick="clearResults()" class="danger">🧹 Clear Results</button>
                    <button onclick="resetMap()">🔄 Reset Map</button>
                </div>
            </div>
            
            <div id="live-status" class="status info">Ready to start diagnostics...</div>
            
            <div class="test-section">
                <h3>🗺️ Map Container</h3>
                <div id="map"></div>
                <div class="controls">
                    <button onclick="initMap()">Initialize Map</button>
                    <button onclick="testMapFeatures()">Test Features</button>
                </div>
            </div>
            
            <div id="results-container" class="results" style="display: none;">
                <h3>📊 Diagnostic Results</h3>
                <div id="test-results"></div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        let map = null;
        let testResults = [];
        
        function updateStatus(message, type = 'info') {
            const status = document.getElementById('live-status');
            status.className = `status ${type}`;
            status.innerHTML = `[${new Date().toLocaleTimeString()}] ${message}`;
            console.log(`[${type.toUpperCase()}] ${message}`);
        }
        
        function addTestResult(test, passed, details = '') {
            testResults.push({
                test: test,
                passed: passed,
                details: details,
                timestamp: new Date().toLocaleTimeString()
            });
            displayResults();
        }
        
        function displayResults() {
            const container = document.getElementById('results-container');
            const results = document.getElementById('test-results');
            
            if (testResults.length === 0) {
                container.style.display = 'none';
                return;
            }
            
            container.style.display = 'block';
            
            const html = testResults.map(result => `
                <div class="step">
                    <div class="step-icon ${result.passed ? 'pass' : 'fail'}">
                        ${result.passed ? '✓' : '✗'}
                    </div>
                    <div>
                        <strong>${result.test}</strong><br>
                        <small style="color: #666;">${result.details}</small>
                        <small style="float: right; color: #999;">${result.timestamp}</small>
                    </div>
                </div>
            `).join('');
            
            results.innerHTML = html;
        }
        
        function clearResults() {
            testResults = [];
            displayResults();
            updateStatus('Results cleared', 'info');
        }
        
        function resetMap() {
            updateStatus('Resetting map...', 'info');
            
            if (map) {
                map.remove();
                map = null;
            }
            
            document.getElementById('map').innerHTML = '';
            updateStatus('Map reset completed', 'success');
        }
        
        async function runFullDiagnostic() {
            updateStatus('Starting full diagnostic...', 'info');
            clearResults();
            
            // Test 1: Check environment
            updateStatus('Testing environment...', 'info');
            
            const leafletLoaded = typeof L !== 'undefined';
            addTestResult('Leaflet Library', leafletLoaded, leafletLoaded ? 'Version: ' + L.version : 'Library not found');
            
            const mapElement = document.getElementById('map');
            const elementExists = mapElement !== null;
            addTestResult('Map Element', elementExists, elementExists ? 'Element found in DOM' : 'Element missing');
            
            if (elementExists) {
                const rect = mapElement.getBoundingClientRect();
                const hasSize = rect.width > 0 && rect.height > 0;
                addTestResult('Element Size', hasSize, `${rect.width}x${rect.height}px`);
            }
            
            // Test 2: Network connectivity
            updateStatus('Testing network connectivity...', 'info');
            try {
                const response = await fetch('https://tile.openstreetmap.org/0/0/0.png', {
                    method: 'HEAD',
                    timeout: 5000
                });
                const networkOk = response.ok;
                addTestResult('Network Access', networkOk, networkOk ? 'Can reach tile servers' : 'Network error');
            } catch (error) {
                addTestResult('Network Access', false, `Error: ${error.message}`);
            }
            
            // Test 3: Try map initialization
            updateStatus('Testing map initialization...', 'info');
            try {
                const mapInitialized = await testMapInitialization();
                addTestResult('Map Initialization', mapInitialized, mapInitialized ? 'Map created successfully' : 'Failed to create map');
            } catch (error) {
                addTestResult('Map Initialization', false, `Error: ${error.message}`);
            }
            
            updateStatus('Diagnostic completed!', 'success');
        }
        
        async function testMapInitialization() {
            return new Promise((resolve) => {
                try {
                    resetMap();
                    
                    if (typeof L === 'undefined') {
                        resolve(false);
                        return;
                    }
                    
                    const mapEl = document.getElementById('map');
                    if (!mapEl) {
                        resolve(false);
                        return;
                    }
                    
                    map = L.map('map').setView([-1.2921, 36.8219], 13);
                    
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '© OpenStreetMap contributors'
                    }).addTo(map);
                    
                    setTimeout(() => {
                        if (map) {
                            map.invalidateSize();
                            resolve(true);
                        } else {
                            resolve(false);
                        }
                    }, 500);
                    
                } catch (error) {
                    console.error('Map initialization error:', error);
                    resolve(false);
                }
            });
        }
        
        function initMap() {
            updateStatus('Initializing map manually...', 'info');
            
            try {
                resetMap();
                
                if (typeof L === 'undefined') {
                    updateStatus('❌ Leaflet library not available', 'error');
                    return;
                }
                
                const mapEl = document.getElementById('map');
                if (!mapEl) {
                    updateStatus('❌ Map element not found', 'error');
                    return;
                }
                
                updateStatus('Creating map object...', 'info');
                map = L.map('map').setView([-1.2921, 36.8219], 13);
                
                updateStatus('Adding tile layer...', 'info');
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors',
                    maxZoom: 19
                }).addTo(map);
                
                updateStatus('Adding marker...', 'info');
                L.marker([-1.2921, 36.8219])
                    .addTo(map)
                    .bindPopup('Nairobi, Kenya - Click anywhere to test!')
                    .openPopup();
                
                map.on('click', function(e) {
                    updateStatus(`Map clicked at: ${e.latlng.lat.toFixed(4)}, ${e.latlng.lng.toFixed(4)}`, 'success');
                    L.marker([e.latlng.lat, e.latlng.lng])
                        .addTo(map)
                        .bindPopup(`New location: ${e.latlng.lat.toFixed(4)}, ${e.latlng.lng.toFixed(4)}`);
                });
                
                setTimeout(() => {
                    if (map) {
                        map.invalidateSize();
                        updateStatus('✅ Map initialization successful!', 'success');
                    }
                }, 300);
                
            } catch (error) {
                updateStatus(`❌ Map initialization failed: ${error.message}`, 'error');
                console.error('Map error:', error);
            }
        }
        
        function testMapFeatures() {
            if (!map) {
                updateStatus('❌ No map to test. Initialize map first.', 'error');
                return;
            }
            
            updateStatus('Testing map features...', 'info');
            
            try {
                // Test zoom
                map.setZoom(15);
                setTimeout(() => {
                    map.setZoom(11);
                    updateStatus('✅ Zoom functionality working', 'success');
                }, 500);
                
                // Test pan
                setTimeout(() => {
                    map.panTo([-1.3, 36.9]);
                    setTimeout(() => {
                        map.panTo([-1.2921, 36.8219]);
                        updateStatus('✅ Pan functionality working', 'success');
                    }, 1000);
                }, 1000);
                
            } catch (error) {
                updateStatus(`❌ Feature test failed: ${error.message}`, 'error');
            }
        }
        
        function testMapInit() {
            updateStatus('Running map initialization test...', 'info');
            initMap();
        }
        
        // Auto-run basic checks when page loads
        document.addEventListener('DOMContentLoaded', function() {
            updateStatus('Page loaded. Ready for testing.', 'success');
            
            // Auto-run a quick check
            setTimeout(() => {
                if (typeof L !== 'undefined') {
                    updateStatus('✅ Leaflet library detected', 'success');
                } else {
                    updateStatus('❌ Leaflet library not detected', 'error');
                }
            }, 500);
        });
    </script>
</body>
</html>
