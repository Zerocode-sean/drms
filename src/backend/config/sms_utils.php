<?php
/**
 * SMS Utilities - Common functions for SMS handling
 * Helps avoid function name conflicts between gateways
 */

/**
 * Generic phone number formatter for Kenya
 * @param string $phone Phone number in various formats
 * @return string Formatted phone number (+254...)
 */
function formatKenyanPhoneNumber($phone) {
    // Remove any non-digit characters except +
    $phone = preg_replace('/[^\d+]/', '', $phone);
    
    // Handle different formats
    if (str_starts_with($phone, '+254')) {
        // Already in correct format
        return $phone;
    } elseif (str_starts_with($phone, '254')) {
        // Add the + prefix
        return '+' . $phone;
    } elseif (str_starts_with($phone, '0') && strlen($phone) == 10) {
        // Convert 0712345678 to +254712345678
        return '+254' . substr($phone, 1);
    } elseif (strlen($phone) == 9) {
        // Assume it's a Kenyan number without the 0
        return '+254' . $phone;
    } else {
        // Return as is, might be international
        return str_starts_with($phone, '+') ? $phone : '+' . $phone;
    }
}

/**
 * Format phone number for BlessedText (254 format without +)
 * @param string $phone Phone number
 * @return string Formatted phone number (254...)
 */
function formatPhoneNumberForBlessedText($phone) {
    $formatted = formatKenyanPhoneNumber($phone);
    return ltrim($formatted, '+');
}

/**
 * Validate Kenyan phone number
 * @param string $phone Phone number
 * @return bool True if valid Kenyan number
 */
function isValidKenyanPhone($phone) {
    $formatted = formatKenyanPhoneNumber($phone);
    // Kenyan numbers should be +254 followed by 9 digits starting with 7
    return preg_match('/^\+254[7][0-9]{8}$/', $formatted);
}

/**
 * Get phone number display format
 * @param string $phone Phone number
 * @return string Display format (0712 345 678)
 */
function getPhoneDisplayFormat($phone) {
    $formatted = formatKenyanPhoneNumber($phone);
    if (str_starts_with($formatted, '+254')) {
        $local = '0' . substr($formatted, 4);
        return substr($local, 0, 4) . ' ' . substr($local, 4, 3) . ' ' . substr($local, 7);
    }
    return $phone;
}
?>
