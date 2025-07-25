<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/mpesa_config.php';

$data = json_decode(file_get_contents('php://input'), true);
$checkout_request_id = $data['checkout_request_id'] ?? '';

if (!$checkout_request_id) {
    echo json_encode(['error' => 'Checkout request ID is required.']);
    exit;
}

// Check the status of the STK Push transaction
$result = checkStkPushStatus($checkout_request_id);

if ($result['success']) {
    $status = $result['status'] ?? 'pending';
    $response = [
        'success' => true,
        'status' => $status, // 'pending', 'completed', 'failed', 'cancelled'
        'checkout_request_id' => $checkout_request_id
    ];
    
    // Add additional data if payment is completed
    if ($status === 'completed') {
        $response['transaction_id'] = $result['transaction_id'] ?? null;
        $response['amount'] = $result['amount'] ?? null;
        $response['phone'] = $result['phone'] ?? null;
        $response['message'] = 'Payment completed successfully';
    } elseif ($status === 'failed') {
        $response['message'] = $result['error_message'] ?? 'Payment failed';
    } elseif ($status === 'cancelled') {
        $response['message'] = 'Payment was cancelled by user';
    }
    
    echo json_encode($response);
} else {
    echo json_encode([
        'success' => false,
        'error' => $result['error'] ?? 'Failed to check payment status',
        'status' => 'unknown'
    ]);
}
?>
