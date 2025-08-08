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
$bookingId = $_POST['bookingId'] ?? null;
$amount = $_POST['amount'] ?? null;
$paymentMethod = $_POST['paymentMethod'] ?? null;

if (!$orderCode || !$bookingId) {
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
    
    // Kiểm tra trạng thái thanh toán
    $paymentStatus = checkPaymentStatus($orderCode, $bookingId);
    
    if ($paymentStatus === 'paid') {
        echo json_encode(['status' => 'success', 'message' => 'Thanh toán thành công!']);
    } elseif ($paymentStatus === 'failed') {
        echo json_encode(['status' => 'failed', 'message' => 'Thanh toán thất bại']);
    } else {
        echo json_encode(['status' => 'pending', 'message' => 'Đang chờ thanh toán...']);
    }
    
} catch (Exception $e) {
    error_log('Check payment status error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Có lỗi xảy ra khi kiểm tra trạng thái']);
}

// Hàm kiểm tra trạng thái thanh toán
function checkPaymentStatus($orderCode, $bookingId) {
    // Kiểm tra trong bảng payment_requests
    $stmt = $GLOBALS['conn']->prepare("SELECT status FROM payment_requests WHERE order_code = ? AND booking_id = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$orderCode, $bookingId]);
    $paymentRequest = $stmt->fetch();
    
    if ($paymentRequest) {
        return $paymentRequest['status'];
    }
    
    // Kiểm tra trong bảng roombook
    $stmt = $GLOBALS['conn']->prepare("SELECT stat FROM roombook WHERE id = ?");
    $stmt->execute([$bookingId]);
    $booking = $stmt->fetch();
    
    if ($booking && $booking['stat'] === 'Paid') {
        return 'paid';
    }
    
    return 'pending';
}
?>
