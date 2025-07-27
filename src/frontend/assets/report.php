<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DRMS | Report</title>
    
    <link rel="stylesheet" href="<?php echo cssPath('report.css'); ?>">
    <link rel="icon" href="<?php echo logoPath(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="logo">
            <img src="<?php echo logoPath(); ?>" alt="DRWMS Logo" class="logo-img">
        </div>
        <div class="nav-links">
            <a href="home.php"><i class="fas fa-home"></i> Home</a>
            <a href="#" onclick="confirmLogout()"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </nav>
    
    <main>
        <!-- Hero Section -->
        <section class="hero-section">
            <div class="hero-content">
                <h1>Report Issues</h1>
                <p>Got a <span class="highlight">complaint</span> about any of our services? Submit a <span class="highlight">quick report</span> here and we will <span class="highlight-success">get in touch</span> with you as soon as possible.</p>
                <div class="hero-buttons">
                    <button class="btn-secondary" onclick="window.location.href='about.php'">
                        <i class="fas fa-info-circle"></i> Learn More
                    </button>
                    <button class="btn-secondary" onclick="window.location.href='contact.php'">
                        <i class="fas fa-envelope"></i> Contact Us
                    </button>
                </div>
            </div>
        </section>

        <!-- Report Form Section -->
        <section class="report-section">
            <div class="form-container">
                <div class="form-header">
                    <h2><i class="fas fa-clipboard-list"></i> Submit a Quick Report</h2>
                    <p>Help us improve our services by reporting any issues you encounter</p>
                </div>
                
                <form id="reportForm" action="submit_report.php" method="POST" class="report-form">
                    <div class="form-group">
                        <label for="report_type">
                            <i class="fas fa-tag"></i> Report Type:
                        </label>
                        <select name="report_type" id="report_type" required>
                            <option value="">Select report type...</option>
                            <option value="Waste Service Issue">Waste Service Issue</option>
                            <option value="Environmental Concern">Environmental Concern</option>
                            <option value="Technical Problem">Technical Problem</option>
                            <option value="Driver Behavior">Driver Behavior</option>
                            <option value="Billing Issue">Billing Issue</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="location">
                            <i class="fas fa-map-marker-alt"></i> Location:
                        </label>
                        <input type="text" name="location" id="location" placeholder="Enter your location" required>
                    </div>

                    <div class="form-group">
                        <label for="description">
                            <i class="fas fa-align-left"></i> Description:
                        </label>
                        <textarea name="description" id="description" placeholder="Describe the issue in detail..." required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="impact">
                            <i class="fas fa-exclamation-triangle"></i> Impact:
                        </label>
                        <textarea name="impact" id="impact" placeholder="How does this issue affect you or the community?" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="suggestion">
                            <i class="fas fa-lightbulb"></i> Suggestion:
                        </label>
                        <textarea name="suggestion" id="suggestion" placeholder="Any suggestions for improvement?" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="additional_notes">
                            <i class="fas fa-sticky-note"></i> Additional Notes:
                        </label>
                        <textarea name="additional_notes" id="additional_notes" placeholder="Any additional information..."></textarea>
                    </div>

                    <div class="form-group">
                        <label for="contact_preference">
                            <i class="fas fa-phone"></i> Contact Preference:
                        </label>
                        <input type="text" name="contact_preference" id="contact_preference" placeholder="Preferred contact method (phone, email, etc.)">
                    </div>

                    <input type="hidden" name="user_name" value="John">
                    <input type="hidden" name="report_date" value="<?php echo date('Y-m-d'); ?>">
// Include asset helper for environment-aware paths
require_once __DIR__ . '/../../backend/config/asset_helper.php';
                    <input type="hidden" name="report_time" value="<?php echo date('H:i:s'); ?>">
                    
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-paper-plane"></i> Submit Report
                    </button>
                </form>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <h3>Follow Us</h3>
            <div class="social-icons">
                <a href="https://x.com/_mutua1?t=TqLWoiqtydJvudV4_sd2qw&s=09" target="_blank" class="social-icon">
                    <i class="fa-brands fa-square-x-twitter"></i>
                </a>
                <a href="https://wa.me/254798587203" target="_blank" class="social-icon">
                    <i class="fa-brands fa-whatsapp"></i>
                </a>
                <a href="https://www.instagram.com/browshawnmutua/profilecard/?igsh=MTJ3Ynp5ZWs1ajRqYg==" target="_blank" class="social-icon">
                    <i class="fa-brands fa-square-instagram"></i>
                </a>
                <a href="https://github.com/Zerocode-sean" target="_blank" class="social-icon">
                    <i class="fa-brands fa-github"></i>
                </a>
                <a href="https://www.linkedin.com/in/john-mutua-5162b2248?utm_source=share&utm_campaign=share_via&utm_content=profile&utm_medium=android_app" target="_blank" class="social-icon">
                    <i class="fa-brands fa-linkedin"></i>
                </a>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 Digital Request Waste Management. All rights reserved</p>
            </div>
        </div>
    </footer>

    <script src="<?php echo jsPath('report.js'); ?>"></script>
    <script>
        function confirmLogout() {
            if (confirm('Are you sure you want to logout?')) {
                // Show loading state
                const logoutLink = document.querySelector('.nav-links a[onclick="confirmLogout()"]');
                const originalText = logoutLink.innerHTML;
                logoutLink.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Logging out...';
                logoutLink.style.pointerEvents = 'none';
                
                // Call the logout API
                fetch('../../backend/api/logout.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'include'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message briefly
                        logoutLink.innerHTML = '<i class="fas fa-check"></i> Logged out!';
                        setTimeout(() => {
                            window.location.href = 'login.php';
                        }, 1000);
                    } else {
                        // Handle error
                        logoutLink.innerHTML = originalText;
                        logoutLink.style.pointerEvents = 'auto';
                        alert('Logout failed. Please try again.');
                    }
                })
                .catch(error => {
                    console.error('Logout error:', error);
                    // Fallback to direct logout
                    logoutLink.innerHTML = originalText;
                    logoutLink.style.pointerEvents = 'auto';
                    window.location.href = 'logout.php';
                });
            }
        }
        
        // Add keyboard shortcut for logout (Ctrl+L)
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'l') {
                e.preventDefault();
                confirmLogout();
            }
        });
    </script>
</body>
</html>