<?php
require_once 'config.php';

// Webhook endpoint để nhận thông báo thanh toán từ PayOS
// URL: https://yourdomain.com/webhook_payment.php

// Log webhook data để debug
$input = file_get_contents('php://input');
error_log('PayOS Webhook: ' . $input);

// Kiểm tra method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

try {
    // Decode JSON data
    $data = json_decode($input, true);
    
    if (!$data) {
        http_response_code(400);
        exit('Invalid JSON data');
    }
    
    // Xác thực webhook signature (thêm nếu PayOS yêu cầu)
    if (!verifyWebhookSignature($data)) {
        http_response_code(401);
        exit('Unauthorized');
    }
    
    // Xử lý thông báo thanh toán
    $orderCode = $data['orderCode'] ?? '';
    $status = $data['status'] ?? '';
    $amount = $data['amount'] ?? 0;
    $transactionId = $data['transactionId'] ?? '';
    
    // Trích xuất booking ID từ orderCode (format: "BOOKING_12345")
    $bookingId = extractBookingIdFromOrderCode($orderCode);
    
    if ($status === 'PAID' && $bookingId) {
        // Cập nhật trạng thái đặt phòng thành công
        $stmt = $conn->prepare("UPDATE roombook SET stat = 'Paid' WHERE id = ?");
        $stmt->execute([$bookingId]);
        
        // Lấy thông tin đặt phòng
        $stmt = $conn->prepare("SELECT * FROM roombook WHERE id = ?");
        $stmt->execute([$bookingId]);
        $booking = $stmt->fetch();
        
        if ($booking) {
            // Tính toán giá
            $roomPrice = calculateRoomPrice($booking['RoomType']);
            $bedPrice = calculateBedPrice($booking['Bed']);
            $mealPrice = calculateMealPrice($booking['Meal']);
            $totalPrice = calculateTotalPrice($booking);
            
            // Lưu vào bảng payment
            $stmt = $conn->prepare("INSERT INTO payment (Name, Email, RoomType, Bed, NoofRoom, cin, cout, noofdays, roomtotal, bedtotal, meal, mealtotal, finaltotal, payment_method, transaction_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([
                $booking['Name'],
                $booking['Email'],
                $booking['RoomType'],
                $booking['Bed'],
                $booking['NoofRoom'],
                $booking['cin'],
                $booking['cout'],
                $booking['nodays'],
                $roomPrice,
                $bedPrice,
                $booking['Meal'],
                $mealPrice,
                $totalPrice,
                'vietqr',
                $transactionId
            ]);
            
            // Cập nhật payment_confirmations nếu có
            $stmt = $conn->prepare("UPDATE payment_confirmations SET status = 'confirmed', admin_notes = 'Tự động xác nhận qua webhook' WHERE booking_id = ? AND status = 'pending'");
            $stmt->execute([$bookingId]);
            
            // Gửi email thông báo cho khách hàng
            sendPaymentSuccessEmail($booking);
            
            // Gửi thông báo real-time (nếu có)
            sendRealTimeNotification($bookingId, 'success');
            
            error_log("Payment confirmed via webhook for booking ID: $bookingId");
        }
    } elseif ($status === 'FAILED' && $bookingId) {
        // Cập nhật trạng thái thất bại
        $stmt = $conn->prepare("UPDATE roombook SET stat = 'Confirm' WHERE id = ?");
        $stmt->execute([$bookingId]);
        
        // Gửi thông báo thất bại
        sendRealTimeNotification($bookingId, 'failed');
        
        error_log("Payment failed via webhook for booking ID: $bookingId");
    }
    
    // Trả về success response
    http_response_code(200);
    echo json_encode(['status' => 'success']);
    
} catch (Exception $e) {
    error_log('Webhook Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

// Hàm xác thực webhook signature
function verifyWebhookSignature($data) {
    // Implement signature verification logic here
    // Ví dụ với PayOS
    $secretKey = 'your_payos_secret_key'; // Thay bằng secret key của PayOS
    $signature = $_SERVER['HTTP_X_SIGNATURE'] ?? '';
    
    // Tạo signature để so sánh
    $payload = json_encode($data);
    $expectedSignature = hash_hmac('sha256', $payload, $secretKey);
    
    return hash_equals($expectedSignature, $signature);
}

// Hàm trích xuất booking ID từ orderCode
function extractBookingIdFromOrderCode($orderCode) {
    // Tìm pattern "BOOKING_{ID}"
    if (preg_match('/BOOKING_(\d+)/', $orderCode, $matches)) {
        return $matches[1];
    }
    return null;
}

// Các hàm tính toán giá
function calculateRoomPrice($roomType) {
    $prices = [
        'Superior Room' => 1000,
        'Deluxe Room' => 1500,
        'Guest House' => 800,
        'Single Room' => 500
    ];
    return $prices[$roomType] ?? 0;
}

function calculateBedPrice($bedType) {
    $prices = [
        'Single' => 10,
        'Double' => 20,
        'Triple' => 30,
        'Quad' => 40
    ];
    return $prices[$bedType] ?? 0;
}

function calculateMealPrice($meal) {
    $prices = [
        'Room only' => 0,
        'Breakfast' => 200,
        'Half Board' => 400,
        'Full Board' => 600
    ];
    return $prices[$meal] ?? 0;
}

function calculateTotalPrice($booking) {
    $roomPrice = calculateRoomPrice($booking['RoomType']);
    $bedPrice = calculateBedPrice($booking['Bed']);
    $mealPrice = calculateMealPrice($booking['Meal']);
    $days = $booking['nodays'];
    
    return ($roomPrice + $bedPrice + $mealPrice) * $days;
}

function sendPaymentSuccessEmail($booking) {
    $to = $booking['Email'];
    $subject = 'Thanh toán thành công - BlueBird Hotel';
    $message = "
    <html>
    <body>
        <h2>Thanh toán thành công!</h2>
        <p>Xin chào {$booking['Name']},</p>
        <p>Chúng tôi đã nhận được thanh toán của bạn cho đặt phòng #{$booking['id']}.</p>
        <p><strong>Thông tin đặt phòng:</strong></p>
        <ul>
            <li>Loại phòng: {$booking['RoomType']}</li>
            <li>Check-in: " . date('d/m/Y', strtotime($booking['cin'])) . "</li>
            <li>Check-out: " . date('d/m/Y', strtotime($booking['cout'])) . "</li>
            <li>Số ngày: {$booking['nodays']} ngày</li>
        </ul>
        <p>Cảm ơn bạn đã chọn BlueBird Hotel!</p>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: BlueBird Hotel <noreply@bluebirdhotel.com>\r\n";
    
    mail($to, $subject, $message, $headers);
}

function sendRealTimeNotification($bookingId, $status) {
    // Có thể implement WebSocket hoặc Server-Sent Events ở đây
    // Để gửi thông báo real-time cho user
    error_log("Real-time notification: Booking $bookingId - Status: $status");
}
?>
