<?php
require_once 'config.php';

if (!is_logged_in()) {
    redirect('index.php');
}

$user_name = $_SESSION['user_name'];
$orderId = $_GET['orderId'] ?? '';
$resultCode = $_GET['resultCode'] ?? '';
$message = $_GET['message'] ?? '';

$success = false;
$error = '';

if ($resultCode == 0) {
    $success = true;
} else {
    $error = $message;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết quả thanh toán - BlueBird Hotel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .payment-result {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            padding: 40px;
            text-align: center;
            max-width: 500px;
            width: 100%;
        }
        
        .success-icon {
            width: 80px;
            height: 80px;
            background: #28a745;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 2rem;
        }
        
        .error-icon {
            width: 80px;
            height: 80px;
            background: #dc3545;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 2rem;
        }
        
        .btn-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            color: white;
            padding: 12px 25px;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        .order-details {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="payment-result">
            <?php if ($success): ?>
                <div class="success-icon">
                    <i class="fas fa-check"></i>
                </div>
                <h2 class="text-success mb-3">Thanh toán thành công!</h2>
                <p class="text-muted mb-4">Cảm ơn bạn đã sử dụng dịch vụ của BlueBird Hotel</p>
                
                <div class="order-details">
                    <h5><i class="fas fa-receipt"></i> Chi tiết đơn hàng</h5>
                    <p><strong>Mã đơn hàng:</strong> <?php echo $orderId; ?></p>
                    <p><strong>Trạng thái:</strong> <span class="badge bg-success">Đã thanh toán</span></p>
                </div>
                
                <div class="mt-4">
                    <a href="dashboard.php" class="btn btn-custom me-2">
                        <i class="fas fa-home"></i> Về Dashboard
                    </a>
                    <a href="my-bookings.php" class="btn btn-outline-secondary">
                        <i class="fas fa-list"></i> Xem đặt phòng
                    </a>
                </div>
                
            <?php else: ?>
                <div class="error-icon">
                    <i class="fas fa-times"></i>
                </div>
                <h2 class="text-danger mb-3">Thanh toán thất bại!</h2>
                <p class="text-muted mb-4">Đã xảy ra lỗi trong quá trình thanh toán</p>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <div class="mt-4">
                    <a href="my-bookings.php" class="btn btn-custom me-2">
                        <i class="fas fa-list"></i> Xem đặt phòng
                    </a>
                    <a href="dashboard.php" class="btn btn-outline-secondary">
                        <i class="fas fa-home"></i> Về Dashboard
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 