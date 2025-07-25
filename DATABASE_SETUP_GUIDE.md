# 🗄️ Database Setup Guide - PlanetScale (Free MySQL)

## Why PlanetScale?
✅ **10GB free storage** - Perfect for DRMS  
✅ **1 billion row reads/month** - More than enough  
✅ **10 million row writes/month** - Handles all your traffic  
✅ **No credit card required** for free tier  
✅ **Built-in branching** like Git for databases  
✅ **Automatic backups and scaling**  

## Step-by-Step Setup

### 1. Create PlanetScale Account
1. Go to **https://planetscale.com**
2. Click **"Sign up"**
3. Choose **"Sign up with GitHub"** (recommended)
4. Verify your email if prompted

### 2. Create Database
1. Click **"Create database"**
2. **Database name**: `drms-production`
3. **Region**: Choose closest to your users (US East, EU West, etc.)
4. **Plan**: Select **"Hobby"** (Free)
5. Click **"Create database"**

### 3. Get Connection Details
1. Go to your database dashboard
2. Click **"Connect"**
3. Select **"PHP (PDO)"** 
4. Copy the connection string - it looks like:
   ```
   mysql://username:password@host:3306/database_name?sslaccept=strict
   ```

### 4. Parse Connection String for Render
From your connection string, extract these values:

**Example**: `mysql://abc123:pscale_pw_xyz@aws.connect.psdb.cloud:3306/drms-production?sslaccept=strict`

- **DATABASE_HOST**: `aws.connect.psdb.cloud`
- **DATABASE_NAME**: `drms-production`
- **DATABASE_USER**: `abc123`
- **DATABASE_PASSWORD**: `pscale_pw_xyz`
- **DATABASE_PORT**: `3306`

### 5. Add to Render Environment Variables
Go to Render dashboard → Your service → **Environment** tab:

```
DATABASE_HOST=your_planetscale_host
DATABASE_NAME=drms-production
DATABASE_USER=your_planetscale_username
DATABASE_PASSWORD=your_planetscale_password
DATABASE_PORT=3306
```

### 6. Import Your Database Schema

#### Option A: Using PlanetScale Console (Web Interface)
1. In PlanetScale dashboard, click **"Console"**
2. Select your database and branch (`main`)
3. Copy and paste your SQL schema
4. Run the queries

#### Option B: Using PlanetScale CLI (Advanced)
```bash
# Install PlanetScale CLI
npm install -g @planetscale/cli

# Login
pscale auth login

# Connect to your database
pscale shell drms-production main

# Import your schema
-- Copy and paste your CREATE TABLE statements
```

## 📋 Your Database Schema (Ready to Import)

Here's your DRMS schema ready for PlanetScale:

```sql
-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(15) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('resident', 'driver', 'admin') DEFAULT 'resident',
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Waste requests table
CREATE TABLE waste_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    pickup_date DATE NOT NULL,
    pickup_time TIME NOT NULL,
    waste_type VARCHAR(50) NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    location TEXT NOT NULL,
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    status ENUM('pending', 'assigned', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    driver_id INT,
    special_instructions TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (driver_id) REFERENCES users(id) ON DELETE SET NULL
);

-- SMS logs table
CREATE TABLE sms_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    phone_number VARCHAR(15) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
    gateway VARCHAR(50) DEFAULT 'blessedtext',
    response_data TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Payment transactions table
CREATE TABLE payment_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    request_id INT,
    amount DECIMAL(10,2) NOT NULL,
    phone_number VARCHAR(15) NOT NULL,
    transaction_id VARCHAR(100),
    checkout_request_id VARCHAR(100),
    status ENUM('pending', 'success', 'failed', 'cancelled') DEFAULT 'pending',
    mpesa_receipt_number VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (request_id) REFERENCES waste_requests(id) ON DELETE SET NULL
);

-- Admin settings table
CREATE TABLE admin_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default admin user
INSERT INTO users (name, email, phone, password, role) VALUES 
('Admin User', 'admin@drms.com', '+254700000000', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert default settings
INSERT INTO admin_settings (setting_key, setting_value) VALUES 
('app_name', 'DRMS - Digital Residential Waste Management'),
('default_pickup_fee', '200.00'),
('contact_phone', '+254700000000'),
('contact_email', 'info@drms.com');
```

## 🚀 Next Steps After Database Setup

1. **Import the schema above** into PlanetScale
2. **Add environment variables** to Render
3. **Restart your Render service** (deploy will happen automatically)
4. **Test database connection** by visiting your app

## 🔍 Test Database Connection

Create this test file to verify everything works:

**Visit**: `https://your-app.onrender.com/test_db_connection.php`

The connection should work once you complete the setup!

## 🎯 Ready to Start?

**Which step are you on?**
1. Creating PlanetScale account
2. Setting up the database  
3. Getting connection details
4. Adding to Render environment variables
5. Importing schema

Let me know where you need help!
