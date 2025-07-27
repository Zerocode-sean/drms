<?php
require_once __DIR__ . '/../../backend/config/session.php';
// Include asset helper for environment-aware paths
require_once __DIR__ . '/../../backend/config/asset_helper.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header('Location: login.php');
    exit;
}

// Only allow residents to access the home page
if ($_SESSION['role'] !== 'resident') {
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin.php');
    } elseif ($_SESSION['role'] === 'driver') {
        header('Location: driver.php');
    }
    exit;
}

$username = $_SESSION['username'];
$role = $_SESSION['role'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DRMS - Digital Request Waste Management System</title>
    <link rel="stylesheet" href="<?php echo cssPath('drm-styles.css'); ?>">
    <link rel="icon" href="<?php echo logoPath(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <img src="<?php echo logoPath(); ?>" alt="DRMS Logo" class="logo-img">
                <h2>DRMS</h2>
            </div>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="#home" class="nav-link">Home</a>
                </li>
                <li class="nav-item">
                    <a href="request.php" class="nav-link">Request</a>
                </li>
                <li class="nav-item">
                    <a href="pay.php" class="nav-link">Pay</a>
                </li>
                <li class="nav-item">
                    <a href="report.php" class="nav-link">Report</a>
                </li>
                <li class="nav-item">
                    <a href="contact.php" class="nav-link">Contact</a>
                </li>
                <li class="nav-item">
                    <a href="#notifications" class="nav-link" id="notification-bell">
                        <i class="fas fa-bell"></i>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" onclick="logout()">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
            <div class="hamburger">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </div>
        </div>
    </nav>

    <!-- Notification Box -->
    <div class="notification-box" id="notificationBox">
        <div class="notification-header">
            <h3>Notifications</h3>
            <button class="close-btn" onclick="toggleNotifications()">✖</button>
        </div>
        <div class="notification-content"></div>
    </div>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="hero-container">
            <div class="hero-content">
                <h2 class="hero-welcome">Hello <span><?php echo htmlspecialchars($username); ?></span>, Welcome Back.</h2>
                <h1 class="hero-title">
                    Together, <span>We Can</span> Make a <span>Difference</span>
                </h1>
                <p class="hero-subtitle">
                    Sustainable Solutions for a Sustainable Planet
                </p>
                <div class="hero-buttons">
                    <a href="about.php" class="btn btn-primary">
                        <i class="fas fa-info-circle"></i>
                        Learn More
                    </a>
                    <a href="contact.php" class="btn btn-secondary">
                        Contact Us
                    </a>
                </div>
            </div>
            <div class="hero-image">
                <div class="hero-illustration">
                    <div class="dashboard-mockup">
                        <div class="mockup-header">
                            <div class="mockup-dots">
                                <span></span>
                                <span></span>
                                <span></span>
                            </div>
                        </div>
                        <div class="mockup-content">
                            <div class="mockup-sidebar">
                                <div class="sidebar-item active">
                                    <i class="fas fa-home"></i>
                                    <span>Dashboard</span>
                                </div>
                                <div class="sidebar-item">
                                    <i class="fas fa-recycle"></i>
                                    <span>Waste</span>
                                </div>
                                <div class="sidebar-item">
                                    <i class="fas fa-truck"></i>
                                    <span>Pickup</span>
                                </div>
                                <div class="sidebar-item">
                                    <i class="fas fa-chart-bar"></i>
                                    <span>Reports</span>
                                </div>
                            </div>
                            <div class="mockup-main">
                                <div class="mockup-card">
                                    <h3>Waste Recycled</h3>
                                    <p class="amount">500 kg</p>
                                    <span class="trend positive">+12.5%</span>
                                </div>
                                <div class="mockup-card">
                                    <h3>CO2 Reduced</h3>
                                    <p class="amount">300 kg</p>
                                    <span class="trend positive">+8.2%</span>
                                </div>
                                <div class="mockup-card">
                                    <h3>Trees Saved</h3>
                                    <p class="amount">10</p>
                                    <span class="trend neutral">+2</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <h2>Ready to make a difference?</h2>
            <p>Join DRWMS and be part of a cleaner, greener future.</p>
            <a href="register.php" class="btn btn-primary">
                <i class="fas fa-rocket"></i>
                Get Started
            </a>
        </div>
    </section>

    <!-- Quick Request Section -->
    <section class="quick-request-section" id="request">
        <div class="container">
            <div class="request-card">
                <h2><b>Submit a Quick Request</b></h2>
                <form id="quick-request-form">
                    <div class="form-group">
                        <label for="request-type">Request Type:</label>
                        <select id="request-type" name="request-type">
                            <option value="waste-pickup">Waste Pickup</option>
                            <option value="recycling">Recycling Collection</option>
                            <option value="special-request">Special Request</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="request-date">Preferred Date:</label>
                        <input type="date" id="request-date" name="request-date" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit Request</button>
                </form>
            </div>
        </div>
    </section>

    <!-- Metrics Section -->
    <section class="metrics-section">
        <div class="container">
            <div class="metrics-grid">
                <div class="metric-card">
                    <div class="metric-icon">
                        <i class="fas fa-recycle"></i>
                    </div>
                    <h3>Waste Recycled</h3>
                    <p class="metric-value">500 kg</p>
                </div>
                <div class="metric-card">
                    <div class="metric-icon">
                        <i class="fas fa-leaf"></i>
                    </div>
                    <h3>CO2 Reduced</h3>
                    <p class="metric-value">300 kg</p>
                </div>
                <div class="metric-card">
                    <div class="metric-icon">
                        <i class="fas fa-tree"></i>
                    </div>
                    <h3>Trees Saved</h3>
                    <p class="metric-value">10</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Request Status Section -->
    <section class="status-section">
        <div class="container">
            <div class="status-card">
                <h2>Your Recent Requests</h2>
                <div id="recent-requests-container">
                    <div class="loading">Loading your requests...</div>
                </div>
                <a href="request.php" class="btn btn-secondary">View All Requests</a>
            </div>
        </div>
    </section>

    <!-- Sustainability Tips Section -->
    <section class="tips-section">
        <div class="container">
            <div class="tips-card">
                <h2>Sustainability Tips</h2>
                <ul class="tips-list">
                    <li>Separate recyclables from waste to improve collection efficiency.</li>
                    <li>Reduce single-use plastics by using reusable bags and bottles.</li>
                    <li>Schedule regular pickups to avoid waste buildup.</li>
                </ul>
                <a href="#sustainability-guide" class="btn btn-primary">Learn More</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="footer-content">
            <div class="footer-social">
                <h3>Follow Us</h3>
                <div class="social-links">
                    <a href="https://x.com/_mutua1?t=TqLWoiqtydJvudV4_sd2qw&s=09" target="_blank" class="social-link twitter">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="https://wa.me/254798587203" target="_blank" class="social-link whatsapp">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                    <a href="https://www.instagram.com/browshawnmutua/profilecard/?igsh=MTJ3Ynp5ZWs1ajRqYg==" target="_blank" class="social-link instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="https://github.com/Zerocode-sean" target="_blank" class="social-link github">
                        <i class="fab fa-github"></i>
                    </a>
                    <a href="https://www.linkedin.com/in/john-mutua-5162b2248?utm_source=share&utm_campaign=share_via&utm_content=profile&utm_medium=android_app" target="_blank" class="social-link linkedin">
                        <i class="fab fa-linkedin"></i>
                    </a>
                </div>
            </div>
            <div class="footer-bottom">
                <p>© <span>2025</span> Digital Request Waste <span>Management</span>. All rights <span>reserved</span></p>
            </div>
        </div>
    </footer>

    <script src="<?php echo jsPath('drm-script.js'); ?>"></script>
    <script>
        function logout() {
            fetch('../../backend/api/logout.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = 'login.php';
                }
            })
            .catch(error => {
                console.error('Logout error:', error);
                // Redirect anyway
                window.location.href = 'login.php';
            });
        }

        // Function to fetch and display recent requests
        function loadRecentRequests() {
            const container = document.getElementById('recent-requests-container');
            
            console.log('Loading recent requests...');
            
            fetch('../../backend/api/get_recent_requests.php', {
                credentials: 'include'
            })
            .then(response => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Received data:', data);
                
                if (Array.isArray(data) && data.length > 0) {
                    const requestsHTML = data.slice(0, 5).map(request => {
                        const statusClass = getStatusClass(request.status);
                        const formattedDate = new Date(request.created_at).toLocaleDateString();
                        const requestType = request.document || request.waste_type || 'Waste Collection';
                        
                        console.log('Processing request:', request);
                        
                        return `
                            <div class="status-item">
                                <p>${requestType} - ${formattedDate}</p>
                                <div class="status-bar">
                                    <div class="status-progress ${statusClass}">${request.status}</div>
                                </div>
                            </div>
                        `;
                    }).join('');
                    
                    container.innerHTML = requestsHTML;
                    console.log('Requests displayed successfully');
                } else {
                    container.innerHTML = '<div class="no-requests">No recent requests found.</div>';
                    console.log('No requests found');
                }
            })
            .catch(error => {
                console.error('Error loading recent requests:', error);
                container.innerHTML = '<div class="error">Error loading requests. Please try again.</div>';
            });
        }

        // Function to get CSS class for status
        function getStatusClass(status) {
            switch(status) {
                case 'Pending':
                    return 'pending';
                case 'Approved':
                    return 'approved';
                case 'Assigned':
                    return 'assigned';
                case 'In Progress':
                    return 'in-progress';
                case 'Completed':
                    return 'completed';
                case 'Rejected':
                    return 'rejected';
                case 'Missed':
                    return 'missed';
                default:
                    return 'pending';
            }
        }

        // Load recent requests when page loads
        document.addEventListener('DOMContentLoaded', function() {
            loadRecentRequests();
            
            // Refresh requests every 30 seconds
            setInterval(loadRecentRequests, 30000);
        });
    </script>
</body>
</html>
