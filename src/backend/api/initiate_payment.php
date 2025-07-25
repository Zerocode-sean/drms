<?php
header('Content-Type: application/json');

require_once '../config/mpesa_config.php';

$data = json_decode(file_get_contents('php://input'), true);
$amount = $data['amount'] ?? '';
$phone = $data['phone'] ?? '';
$service = $data['service'] ?? '';

if (!$amount || !$phone || !$service) {
    echo json_encode(['error' => 'All fields are required.']);
    exit;
}

// Check if M-Pesa credentials are configured
if (MPESA_CONSUMER_KEY === 'your_consumer_key_here' || MPESA_CONSUMER_SECRET === 'your_consumer_secret_here') {
    echo json_encode(['error' => 'M-Pesa credentials not configured. Please update your .env file.']);
    exit;
}

// Validate and format phone number
$formattedPhone = validateMpesaPhone($phone);
if (!$formattedPhone) {
    echo json_encode(['error' => 'Invalid phone number format. Use format: 0712345678 or 254712345678']);
    exit;
}

// Validate amount
if (!is_numeric($amount) || $amount <= 0) {
    echo json_encode(['error' => 'Invalid amount. Must be a positive number.']);
    exit;
}

// Initiate STK Push
$result = initiateStkPush($formattedPhone, $amount, $service, 'DRMS - ' . $service);

if ($result['success']) {
    echo json_encode([
        'success' => true,
        'message' => 'STK Push initiated. Check your phone to complete the payment.',
        'checkout_request_id' => $result['checkout_request_id'] ?? null
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => $result['error'],
        'details' => $result['details'] ?? null
    ]);
}
?> 