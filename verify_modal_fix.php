#!/usr/bin/env php
<?php
/**
 * Modal Fix Verification Script
 * This script checks if the notification modal fix has been applied correctly
 */

echo "🔧 DRMS Notification Modal Fix Verification\n";
echo "==========================================\n\n";

$driverFile = 'c:/xampp/htdocs/project/src/frontend/assets/driver.php';

if (!file_exists($driverFile)) {
    echo "❌ ERROR: Driver file not found at: $driverFile\n";
    exit(1);
}

$content = file_get_contents($driverFile);

// Check 1: Modal HTML exists
if (strpos($content, 'id="notificationModal"') !== false) {
    echo "✅ Modal HTML found in driver.php\n";
} else {
    echo "❌ Modal HTML NOT found in driver.php\n";
}

// Check 2: Modal is NOT inside script tags
$scriptPattern = '/<script[^>]*>.*?id="notificationModal".*?<\/script>/s';
if (preg_match($scriptPattern, $content)) {
    echo "❌ ERROR: Modal is still inside script tags!\n";
} else {
    echo "✅ Modal is correctly placed outside script tags\n";
}

// Check 3: Modal is before closing body tag
$bodyClosePos = strrpos($content, '</body>');
$modalPos = strpos($content, 'id="notificationModal"');

if ($modalPos !== false && $bodyClosePos !== false && $modalPos < $bodyClosePos) {
    echo "✅ Modal is correctly placed before closing </body> tag\n";
} else {
    echo "❌ Modal positioning may have issues\n";
}

// Check 4: Modal CSS styles exist
if (strpos($content, '.modal {') !== false) {
    echo "✅ Modal CSS styles found\n";
} else {
    echo "⚠️  Modal CSS styles not found (they may be in external file)\n";
}

// Check 5: JavaScript functions exist
$jsFile = 'c:/xampp/htdocs/project/src/frontend/js/driver.js';
if (file_exists($jsFile)) {
    $jsContent = file_get_contents($jsFile);
    
    if (strpos($jsContent, 'openNotificationModal') !== false) {
        echo "✅ openNotificationModal function found in driver.js\n";
    } else {
        echo "❌ openNotificationModal function NOT found in driver.js\n";
    }
    
    if (strpos($jsContent, 'closeNotificationModal') !== false) {
        echo "✅ closeNotificationModal function found in driver.js\n";
    } else {
        echo "❌ closeNotificationModal function NOT found in driver.js\n";
    }
} else {
    echo "⚠️  driver.js file not found\n";
}

echo "\n🔍 ANALYSIS RESULTS:\n";
echo "===================\n";

// Count total checks
$totalChecks = 0;
$passedChecks = 0;

// Re-run checks and count
if (strpos($content, 'id="notificationModal"') !== false) $passedChecks++;
$totalChecks++;

if (!preg_match($scriptPattern, $content)) $passedChecks++;
$totalChecks++;

if ($modalPos !== false && $bodyClosePos !== false && $modalPos < $bodyClosePos) $passedChecks++;
$totalChecks++;

if (strpos($content, '.modal {') !== false) $passedChecks++;
$totalChecks++;

if (file_exists($jsFile)) {
    $jsContent = file_get_contents($jsFile);
    if (strpos($jsContent, 'openNotificationModal') !== false) $passedChecks++;
    $totalChecks++;
    if (strpos($jsContent, 'closeNotificationModal') !== false) $passedChecks++;
    $totalChecks++;
}

echo "Passed: $passedChecks/$totalChecks checks\n";

if ($passedChecks === $totalChecks) {
    echo "🎉 ALL CHECKS PASSED! The modal should now work correctly.\n";
    echo "\n📋 NEXT STEPS:\n";
    echo "1. Open http://localhost/project/src/frontend/assets/driver.php\n";
    echo "2. Click any 'Notify' button in the tasks table\n";
    echo "3. Verify the modal opens without console errors\n";
    echo "4. Test template selection and notification sending\n";
} else {
    echo "⚠️  Some checks failed. Please review the issues above.\n";
}

echo "\n";
?>
