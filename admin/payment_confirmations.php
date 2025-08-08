<?php
require_once '../config.php';
require_once 'config_admin.php';

// Kiểm tra đăng nhập admin
if (!is_admin_logged_in()) {
    redirect('admin.php');
}

$message = '';

// Xử lý xác nhận/từ chối thanh toán
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $confirmation_id = $_POST['confirmation_id'] ?? null;
    $action = $_POST['action'] ?? null; // 'confirm' hoặc 'reject'
    $admin_notes = $_POST['admin_notes'] ?? '';
    
    if ($confirmation_id && $action) {
        try {
            // Lấy thông tin yêu cầu xác nhận
            $stmt = $conn->prepare("SELECT * FROM payment_confirmations WHERE id = ?");
            $stmt->execute([$confirmation_id]);
            $confirmation = $stmt->fetch();
            
            if ($confirmation) {
                if ($action === 'confirm') {
                    // Xác nhận thanh toán
                    $stmt = $conn->prepare("UPDATE payment_confirmations SET status = 'confirmed', admin_notes = ? WHERE id = ?");
                    $stmt->execute([$admin_notes, $confirmation_id]);
                    
                    // Cập nhật trạng thái đặt phòng
                    $stmt = $conn->prepare("UPDATE roombook SET stat = 'Paid' WHERE id = ?");
                    $stmt->execute([$confirmation['booking_id']]);
                    
                    // Lấy thông tin đặt phòng để lưu vào bảng payment
                    $stmt = $conn->prepare("SELECT * FROM roombook WHERE id = ?");
                    $stmt->execute([$confirmation['booking_id']]);
                    $booking = $stmt->fetch();
                    
                    if ($booking) {
                        // Tính toán giá
                        $roomPrice = calculateRoomPrice($booking['RoomType']);
                        $bedPrice = calculateBedPrice($booking['Bed']);
                        $mealPrice = calculateMealPrice($booking['Meal']);
                        $totalPrice = calculateTotalPrice($booking);
                        
                                             // Lưu vào bảng payment với đầy đủ thông tin
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
                         $confirmation['payment_method'],
                         'CONF_' . $confirmation_id
                     ]);
                        
                        // Gửi email thông báo cho khách hàng
                        sendPaymentSuccessEmail($booking);
                    }
                    
                    $message = 'success:Thanh toán đã được xác nhận thành công!';
                    
                } elseif ($action === 'reject') {
                    // Từ chối thanh toán
                    $stmt = $conn->prepare("UPDATE payment_confirmations SET status = 'rejected', admin_notes = ? WHERE id = ?");
                    $stmt->execute([$admin_notes, $confirmation_id]);
                    
                    // Cập nhật trạng thái đặt phòng về Confirm
                    $stmt = $conn->prepare("UPDATE roombook SET stat = 'Confirm' WHERE id = ?");
                    $stmt->execute([$confirmation['booking_id']]);
                    
                    $message = 'success:Thanh toán đã bị từ chối!';
                }
            }
        } catch (Exception $e) {
            $message = 'error:Có lỗi xảy ra: ' . $e->getMessage();
        }
    }
}

// Lấy danh sách yêu cầu xác nhận
try {
    $stmt = $conn->prepare("
        SELECT pc.*, rb.Name, rb.Email, rb.RoomType, rb.cin, rb.cout 
        FROM payment_confirmations pc 
        JOIN roombook rb ON pc.booking_id = rb.id 
        ORDER BY pc.created_at DESC
    ");
    $stmt->execute();
    $confirmations = $stmt->fetchAll();
} catch (Exception $e) {
    $confirmations = [];
    $message = 'error:Có lỗi xảy ra khi tải dữ liệu: ' . $e->getMessage();
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
        <p>Chúng tôi đã xác nhận thanh toán của bạn cho đặt phòng #{$booking['id']}.</p>
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

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác nhận thanh toán - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="css/admin.css" rel="stylesheet">
</head>
<body>
    <?php include 'menu.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-check-circle"></i> Xác nhận thanh toán
                    </h1>
                </div>
                
                <?php if ($message): ?>
                    <?php 
                    $messageParts = explode(':', $message, 2);
                    $messageType = $messageParts[0];
                    $messageText = $messageParts[1] ?? $message;
                    ?>
                    <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                        <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i> 
                        <?php echo $messageText; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Khách hàng</th>
                                <th>Đặt phòng</th>
                                <th>Số tiền</th>
                                <th>Phương thức</th>
                                <th>Trạng thái</th>
                                <th>Thời gian</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($confirmations)): ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted">
                                        <i class="fas fa-inbox"></i> Không có yêu cầu xác nhận nào
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($confirmations as $conf): ?>
                                    <tr>
                                        <td>#<?php echo $conf['id']; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($conf['Name']); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($conf['user_email']); ?></small>
                                        </td>
                                        <td>
                                            <strong>#<?php echo $conf['booking_id']; ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($conf['RoomType']); ?></small><br>
                                            <small class="text-muted">
                                                <?php echo date('d/m/Y', strtotime($conf['cin'])); ?> - 
                                                <?php echo date('d/m/Y', strtotime($conf['cout'])); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <strong class="text-primary">
                                                <?php echo number_format($conf['amount'], 0, ',', '.'); ?> VNĐ
                                            </strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?php echo ucfirst($conf['payment_method']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($conf['status'] === 'pending'): ?>
                                                <span class="badge bg-warning">Chờ xác nhận</span>
                                            <?php elseif ($conf['status'] === 'confirmed'): ?>
                                                <span class="badge bg-success">Đã xác nhận</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Đã từ chối</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small><?php echo date('d/m/Y H:i', strtotime($conf['created_at'])); ?></small>
                                        </td>
                                        <td>
                                            <?php if ($conf['status'] === 'pending'): ?>
                                                <button type="button" class="btn btn-success btn-sm" 
                                                        onclick="showConfirmModal(<?php echo $conf['id']; ?>, 'confirm')">
                                                    <i class="fas fa-check"></i> Xác nhận
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm" 
                                                        onclick="showConfirmModal(<?php echo $conf['id']; ?>, 'reject')">
                                                    <i class="fas fa-times"></i> Từ chối
                                                </button>
                                            <?php else: ?>
                                                <small class="text-muted">
                                                    <?php echo $conf['admin_notes'] ? 'Có ghi chú' : 'Không có ghi chú'; ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Modal xác nhận/từ chối -->
    <div class="modal fade" id="confirmModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalTitle">Xác nhận thanh toán</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="confirmation_id" id="confirmationId">
                        <input type="hidden" name="action" id="actionType">
                        
                        <div class="mb-3">
                            <label for="admin_notes" class="form-label">Ghi chú (tùy chọn)</label>
                            <textarea class="form-control" id="admin_notes" name="admin_notes" rows="3" 
                                      placeholder="Nhập ghi chú nếu cần..."></textarea>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Lưu ý:</strong> 
                            <span id="actionDescription"></span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn" id="confirmButton">Xác nhận</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showConfirmModal(confirmationId, action) {
            document.getElementById('confirmationId').value = confirmationId;
            document.getElementById('actionType').value = action;
            
            const modal = document.getElementById('confirmModal');
            const title = document.getElementById('confirmModalTitle');
            const description = document.getElementById('actionDescription');
            const button = document.getElementById('confirmButton');
            
            if (action === 'confirm') {
                title.textContent = 'Xác nhận thanh toán';
                description.textContent = 'Bạn sẽ xác nhận rằng khách hàng đã thanh toán thành công. Đặt phòng sẽ được chuyển sang trạng thái "Đã thanh toán".';
                button.className = 'btn btn-success';
                button.textContent = 'Xác nhận thanh toán';
            } else {
                title.textContent = 'Từ chối thanh toán';
                description.textContent = 'Bạn sẽ từ chối yêu cầu thanh toán này. Đặt phòng sẽ quay về trạng thái "Đã xác nhận" để khách hàng có thể thử lại.';
                button.className = 'btn btn-danger';
                button.textContent = 'Từ chối thanh toán';
            }
            
            new bootstrap.Modal(modal).show();
        }
    </script>
</body>
</html>
