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

$user_email = $_SESSION['user_email'];
$user_name = $_SESSION['user_name'];

try {
    $stmt = $conn->prepare("SELECT * FROM roombook WHERE Email = ? ORDER BY id DESC");
    $stmt->execute([$user_email]);
    $bookings = $stmt->fetchAll();
} catch(PDOException $e) {
    $bookings = [];
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch sử đặt phòng - BlueBird Hotel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 20px;
        }
        
        .btn-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            background-color: #28a745;
            color: white;
        }
        
        .status-pending {
            background-color: #ffc107;
            color: #333;
        }
        
        .status-cancelled {
            background-color: #dc3545;
            color: white;
        }
        
        .booking-card {
            border: 1px solid #e9ecef;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s;
        }
        
        .booking-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .booking-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .info-item i {
            color: #667eea;
            width: 20px;
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
                        <h2><i class="fas fa-history"></i> Lịch sử đặt phòng</h2>
                        <a href="booking.php" class="btn btn-custom">
                            <i class="fas fa-plus"></i> Đặt phòng mới
                        </a>
                    </div>
                    
                    <?php if (empty($bookings)): ?>
                        <div class="card">
                            <div class="card-body text-center py-5">
                                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Chưa có đặt phòng nào</h5>
                                <p class="text-muted">Bắt đầu đặt phòng ngay hôm nay!</p>
                                <a href="booking.php" class="btn btn-custom">Đặt phòng ngay</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($bookings as $booking): ?>
                                <div class="col-md-6 col-lg-4">
                                    <div class="booking-card">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <h6 class="mb-0"><?php echo $booking['RoomType']; ?></h6>
                                            <span class="booking-status status-<?php echo strtolower($booking['stat'] ?? 'notconfirm'); ?>">
                                                <?php echo $booking['stat'] == 'Confirm' ? 'Confirmed' : 'Pending'; ?>
                                            </span>
                                        </div>
                                        
                                        <div class="booking-info">
                                            <div class="info-item">
                                                <i class="fas fa-calendar-check"></i>
                                                <span><?php echo date('d/m/Y', strtotime($booking['cin'])); ?></span>
                                            </div>
                                            <div class="info-item">
                                                <i class="fas fa-calendar-times"></i>
                                                <span><?php echo date('d/m/Y', strtotime($booking['cout'])); ?></span>
                                            </div>
                                            <div class="info-item">
                                                <i class="fas fa-bed"></i>
                                                <span><?php echo $booking['Bed']; ?></span>
                                            </div>
                                            <div class="info-item">
                                                <i class="fas fa-door-open"></i>
                                                <span><?php echo $booking['NoofRoom']; ?> phòng</span>
                                            </div>
                                            <div class="info-item">
                                                <i class="fas fa-utensils"></i>
                                                <span><?php echo $booking['Meal']; ?></span>
                                            </div>
                                            <div class="info-item">
                                                <i class="fas fa-clock"></i>
                                                <span><?php echo $booking['nodays']; ?> ngày</span>
                                            </div>
                                        </div>
                                        
                                        <hr>
                                        
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="text-primary mb-0">
                                                    <?php echo number_format(calculateTotalPrice($booking), 0, ',', '.'); ?> VNĐ
                                                </h6>
                                                <small class="text-muted">Tổng chi phí</small>
                                            </div>
                                            <div class="btn-group">
                                                <a href="booking-detail.php?id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if (($booking['stat'] ?? 'NotConfirm') == 'NotConfirm'): ?>
                                                    <a href="payment.php?booking_id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-success">
                                                        <i class="fas fa-credit-card"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 