# Render Deployment Guide for DRMS

## Why Render?

✅ **More reliable** than Railway  
✅ **Simpler configuration** - no complex TOML files  
✅ **Better PHP support** out of the box  
✅ **Free tier available** with good limits  
✅ **Automatic HTTPS** and CDN  
✅ **Easy database integration**

## Step-by-Step Render Deployment

### 1. Create Render Account

1. Go to https://render.com
2. Sign up with GitHub (recommended)
3. Connect your GitHub account

### 2. Deploy from GitHub

1. Click **"New +"** → **"Web Service"**
2. Connect your GitHub repository: `https://github.com/Zerocode-sean/drms`
3. Use these settings:
   - **Name**: `drms-app` (or your preferred name)
   - **Runtime**: `PHP`
   - **Build Command**: `composer install --no-dev --optimize-autoloader`
   - **Start Command**: `php -S 0.0.0.0:$PORT index.php`
   - **Plan**: `Free` (for testing)

### 3. Set Environment Variables

In the Render dashboard, go to your service → **Environment** tab and add:

```
DATABASE_HOST=your_mysql_host
DATABASE_NAME=drms_db
DATABASE_USER=your_username
DATABASE_PASSWORD=your_password
BLESSEDTEXT_USERNAME=your_blessedtext_username
BLESSEDTEXT_PASSWORD=your_blessedtext_password
MPESA_CONSUMER_KEY=your_mpesa_key
MPESA_CONSUMER_SECRET=your_mpesa_secret
MPESA_PASSKEY=your_passkey
MPESA_SHORTCODE=your_shortcode
```

### 4. Database Options

#### Option A: External MySQL (Recommended for Free Tier)

- Use **PlanetScale** (free MySQL)
- Use **Aiven** (free MySQL)
- Use **Railway MySQL** (just for database)

#### Option B: Render PostgreSQL (Paid)

- Add PostgreSQL service in Render
- Update database connection in your code

### 5. Deploy

1. Click **"Create Web Service"**
2. Render will automatically build and deploy
3. You'll get a URL like: `https://drms-app.onrender.com`

### 6. Post-Deployment

1. Test the health check: `https://your-app.onrender.com/health`
2. Import your database schema
3. Test SMS and payment functionality
4. Set up custom domain (optional)

## Advantages of Render over Railway

- ✅ **No complex configuration files** needed
- ✅ **Better error messages** and debugging
- ✅ **More stable deployments**
- ✅ **Automatic deployments** on git push
- ✅ **Built-in monitoring** and logs
- ✅ **Easy environment variable management**

## Quick Database Setup with PlanetScale (Free)

1. Go to https://planetscale.com
2. Create free account
3. Create new database: `drms-db`
4. Get connection string
5. Import your schema using their web console or CLI

## Ready to Deploy!

Your application is now configured for Render. The deployment process is much simpler and more reliable than Railway.
