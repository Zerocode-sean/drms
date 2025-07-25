# 🚀 DRMS DEPLOYMENT GUIDE - GITHUB TO PRODUCTION

## 📋 **Phase 1: Prepare Project for GitHub**

### 1. **Project Structure Reorganization**

Let's reorganize your project for optimal hosting:

```
drms/
├── public/                 # ✅ Web server document root
│   ├── index.php          # Main entry point
│   ├── assets/            # Static assets
│   │   ├── css/
│   │   ├── js/
│   │   └── images/
│   └── api/               # API endpoints (publicly accessible)
├── src/                   # ✅ Application source (protected)
│   ├── backend/
│   │   ├── config/
│   │   ├── models/
│   │   └── controllers/
│   ├── frontend/          # PHP templates
│   └── database/
├── vendor/                # ✅ Composer dependencies
├── storage/               # ✅ Logs, uploads, cache
├── .env.example          # ✅ Environment template
├── .gitignore            # ✅ Git ignore rules
├── composer.json         # ✅ PHP dependencies
├── Procfile              # ✅ For Heroku deployment
├── railway.toml          # ✅ For Railway deployment
└── README.md             # ✅ Documentation
```

### 2. **Essential Files to Create**

#### `.gitignore`

```gitignore
# Environment files
.env
.env.local
.env.production

# Dependencies
/vendor/
/node_modules/

# Logs
/storage/logs/
*.log

# Cache
/storage/cache/
/storage/sessions/

# Uploads
/storage/uploads/
/public/uploads/

# IDE files
.vscode/
.idea/
*.swp
*.swo

# OS files
.DS_Store
Thumbs.db

# Temporary files
/tmp/
*.tmp

# Database
*.sql
*.db
*.sqlite

# Test files
/tests/coverage/
```

#### `composer.json`

```json
{
  "name": "johnmutua/drms",
  "description": "Digital Residential Waste Management System",
  "type": "project",
  "license": "MIT",
  "authors": [
    {
      "name": "John Mutua",
      "email": "your.email@example.com"
    }
  ],
  "require": {
    "php": ">=8.0",
    "ext-curl": "*",
    "ext-json": "*",
    "ext-mysqli": "*",
    "ext-mbstring": "*"
  },
  "require-dev": {
    "phpunit/phpunit": "^9.0"
  },
  "autoload": {
    "psr-4": {
      "App\\": "src/"
    }
  },
  "scripts": {
    "start": "php -S localhost:8000 -t public",
    "test": "phpunit"
  }
}
```

#### `.env.example`

```env
# Application
APP_NAME="DRMS"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=drms_db
DB_USERNAME=your_username
DB_PASSWORD=your_password

# BlessedText SMS
BLESSEDTEXT_API_KEY=your_api_key
BLESSEDTEXT_SENDER_ID=DRMS

# Twilio SMS (Fallback)
TWILIO_SID=your_twilio_sid
TWILIO_TOKEN=your_twilio_token
TWILIO_FROM=+1234567890

# M-Pesa
MPESA_CONSUMER_KEY=your_consumer_key
MPESA_CONSUMER_SECRET=your_consumer_secret
MPESA_SHORTCODE=174379
MPESA_PASSKEY=your_passkey
MPESA_CALLBACK_URL=https://yourdomain.com/api/mpesa/callback

# Security
JWT_SECRET=your_jwt_secret_key
ENCRYPTION_KEY=your_encryption_key
```

#### `Procfile` (for Heroku)

```
web: php -S 0.0.0.0:$PORT -t public/
```

#### `railway.toml` (for Railway)

```toml
[build]
builder = "heroku/buildpacks:20"

[deploy]
startCommand = "php -S 0.0.0.0:$PORT -t public/"

[environment]
PHP_VERSION = "8.1"
```

### 3. **File Organization Strategy**

**Keep as PHP**: All your current files should remain PHP since you have server-side logic
**Images**: Host on the same server initially, later can move to CDN
**Structure**: Move files to match hosting best practices

## 🏗️ **Phase 2: Hosting Platform Setup**

### **Option 1: Railway (Recommended for Students)**

1. **Setup Steps:**

```bash
# Install Railway CLI
npm install -g @railway/cli

# Login to Railway
railway login

# Initialize project
railway init

# Deploy
railway up
```

2. **Benefits:**

- ✅ Automatic GitHub deployments
- ✅ Free MySQL database
- ✅ Environment variables management
- ✅ Custom domains
- ✅ CI/CD pipeline

### **Option 2: Heroku (Classic Choice)**

1. **Setup Steps:**

```bash
# Install Heroku CLI
# Create app
heroku create your-drms-app

# Add MySQL addon
heroku addons:create cleardb:ignite

# Set environment variables
heroku config:set APP_ENV=production
heroku config:set DB_HOST=...

# Deploy
git push heroku main
```

### **Option 3: GitHub Student Pack Options**

Since you have the GitHub Student Pack:

1. **DigitalOcean** ($100 credit)
2. **Azure for Students** (Free tier)
3. **AWS Educate** (Credits available)
4. **GitHub Codespaces** (Free hours)

## 🔄 **Phase 3: Git Version Control Setup**

### 1. **Initialize Repository**

```bash
cd c:\xampp\htdocs\project
git init
git add .
git commit -m "Initial commit: DRMS v1.0.0"
```

### 2. **Create GitHub Repository**

```bash
# Create repo on GitHub, then:
git remote add origin https://github.com/yourusername/drms.git
git branch -M main
git push -u origin main
```

### 3. **Branching Strategy**

```bash
# Development branch
git checkout -b develop

# Feature branches
git checkout -b feature/password-reset
git checkout -b feature/enhanced-analytics

# Release branches
git checkout -b release/v1.1.0
```

### 4. **CI/CD with GitHub Actions**

Create `.github/workflows/deploy.yml`:

```yaml
name: Deploy to Production

on:
  push:
    branches: [main]

jobs:
  deploy:
    runs-on: ubuntu-latest

    steps:
      - uses: actions/checkout@v3

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: "8.1"

      - name: Install dependencies
        run: composer install --no-dev --optimize-autoloader

      - name: Run tests
        run: composer test

      - name: Deploy to Railway
        uses: railwayapp/railway-deploy@v1
        with:
          service: ${{ secrets.RAILWAY_SERVICE }}
          token: ${{ secrets.RAILWAY_TOKEN }}
```

## 🎯 **Phase 4: Prepare for Password Reset Feature**

### 1. **Database Migration System**

Create `database/migrations/`:

```sql
-- 001_add_password_reset_tokens.sql
CREATE TABLE password_reset_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    used BOOLEAN DEFAULT FALSE,
    INDEX idx_email (email),
    INDEX idx_token (token)
);
```

### 2. **Feature Branch Workflow**

```bash
# After hosting, create feature branch
git checkout -b feature/password-reset

# Develop feature
# ... code password reset functionality

# Commit and push
git add .
git commit -m "Add password reset functionality"
git push origin feature/password-reset

# Create pull request on GitHub
# After review, merge to main
# Automatic deployment via CI/CD
```

## 🛡️ **Security Checklist for Production**

- [ ] Environment variables properly set
- [ ] Database credentials secured
- [ ] API keys in environment (not code)
- [ ] HTTPS enabled
- [ ] Input validation on all forms
- [ ] SQL injection protection
- [ ] CSRF tokens implemented
- [ ] File upload restrictions
- [ ] Error messages don't reveal sensitive info

## 📊 **Monitoring & Analytics Setup**

1. **Application Monitoring**

   - Error logging
   - Performance metrics
   - User analytics

2. **Database Monitoring**
   - Query performance
   - Connection pooling
   - Backup strategy

## 🎉 **Next Steps After Hosting**

1. **Deploy MVP** → Get basic version live
2. **Setup Monitoring** → Track usage and errors
3. **Feature Development** → Password reset, analytics, etc.
4. **User Feedback** → Iterate based on real usage
5. **Scaling** → Optimize as user base grows

## 🚀 **Recommended Deployment Order**

1. **Railway** (easiest for students)
2. **Setup CI/CD** (GitHub Actions)
3. **Domain Configuration** (custom domain)
4. **SSL/HTTPS** (automatic with Railway)
5. **Environment Variables** (production config)
6. **Database Migration** (production data)
7. **Testing** (verify all features work)
8. **Go Live!** 🎉

Would you like me to help you start with any specific phase?
