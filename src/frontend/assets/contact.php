<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DRWMS - Contact Us</title>
    <link rel="stylesheet" href="/src/frontend/css/contact.css">
    <link rel="icon" href="/src/frontend/images/logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="contact-card">
            <div class="card-header">
                <div class="header-content">
                    <i class="fas fa-envelope-open-text"></i>
                    <h1>Get in Touch</h1>
                    <p>We'd love to hear from you. Send us a message and we'll respond as soon as possible.</p>
                </div>
            </div>

            <div class="contact-content">
                <div class="contact-form-section">
                    <h2>Send us a Message</h2>
                    <form id="contactForm" class="contact-form">
                        <div class="form-row">
                            <div class="input-group">
                                <label for="name">
                                    <i class="fas fa-user"></i>
                                    Full Name *
                                </label>
                                <input type="text" id="name" name="name" placeholder="Enter your full name" required>
                                <span class="error" id="nameError"></span>
                            </div>
                            <div class="input-group">
                                <label for="email">
                                    <i class="fas fa-envelope"></i>
                                    Email Address *
                                </label>
                                <input type="email" id="email" name="email" placeholder="your.email@example.com" required>
                                <span class="error" id="emailError"></span>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="input-group">
                                <label for="phone">
                                    <i class="fas fa-phone"></i>
                                    Phone Number
                                </label>
                                <input type="tel" id="phone" name="phone" placeholder="+254712345678" pattern="[0-9]{12}">
                                <span class="error" id="phoneError"></span>
                            </div>
                            <div class="input-group">
                                <label for="inquiry">
                                    <i class="fas fa-tag"></i>
                                    Inquiry Type *
                                </label>
                                <select id="inquiry" name="inquiry" required>
                                    <option value="">Select an option</option>
                                    <option value="pickup">Waste Pickup</option>
                                    <option value="billing">Billing Issue</option>
                                    <option value="general">General Inquiry</option>
                                    <option value="support">Technical Support</option>
                                    <option value="feedback">Feedback</option>
                                </select>
                                <span class="error" id="inquiryError"></span>
                            </div>
                        </div>

                        <div class="input-group full-width">
                            <label for="message">
                                <i class="fas fa-comment"></i>
                                Message *
                            </label>
                            <textarea id="message" name="message" rows="5" placeholder="Tell us how we can help you..." required></textarea>
                            <span class="error" id="messageError"></span>
                        </div>

                        <button type="submit" class="submit-button">
                            <span class="button-content">
                                <i class="fas fa-paper-plane"></i>
                                <span>Send Message</span>
                            </span>
                            <div class="button-loading" style="display: none;">
                                <i class="fas fa-spinner fa-spin"></i>
                                Sending...
                            </div>
                        </button>
                        
                        <div id="status" class="status"></div>
                    </form>
                </div>

                <div class="contact-info-section">
                    <h2>Contact Information</h2>
                    <div class="contact-details">
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="contact-text">
                                <h3>Email</h3>
                                <p>support@drwms.com</p>
                                <small>We'll respond within 24 hours</small>
                            </div>
                        </div>

                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div class="contact-text">
                                <h3>Phone</h3>
                                <p>+254712345678</p>
                                <small>Mon-Fri, 8 AM - 5 PM EAT</small>
                            </div>
                        </div>

                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="contact-text">
                                <h3>Address</h3>
                                <p>Nairobi, Kenya</p>
                                <small>Headquarters</small>
                            </div>
                        </div>

                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="contact-text">
                                <h3>Business Hours</h3>
                                <p>Monday - Friday</p>
                                <small>8:00 AM - 5:00 PM EAT</small>
                            </div>
                        </div>
                    </div>

                    <div class="quick-links">
                        <h3>Quick Links</h3>
                        <div class="links-grid">
                            <a href="about.php" class="quick-link">
                                <i class="fas fa-info-circle"></i>
                                <span>About Us</span>
                            </a>
                            <a href="home.php" class="quick-link">
                                <i class="fas fa-home"></i>
                                <span>Home</span>
                            </a>
                            <a href="pay.php" class="quick-link">
                                <i class="fas fa-credit-card"></i>
                                <span>Payment</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="/src/frontend/js/contact.js"></script>
</body>
</html>