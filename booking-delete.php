<?php
require_once 'config.php';

// Kiểm tra đăng nhập
if (!is_logged_in()) {
    redirect('index.php');
}

$user_id = $_SESSION['user_id'];
$user_email = $_SESSION['user_email'];

// Kiểm tra method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('dashboard.php');
}

// Kiểm tra có ID được gửi không
if (!isset($_POST['booking_id']) || empty($_POST['booking_id'])) {
    $_SESSION['error'] = 'ID đặt phòng không hợp lệ!';
    redirect('dashboard.php');
}

$booking_id = $_POST['booking_id'];

try {
    // Kiểm tra xem đặt phòng có thuộc về user hiện tại không
    $stmt = $conn->prepare("SELECT * FROM roombook WHERE id = ? AND Email = ?");
    $stmt->execute([$booking_id, $user_email]);
    $booking = $stmt->fetch();

    if (!$booking) {
        $_SESSION['error'] = 'Không tìm thấy đặt phòng hoặc bạn không có quyền xóa!';
        redirect('dashboard.php');
    }

    // Kiểm tra trạng thái - chỉ cho phép xóa đặt phòng chưa xác nhận
    if ($booking['stat'] === 'Confirm') {
        $_SESSION['error'] = 'Không thể xóa đặt phòng đã được xác nhận!';
        redirect('dashboard.php');
    }

    // Xóa đặt phòng
    $delete_stmt = $conn->prepare("DELETE FROM roombook WHERE id = ? AND Email = ?");
    $result = $delete_stmt->execute([$booking_id, $user_email]);

    if ($result) {
        $_SESSION['success'] = 'Đã xóa đặt phòng thành công!';
    } else {
        $_SESSION['error'] = 'Có lỗi xảy ra khi xóa đặt phòng!';
    }

} catch(PDOException $e) {
    $_SESSION['error'] = 'Có lỗi xảy ra: ' . $e->getMessage();
}

redirect('dashboard.php');
?>
