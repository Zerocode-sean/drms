# 🚛 DRMS - Digital  Request Waste Management System

A comprehensive waste collection management platform with smart scheduling, SMS notifications, and real-time tracking.

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-blue.svg)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-orange.svg)](https://www.mysql.com/)

## 🌟 Features

### **For Residents**

- 📋 Easy waste collection request submission
- 📍 Location-based service
- 📱 SMS notifications for request status
- 📊 Real-time request tracking
- 📞 Customer support integration

### **For Drivers**

- 🚛 Task dashboard with assigned collections
- 🗺️ Route optimization and mapping
- 📱 SMS communication with customers
- ✅ Status update capabilities
- 📈 Performance tracking

### **For Administrators**

- 📊 Comprehensive dashboard with metrics
- ✅ Request approval/rejection workflow
- 👥 Driver assignment management
- 🧠 Smart scheduling algorithm
- 📱 Bulk SMS broadcasting
- 📈 Analytics and reporting

## 🚀 Smart Scheduling Algorithm

- **Geographic Clustering**: Groups requests within 2km radius
- **Route Optimization**: Nearest neighbor algorithm for fuel efficiency
- **Time Slot Management**: Morning (8-12), Afternoon (12-4), Evening (4-8)
- **Fuel Savings**: Estimates 30-40% reduction in operational costs

## 📱 SMS Integration

Powered by **Twilio** for reliable messaging:

- ✅ Request lifecycle notifications
- ✅ Driver assignment alerts
- ✅ Collection reminders
- ✅ Admin broadcasting
- ✅ 99.95% delivery guarantee

## 🛠️ Tech Stack

- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Backend**: PHP 7.4+ with MySQLi/PDO
- **Database**: MySQL 5.7+
- **SMS**: Twilio API
- **Server**: Apache (XAMPP)

## 📦 Installation

### Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache web server
- Twilio account (for SMS features)

### Setup Steps

1. **Clone the repository**

   ```bash
   git clone https://github.com/yourusername/drms.git
   cd drms
   ```

2. **Database Setup**

   ```bash
   # Create database
   mysql -u root -p -e "CREATE DATABASE drms2;"

   # Import schema
   mysql -u root -p drms2 < database.sql
   mysql -u root -p drms2 < sms_tables.sql
   ```

3. **Environment Configuration**

   ```bash
   # Copy environment template
   cp .env.example .env

   # Edit .env with your credentials
   nano .env
   ```

4. **Configure Environment Variables**

   ```env
   # Database
   DB_HOST=localhost
   DB_USER=root
   DB_PASS=your_password
   DB_NAME=drms2

   # Twilio (Get from https://console.twilio.com)
   TWILIO_ACCOUNT_SID=your_account_sid
   TWILIO_AUTH_TOKEN=your_auth_token
   TWILIO_PHONE_NUMBER=your_twilio_number
   ```

5. **Web Server Setup**

   ```bash
   # For XAMPP
   # Copy project to: C:\xampp\htdocs\drms

   # Access via: http://localhost/drms
   ```

## 🎯 Quick Start

### Default Users

After running the database setup:

- **Admin**: `admin` / `admin123`
- **Driver**: `driver1` / `driver123`
- **Resident**: `testuser` / `test123`

### First Steps

1. **Login as Admin**: Access admin dashboard
2. **Test SMS**: Visit `/src/frontend/assets/test_sms.html`
3. **Create Request**: Login as resident and submit a request
4. **Manage Workflow**: Approve requests and assign drivers

## 📱 SMS Testing

1. **Configure Twilio credentials** in `.env`
2. **Verify phone numbers** in Twilio Console (trial accounts)
3. **Test SMS integration**: Visit the SMS test page
4. **Demo workflow**: Submit → Approve → Assign → Complete

## 🔒 Security

This project implements comprehensive security measures:

- ✅ **Environment Variables**: No hardcoded credentials
- ✅ **SQL Injection Prevention**: Prepared statements
- ✅ **Password Hashing**: Bcrypt encryption
- ✅ **Session Management**: Secure session handling
- ✅ **Role-Based Access**: Admin/Driver/Resident permissions

See [SECURITY.md](SECURITY.md) for detailed security information.

## 📊 Database Schema

### Core Tables

- **users**: Authentication and role management
- **requests1**: Waste collection requests
- **drivers**: Driver profiles and capacity
- **assignments**: Request-driver relationships
- **schedules**: Optimized route data
- **notifications**: System notifications
- **sms_logs**: SMS delivery tracking

## 🎨 Screenshots

### Admin Dashboard

- Real-time metrics and analytics
- Request management workflow
- Driver assignment interface
- Smart scheduling controls

### Driver Interface

- Task dashboard with assignments
- Route optimization display
- Customer communication tools

### Resident Portal

- Simple request submission
- Status tracking
- Notification history

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 📞 Support

- **Documentation**: Check the `/docs` folder
- **Issues**: Open a GitHub issue
- **Email**: [ndindajohn22@gmail.com]

## 🎉 Acknowledgments

- **Twilio**: For reliable SMS API
- **PHP Community**: For excellent documentation
- **Open Source**: For making development accessible

---

**Built with ❤️ for efficient waste management**

## 🔮 Future Roadmap

- 📱 Mobile applications (iOS/Android)
- 🗺️ Real-time GPS tracking
- 🤖 AI-powered route optimization
- 🌐 Multi-location support
- ☁️ Cloud deployment options
- 📊 Advanced analytics dashboard
