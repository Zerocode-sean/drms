<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DRWMS - Secure Payment</title>
    <link rel="stylesheet" href="../css/pay.css">
    <link rel="icon" href="../images/logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">
                <img src="../images/logo.png" alt="DRWMS Logo" class="logo-img">
             
                <span>DRWMS</span>
            </div>
            <div class="nav-links">
                <a href="home.php" class="nav-link">
                    <i class="fas fa-home"></i>
                    <span>Home</span>
                </a>
                <a href="logout.php" class="nav-link">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </nav>
    <div class="container">
        <div class="payment-card">
            <div class="card-header">
                <div class="logo-section">
                    <i class="fas fa-shield-alt security-icon"></i>
                    <h1>Secure Payment</h1>
                </div>
                <p class="subtitle">Complete your waste management service payment securely with M-Pesa</p>
            </div>
            
            <form id="paymentForm" class="payment-form">
                <div class="input-group">
                    <label for="amount">
                        <i class="fas fa-money-bill-wave"></i>
                        Amount (KSh)
                    </label>
                    <div class="input-wrapper">
                        <input type="number" id="amount" name="amount" placeholder="0.00" min="10" required>
                        <span class="currency">KES</span>
                    </div>
                </div>
                
                <div class="input-group">
                    <label for="phone">
                        <i class="fas fa-mobile-alt"></i>
                        M-Pesa Phone Number
                    </label>
                    <div class="input-wrapper">
                        <input type="tel" id="phone" name="phone" placeholder="254712345678" pattern="[0-9]{12}" required>
                        <i class="fas fa-phone input-icon"></i>
                    </div>
                </div>
                
                <div class="input-group">
                    <label for="service">
                        <i class="fas fa-tag"></i>
                        Service ID
                    </label>
                    <div class="input-wrapper">
                        <input type="text" id="service" name="service" placeholder="e.g., WM-12345" required>
                        <i class="fas fa-hashtag input-icon"></i>
                    </div>
                </div>
                
                <button type="submit" class="pay-button">
                    <span class="button-content">
                        <i class="fas fa-lock"></i>
                        <span>Pay Securely</span>
                    </span>
                    <div class="button-loading" style="display: none;">
                        <i class="fas fa-spinner fa-spin"></i>
                        Processing...
                    </div>
                </button>
                
                <div id="status" class="status"></div>
                
                <!-- Hidden timer SVG template -->
                <svg style="display: none;">
                    <defs>
                        <circle id="timerCircle" cx="50" cy="50" r="45" fill="none" stroke="#e0e0e0" stroke-width="4"/>
                    </defs>
                </svg>
            </form>
            
            <div class="security-features">
                <div class="security-item">
                    <i class="fas fa-lock"></i>
                    <span>256-bit SSL Encryption</span>
                </div>
                <div class="security-item">
                    <i class="fas fa-shield-alt"></i>
                    <span>PCI DSS Compliant</span>
                </div>
                <div class="security-item">
                    <i class="fas fa-check-circle"></i>
                    <span>Instant Confirmation</span>
                </div>
            </div>
            
            <div class="footer-note">
                <p><i class="fas fa-mobile-alt"></i> Powered by Safaricom M-Pesa</p>
                <p><i class="fas fa-copyright"></i> 2025 DRWMS | John Mutua</p>
            </div>
        </div>
    </div>
    
    <script src="../js/pay.js"></script>
</body>
</html>