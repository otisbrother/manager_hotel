<?php
require_once 'config.php';

// Function tính toán giá phòng
function calculateRoomPrice($roomType) {
    $prices = [
        'Superior Room' => 1000,
        'Deluxe Room' => 1500,
        'Guest House' => 800,
        'Single Room' => 500
    ];
    return $prices[$roomType] ?? 0;
}

// Function tính toán phụ phí giường
function calculateBedPrice($bedType) {
    $prices = [
        'Single' => 10,
        'Double' => 20,
        'Triple' => 30,
        'Quad' => 40
    ];
    return $prices[$bedType] ?? 0;
}

// Function tính toán giá dịch vụ ăn uống
function calculateMealPrice($meal) {
    $prices = [
        'Room only' => 0,
        'Breakfast' => 200,
        'Half Board' => 400,
        'Full Board' => 600
    ];
    return $prices[$meal] ?? 0;
}

// Function tính tổng giá
function calculateTotalPrice($booking) {
    $roomPrice = calculateRoomPrice($booking['RoomType']);
    $bedPrice = calculateBedPrice($booking['Bed']);
    $mealPrice = calculateMealPrice($booking['Meal']);
    $days = $booking['nodays'];
    
    return ($roomPrice + $bedPrice + $mealPrice) * $days;
}

if (!is_logged_in()) {
    redirect('index.php');
}

$booking_id = $_GET['id'] ?? 0;
$user_email = $_SESSION['user_email'];

// Lấy thông tin đặt phòng
try {
    $stmt = $conn->prepare("SELECT * FROM roombook WHERE id = ? AND Email = ?");
    $stmt->execute([$booking_id, $user_email]);
    $booking = $stmt->fetch();
    
    if (!$booking) {
        redirect('my-bookings.php');
    }
} catch(PDOException $e) {
    redirect('my-bookings.php');
}

$user_name = $_SESSION['user_name'];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết đặt phòng - BlueBird Hotel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            font-weight: 700;
            color: white !important;
        }
        
        .sidebar {
            background: white;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            min-height: calc(100vh - 76px);
        }
        
        .sidebar .nav-link {
            color: #333;
            padding: 15px 20px;
            border-radius: 10px;
            margin: 5px 10px;
            transition: all 0.3s;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
        }
        
        .main-content {
            padding: 30px;
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 20px;
        }
        
        .btn-custom {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            border-radius: 10px;
            color: white;
            padding: 12px 25px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        .booking-status {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-confirmed {
            background-color: var(--success-color);
            color: white;
        }
        
        .status-pending {
            background-color: var(--warning-color);
            color: #333;
        }
        
        .status-cancelled {
            background-color: var(--danger-color);
            color: white;
        }
        
        .detail-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        
        .detail-item i {
            width: 30px;
            color: var(--primary-color);
            margin-right: 15px;
        }
        
        .detail-item .label {
            font-weight: 600;
            min-width: 120px;
        }
        
        .detail-item .value {
            color: #666;
        }
        
        .price-breakdown {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 15px;
            padding: 20px;
            margin-top: 20px;
        }
        
        .price-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 8px 0;
            border-bottom: 1px solid #dee2e6;
        }
        
        .price-item:last-child {
            border-bottom: none;
            font-weight: 600;
            font-size: 18px;
            color: var(--primary-color);
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <img src="./image/bluebirdlogo.png" alt="Logo" width="30" height="30" class="d-inline-block align-text-top me-2">
                BlueBird Hotel
            </a>
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle"></i> <?php echo $user_name; ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user"></i> Hồ sơ</a></li>
                        <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2">
                <div class="sidebar p-3">
                    <nav class="nav flex-column">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                        <a class="nav-link" href="booking.php">
                            <i class="fas fa-calendar-plus"></i> Đặt phòng
                        </a>
                        <a class="nav-link active" href="my-bookings.php">
                            <i class="fas fa-list"></i> Lịch sử đặt phòng
                        </a>
                        <a class="nav-link" href="room-gallery.php">
                            <i class="fas fa-images"></i> Gallery Phòng
                        </a>
                        <a class="nav-link" href="payment.php">
                            <i class="fas fa-credit-card"></i> Thanh toán
                        </a>
                        <a class="nav-link" href="profile.php">
                            <i class="fas fa-user"></i> Hồ sơ
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10">
                <div class="main-content">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="fas fa-info-circle"></i> Chi tiết đặt phòng</h2>
                        <a href="my-bookings.php" class="btn btn-custom">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                    
                    <div class="row">
                        <div class="col-lg-8">
                            <!-- Thông tin đặt phòng -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-bed"></i> Thông tin phòng</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="detail-item">
                                                <i class="fas fa-hotel"></i>
                                                <div>
                                                    <div class="label">Loại phòng:</div>
                                                    <div class="value"><?php echo $booking['RoomType']; ?></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="detail-item">
                                                <i class="fas fa-bed"></i>
                                                <div>
                                                    <div class="label">Loại giường:</div>
                                                    <div class="value"><?php echo $booking['Bed']; ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="detail-item">
                                                <i class="fas fa-calendar-check"></i>
                                                <div>
                                                    <div class="label">Ngày check-in:</div>
                                                    <div class="value"><?php echo date('d/m/Y', strtotime($booking['cin'])); ?></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="detail-item">
                                                <i class="fas fa-calendar-times"></i>
                                                <div>
                                                    <div class="label">Ngày check-out:</div>
                                                    <div class="value"><?php echo date('d/m/Y', strtotime($booking['cout'])); ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="detail-item">
                                                <i class="fas fa-clock"></i>
                                                <div>
                                                    <div class="label">Số ngày:</div>
                                                    <div class="value"><?php echo $booking['nodays']; ?> ngày</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="detail-item">
                                                <i class="fas fa-door-open"></i>
                                                <div>
                                                    <div class="label">Số phòng:</div>
                                                    <div class="value"><?php echo $booking['NoofRoom']; ?> phòng</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="detail-item">
                                        <i class="fas fa-utensils"></i>
                                        <div>
                                            <div class="label">Dịch vụ ăn uống:</div>
                                            <div class="value"><?php echo $booking['Meal']; ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-4">
                            <!-- Thông tin khách hàng -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-user"></i> Thông tin khách hàng</h5>
                                </div>
                                <div class="card-body">
                                    <div class="detail-item">
                                        <i class="fas fa-user"></i>
                                        <div>
                                            <div class="label">Họ tên:</div>
                                            <div class="value"><?php echo $booking['Name']; ?></div>
                                        </div>
                                    </div>
                                    
                                    <div class="detail-item">
                                        <i class="fas fa-envelope"></i>
                                        <div>
                                            <div class="label">Email:</div>
                                            <div class="value"><?php echo $booking['Email']; ?></div>
                                        </div>
                                    </div>
                                    
                                    <div class="detail-item">
                                        <i class="fas fa-phone"></i>
                                        <div>
                                            <div class="label">Số điện thoại:</div>
                                            <div class="value"><?php echo $booking['Phone']; ?></div>
                                        </div>
                                    </div>
                                    
                                    <div class="detail-item">
                                        <i class="fas fa-flag"></i>
                                        <div>
                                            <div class="label">Quốc gia:</div>
                                            <div class="value"><?php echo $booking['Country']; ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Trạng thái và giá -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-credit-card"></i> Trạng thái & Thanh toán</h5>
                                </div>
                                <div class="card-body">
                                    <div class="text-center mb-3">
                                        <span class="booking-status status-<?php echo strtolower($booking['stat'] ?? 'notconfirm'); ?>">
                                            <?php echo $booking['stat'] == 'Confirm' ? 'Confirmed' : 'Pending'; ?>
                                        </span>
                                    </div>
                                    
                                    <div class="price-breakdown">
                                        <h6 class="mb-3">Chi tiết giá:</h6>
                                        
                                        <?php
                                        $roomPrice = calculateRoomPrice($booking['RoomType']);
                                        $bedPrice = calculateBedPrice($booking['Bed']);
                                        $mealPrice = calculateMealPrice($booking['Meal']);
                                        $days = $booking['nodays'];
                                        $total = ($roomPrice + $bedPrice + $mealPrice) * $days;
                                        ?>
                                        
                                        <div class="price-item">
                                            <span>Giá phòng/ngày:</span>
                                            <span><?php echo number_format($roomPrice, 0, ',', '.'); ?> VNĐ</span>
                                        </div>
                                        
                                        <div class="price-item">
                                            <span>Phụ phí giường/ngày:</span>
                                            <span><?php echo number_format($bedPrice, 0, ',', '.'); ?> VNĐ</span>
                                        </div>
                                        
                                        <div class="price-item">
                                            <span>Dịch vụ ăn uống/ngày:</span>
                                            <span><?php echo number_format($mealPrice, 0, ',', '.'); ?> VNĐ</span>
                                        </div>
                                        
                                        <div class="price-item">
                                            <span>Số ngày:</span>
                                            <span><?php echo $days; ?> ngày</span>
                                        </div>
                                        
                                        <div class="price-item">
                                            <span>Tổng cộng:</span>
                                            <span><?php echo number_format($total, 0, ',', '.'); ?> VNĐ</span>
                                        </div>
                                    </div>
                                    
                                    <?php if (($booking['stat'] ?? 'NotConfirm') == 'NotConfirm'): ?>
                                        <div class="text-center mt-3">
                                            <a href="payment.php?booking_id=<?php echo $booking['id']; ?>" class="btn btn-custom">
                                                <i class="fas fa-credit-card"></i> Thanh toán ngay
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 