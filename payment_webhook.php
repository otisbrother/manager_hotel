<?php
require_once 'config.php';

// Webhook endpoint để nhận thông báo thanh toán từ ngân hàng
// URL: https://yourdomain.com/payment_webhook.php

// Kiểm tra method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

// Nhận dữ liệu từ webhook
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Log webhook data để debug
error_log('Payment Webhook: ' . $input);

try {
    // Xác thực webhook (thêm signature verification nếu cần)
    if (!verifyWebhookSignature($data)) {
        http_response_code(401);
        exit('Unauthorized');
    }
    
    // Xử lý thông báo thanh toán
    $transactionId = $data['transaction_id'] ?? '';
    $amount = $data['amount'] ?? 0;
    $status = $data['status'] ?? '';
    $bookingId = extractBookingIdFromContent($data['content'] ?? '');
    
    if ($status === 'success' && $bookingId) {
        // Cập nhật trạng thái đặt phòng
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
            $stmt = $conn->prepare("INSERT INTO payment (Name, Email, RoomType, Bed, NoofRoom, cin, cout, noofdays, roomtotal, bedtotal, meal, mealtotal, finaltotal, payment_method, transaction_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
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
                'bank_transfer',
                $transactionId
            ]);
            
            // Gửi email thông báo cho khách hàng
            sendPaymentSuccessEmail($booking);
            
            error_log("Payment confirmed for booking ID: $bookingId");
        }
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
    // Ví dụ với VietQR hoặc ngân hàng cụ thể
    return true; // Tạm thời return true, cần implement theo yêu cầu của ngân hàng
}

// Hàm trích xuất booking ID từ nội dung chuyển khoản
function extractBookingIdFromContent($content) {
    // Tìm pattern "Thanh toan booking {ID}"
    if (preg_match('/Thanh toan booking (\d+)/', $content, $matches)) {
        return $matches[1];
    }
    return null;
}

// Hàm tính toán giá phòng
function calculateRoomPrice($roomType) {
    $prices = [
        'Superior Room' => 1000,
        'Deluxe Room' => 1500,
        'Guest House' => 800,
        'Single Room' => 500
    ];
    return $prices[$roomType] ?? 0;
}

// Hàm tính toán phụ phí giường
function calculateBedPrice($bedType) {
    $prices = [
        'Single' => 10,
        'Double' => 20,
        'Triple' => 30,
        'Quad' => 40
    ];
    return $prices[$bedType] ?? 0;
}

// Hàm tính toán giá dịch vụ ăn uống
function calculateMealPrice($meal) {
    $prices = [
        'Room only' => 0,
        'Breakfast' => 200,
        'Half Board' => 400,
        'Full Board' => 600
    ];
    return $prices[$meal] ?? 0;
}

// Hàm tính tổng giá
function calculateTotalPrice($booking) {
    $roomPrice = calculateRoomPrice($booking['RoomType']);
    $bedPrice = calculateBedPrice($booking['Bed']);
    $mealPrice = calculateMealPrice($booking['Meal']);
    $days = $booking['nodays'];
    
    return ($roomPrice + $bedPrice + $mealPrice) * $days;
}

// Hàm gửi email thông báo
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
?>

