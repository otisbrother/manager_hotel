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
$booking_id = $_POST['booking_id'] ?? null;
$amount = $_POST['amount'] ?? null;
$payment_method = $_POST['payment_method'] ?? null;

if (!$booking_id || !$amount || !$payment_method) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Thiếu thông tin cần thiết']);
    exit;
}

try {
    // Kiểm tra booking có tồn tại và thuộc về user hiện tại không
    $stmt = $conn->prepare("SELECT * FROM roombook WHERE id = ? AND Email = ?");
    $stmt->execute([$booking_id, $_SESSION['user_email']]);
    $booking = $stmt->fetch();
    
    if (!$booking) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy đặt phòng']);
        exit;
    }
    
    // Kiểm tra trạng thái hiện tại
    if ($booking['stat'] === 'Paid') {
        echo json_encode(['status' => 'success', 'message' => 'Đặt phòng đã được thanh toán']);
        exit;
    }
    
    if ($booking['stat'] !== 'Confirm') {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Đặt phòng chưa được admin xác nhận']);
        exit;
    }
    
    // Cập nhật trạng thái thành "Pending Payment" (chờ admin xác nhận)
    $stmt = $conn->prepare("UPDATE roombook SET stat = 'Pending Payment' WHERE id = ?");
    $stmt->execute([$booking_id]);
    
    // Lưu thông tin yêu cầu xác nhận
    $stmt = $conn->prepare("INSERT INTO payment_confirmations (booking_id, user_email, amount, payment_method, status, created_at) VALUES (?, ?, ?, ?, 'pending', NOW())");
    $stmt->execute([$booking_id, $_SESSION['user_email'], $amount, $payment_method]);
    
    // Gửi thông báo cho admin (có thể là email hoặc notification)
    sendAdminNotification($booking, $amount, $payment_method);
    
    echo json_encode([
        'status' => 'success', 
        'message' => 'Yêu cầu xác nhận đã được gửi. Admin sẽ kiểm tra và xác nhận trong thời gian sớm nhất.'
    ]);
    
} catch (Exception $e) {
    error_log('Payment confirmation error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Có lỗi xảy ra khi xử lý yêu cầu']);
}

// Hàm gửi thông báo cho admin
function sendAdminNotification($booking, $amount, $payment_method) {
    $subject = 'Yêu cầu xác nhận thanh toán - BlueBird Hotel';
    $message = "
    <html>
    <body>
        <h2>Yêu cầu xác nhận thanh toán</h2>
        <p><strong>Mã đặt phòng:</strong> #{$booking['id']}</p>
        <p><strong>Khách hàng:</strong> {$booking['Name']}</p>
        <p><strong>Email:</strong> {$booking['Email']}</p>
        <p><strong>Số tiền:</strong> " . number_format($amount, 0, ',', '.') . " VNĐ</p>
        <p><strong>Phương thức:</strong> " . ucfirst($payment_method) . "</p>
        <p><strong>Thời gian:</strong> " . date('d/m/Y H:i:s') . "</p>
        <br>
        <p>Vui lòng kiểm tra tài khoản ngân hàng và xác nhận thanh toán trong trang quản trị.</p>
        <p><a href='http://yourdomain.com/admin/payment_confirmations.php'>Xem chi tiết</a></p>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: BlueBird Hotel <noreply@bluebirdhotel.com>\r\n";
    
    // Gửi email cho admin (thay đổi email admin ở đây)
    mail('admin@bluebirdhotel.com', $subject, $message, $headers);
}
?>
