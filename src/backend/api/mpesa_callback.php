<?php
// Log all incoming requests for debugging
file_put_contents(__DIR__ . '/mpesa_callback_log.txt', file_get_contents('php://input') . PHP_EOL, FILE_APPEND);

require_once '../config/db_config.php';

$data = json_decode(file_get_contents('php://input'), true);

// Parse the callback data
$callback = $data['Body']['stkCallback'] ?? null;
if ($callback) {
    $resultCode = $callback['ResultCode'];
    $resultDesc = $callback['ResultDesc'];
    $amount = 0;
    $mpesaReceipt = '';
    $phone = '';
    $service = '';
    if (isset($callback['CallbackMetadata']['Item'])) {
        foreach ($callback['CallbackMetadata']['Item'] as $item) {
            if ($item['Name'] === 'Amount') $amount = $item['Value'];
            if ($item['Name'] === 'MpesaReceiptNumber') $mpesaReceipt = $item['Value'];
            if ($item['Name'] === 'PhoneNumber') $phone = $item['Value'];
            if ($item['Name'] === 'AccountReference') $service = $item['Value'];
        }
    }
    // Sample DB update: insert payment record
    $stmt = $pdo->prepare('INSERT INTO payments (phone, amount, mpesa_receipt, service, status, result_desc, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
    $status = ($resultCode == 0) ? 'success' : 'failed';
    $stmt->execute([$phone, $amount, $mpesaReceipt, $service, $status, $resultDesc]);
}
// Respond to Safaricom
http_response_code(200);
echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Received successfully']); 