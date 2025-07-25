# Free Database Options for Render Deployment

## 🎯 Recommended: PlanetScale (Free MySQL)

### Why PlanetScale?

✅ **10GB free storage**  
✅ **1 billion row reads/month**  
✅ **10 million row writes/month**  
✅ **Perfect for DRMS application**  
✅ **Easy branching and schema changes**

### Setup Steps:

1. Go to https://planetscale.com
2. Sign up with GitHub
3. Create database: `drms-production`
4. Get connection string from dashboard
5. Import your schema

### Connection String Format:

```
mysql://username:password@host:3306/database_name?sslaccept=strict
```

## 🎯 Alternative: Aiven (Free MySQL)

### Setup Steps:

1. Go to https://aiven.io
2. Create free account
3. Create MySQL service (free tier: 1 month, then $7/month)
4. Download SSL certificates
5. Import your schema

## 🎯 Budget Option: Railway MySQL Only

### Use Railway just for database:

1. Create Railway account
2. Add MySQL addon only (no web service)
3. Get connection details
4. Use with Render web service

## Database Schema Import

### Export from XAMPP:

```bash
# Export your local database
mysqldump -u root -p drms_db > drms_schema.sql
```

### Import to Cloud Database:

```bash
# For PlanetScale (using their CLI)
pscale shell drms-production main < drms_schema.sql

# For other services (using standard MySQL client)
mysql -h your-host -u username -p database_name < drms_schema.sql
```

## Environment Variables for Render

Once you have your database, add these to Render:

```
DATABASE_HOST=your-mysql-host
DATABASE_NAME=drms_db (or your database name)
DATABASE_USER=your-username
DATABASE_PASSWORD=your-password
DATABASE_PORT=3306
```

## Testing Connection

Add this test file to verify database connection:

**test_db_connection.php**:

```php
<?php
require_once 'src/backend/config/env_loader.php';

$host = $_ENV['DATABASE_HOST'];
$dbname = $_ENV['DATABASE_NAME'];
$username = $_ENV['DATABASE_USER'];
$password = $_ENV['DATABASE_PASSWORD'];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    echo "✅ Database connection successful!";
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage();
}
?>
```

## 🚀 Ready for Deployment!

Your database options are set up. Choose PlanetScale for the best free experience, then deploy to Render!
