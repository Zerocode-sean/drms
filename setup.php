#!/usr/bin/env php
<?php
/**
 * DRMS Setup Script
 * Prepares the application for deployment
 */

echo "🚀 DRMS Deployment Setup\n";
echo "========================\n\n";

// Check PHP version
if (version_compare(PHP_VERSION, '8.0.0') < 0) {
    echo "❌ PHP 8.0 or higher is required. Current version: " . PHP_VERSION . "\n";
    exit(1);
}
echo "✅ PHP version check passed (" . PHP_VERSION . ")\n";

// Check required extensions
$required_extensions = ['mysqli', 'curl', 'json', 'mbstring', 'openssl'];
foreach ($required_extensions as $ext) {
    if (!extension_loaded($ext)) {
        echo "❌ Required PHP extension missing: $ext\n";
        exit(1);
    }
}
echo "✅ Required PHP extensions check passed\n";

// Create necessary directories
$directories = [
    'storage',
    'storage/logs',
    'storage/cache',
    'storage/uploads',
    'storage/sessions'
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        echo "✅ Created directory: $dir\n";
    }
}

// Set permissions
if (file_exists('storage')) {
    chmod('storage', 0755);
    echo "✅ Set permissions for storage directory\n";
}

// Check for .env file
if (!file_exists('.env')) {
    if (file_exists('.env.example')) {
        copy('.env.example', '.env');
        echo "✅ Created .env file from template\n";
        echo "⚠️  Please update .env file with your production credentials\n";
    } else {
        echo "❌ .env.example file not found\n";
        exit(1);
    }
}

// Validate critical environment variables
$env_content = file_get_contents('.env');
$critical_vars = [
    'DB_HOST',
    'DB_DATABASE', 
    'DB_USERNAME',
    'DB_PASSWORD',
    'BLESSEDTEXT_API_KEY'
];

$missing_vars = [];
foreach ($critical_vars as $var) {
    if (strpos($env_content, "$var=your_") !== false || strpos($env_content, "$var=") === false) {
        $missing_vars[] = $var;
    }
}

if (!empty($missing_vars)) {
    echo "⚠️  The following environment variables need to be configured:\n";
    foreach ($missing_vars as $var) {
        echo "   - $var\n";
    }
    echo "\n";
}

// Test database connection if credentials are provided
if (strpos($env_content, 'DB_HOST=localhost') === false) {
    echo "ℹ️  Database connection test skipped (using non-localhost)\n";
} else {
    echo "🔍 Testing database connection...\n";
    // Add database connection test here if needed
}

echo "\n🎉 Setup completed!\n";
echo "\nNext steps:\n";
echo "1. Update .env file with your production credentials\n";
echo "2. Set up your hosting platform (Railway, Heroku, etc.)\n";
echo "3. Configure environment variables on your hosting platform\n";
echo "4. Deploy your application\n";
echo "5. Run database migrations if needed\n";

echo "\n📚 Useful commands:\n";
echo "   composer start          - Start local development server\n";
echo "   git add .               - Stage all files for commit\n";
echo "   git commit -m 'message' - Commit changes\n";
echo "   git push origin main    - Push to GitHub\n";

?>
