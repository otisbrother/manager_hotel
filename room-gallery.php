<?php
require_once 'config.php';

if (!is_logged_in()) {
    redirect('index.php');
}

$user_name = $_SESSION['user_name'];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery Phòng - BlueBird Hotel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="css/room-images.css" rel="stylesheet">
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
        
        .section-title {
            color: #667eea;
            font-weight: 700;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .gallery-section {
            margin-bottom: 50px;
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
                        <a class="nav-link" href="my-bookings.php">
                            <i class="fas fa-list"></i> Lịch sử đặt phòng
                        </a>
                        <a class="nav-link active" href="room-gallery.php">
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
                        <h2><i class="fas fa-images"></i> Gallery Phòng & Giường</h2>
                        <a href="booking.php" class="btn btn-custom">
                            <i class="fas fa-calendar-plus"></i> Đặt phòng ngay
                        </a>
                    </div>
                    
                    <!-- Loại phòng -->
                    <div class="gallery-section">
                        <h3 class="section-title">
                            <i class="fas fa-bed"></i> Các Loại Phòng
                        </h3>
                        <div class="room-grid">
                            <div class="room-option">
                                <div class="room-image-container">
                                    <img src="image/hotel1.jpg" alt="Superior Room" class="room-image">
                                    <div class="room-label">Superior Room</div>
                                    <div class="room-price">1.000 VNĐ</div>
                                </div>
                                <div class="text-center mt-3">
                                    <h5>Superior Room</h5>
                                    <p class="text-muted">Phòng cao cấp với view đẹp</p>
                                </div>
                            </div>
                            
                            <div class="room-option">
                                <div class="room-image-container">
                                    <img src="image/hotel2.jpg" alt="Deluxe Room" class="room-image">
                                    <div class="room-label">Deluxe Room</div>
                                    <div class="room-price">1.500 VNĐ</div>
                                </div>
                                <div class="text-center mt-3">
                                    <h5>Deluxe Room</h5>
                                    <p class="text-muted">Phòng sang trọng với tiện nghi cao cấp</p>
                                </div>
                            </div>
                            
                            <div class="room-option">
                                <div class="room-image-container">
                                    <img src="image/hotel3.jpg" alt="Guest House" class="room-image">
                                    <div class="room-label">Guest House</div>
                                    <div class="room-price">800 VNĐ</div>
                                </div>
                                <div class="text-center mt-3">
                                    <h5>Guest House</h5>
                                    <p class="text-muted">Phòng tiện nghi với giá hợp lý</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Loại giường -->
                    <div class="gallery-section">
                        <h3 class="section-title">
                            <i class="fas fa-bed"></i> Các Loại Giường
                        </h3>
                        <div class="bed-grid">
                            <div class="bed-option">
                                <div class="room-image-container">
                                    <img src="image/hotel1photo.webp" alt="Single Bed" class="bed-image">
                                    <div class="bed-label">Single</div>
                                    <div class="bed-price">10 VNĐ</div>
                                </div>
                                <div class="text-center mt-3">
                                    <h6>Single Bed</h6>
                                    <p class="text-muted small">Giường đơn cho 1 người</p>
                                </div>
                            </div>
                            
                            <div class="bed-option">
                                <div class="room-image-container">
                                    <img src="image/hotel2photo.jpg" alt="Double Bed" class="bed-image">
                                    <div class="bed-label">Double</div>
                                    <div class="bed-price">20 VNĐ</div>
                                </div>
                                <div class="text-center mt-3">
                                    <h6>Double Bed</h6>
                                    <p class="text-muted small">Giường đôi cho 2 người</p>
                                </div>
                            </div>
                            
                            <div class="bed-option">
                                <div class="room-image-container">
                                    <img src="image/hotel3photo.avif" alt="Triple Bed" class="bed-image">
                                    <div class="bed-label">Triple</div>
                                    <div class="bed-price">30 VNĐ</div>
                                </div>
                                <div class="text-center mt-3">
                                    <h6>Triple Bed</h6>
                                    <p class="text-muted small">Giường 3 người</p>
                                </div>
                            </div>
                            
                            <div class="bed-option">
                                <div class="room-image-container">
                                    <img src="image/hotel4photo.jpg" alt="Quad Bed" class="bed-image">
                                    <div class="bed-label">Quad</div>
                                    <div class="bed-price">40 VNĐ</div>
                                </div>
                                <div class="text-center mt-3">
                                    <h6>Quad Bed</h6>
                                    <p class="text-muted small">Giường 4 người</p>
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