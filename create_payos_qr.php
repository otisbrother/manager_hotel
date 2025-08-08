<?php
require_once 'config.php';

// Kiểm tra đăng nhập
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Chưa đăng nhập']);
    exit;
}

// Kiểm tra method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method không được phép']);
    exit;
}

// Lấy dữ liệu từ request
$orderCode = $_POST['orderCode'] ?? null;
$amount = $_POST['amount'] ?? null;
$bookingId = $_POST['bookingId'] ?? null;

if (!$orderCode || !$amount || !$bookingId) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Thiếu thông tin cần thiết']);
    exit;
}

try {
    // Kiểm tra booking có tồn tại và thuộc về user hiện tại không
    $stmt = $conn->prepare("SELECT * FROM roombook WHERE id = ? AND Email = ?");
    $stmt->execute([$bookingId, $_SESSION['user_email']]);
    $booking = $stmt->fetch();
    
    if (!$booking) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy đặt phòng']);
        exit;
    }
    
    // Tạo QR code qua PayOS API
    $qrData = createPayOSQRCode($orderCode, $amount, $booking);
    
    if ($qrData) {
        echo json_encode([
            'status' => 'success',
            'qrUrl' => $qrData['qrUrl'],
            'orderCode' => $orderCode
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Không thể tạo QR code']);
    }
    
} catch (Exception $e) {
    error_log('Create QR error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Có lỗi xảy ra khi tạo QR code']);
}

// Hàm tạo QR code qua PayOS API
function createPayOSQRCode($orderCode, $amount, $booking) {
    // PayOS API credentials (thay đổi theo tài khoản PayOS của bạn)
    $clientId = 'your_payos_client_id';
    $apiKey = 'your_payos_api_key';
    $checksum = 'your_payos_checksum';
    
    // Tạo URL callback
    $callbackUrl = 'https://yourdomain.com/webhook_payment.php';
    $returnUrl = 'https://yourdomain.com/payment_success.php?booking_id=' . $booking['id'];
    
    // Dữ liệu gửi đến PayOS
    $data = [
        'orderCode' => $orderCode,
        'amount' => $amount,
        'description' => 'Thanh toán đặt phòng #' . $booking['id'],
        'cancelUrl' => 'https://yourdomain.com/payment_cancel.php',
        'returnUrl' => $returnUrl,
        'signature' => $checksum,
        'items' => [
            [
                'name' => $booking['RoomType'],
                'quantity' => $booking['NoofRoom'],
                'price' => $amount
            ]
        ]
    ];
    
    // Gọi PayOS API
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api-merchant.payos.vn/v2/payment-requests');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'x-client-id: ' . $clientId,
        'x-api-key: ' . $apiKey
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $result = json_decode($response, true);
        
        if (isset($result['data']['qrCode'])) {
            // Lưu thông tin payment request vào database
            $stmt = $GLOBALS['conn']->prepare("INSERT INTO payment_requests (order_code, booking_id, amount, status, created_at) VALUES (?, ?, ?, 'pending', NOW())");
            $stmt->execute([$orderCode, $booking['id'], $amount]);
            
            return [
                'qrUrl' => $result['data']['qrCode'],
                'paymentUrl' => $result['data']['paymentUrl'] ?? '',
                'transactionId' => $result['data']['transactionId'] ?? ''
            ];
        }
    }
    
    error_log('PayOS API Error: ' . $response);
    return null;
}
?>
