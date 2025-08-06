<?php
require_once 'config.php';

if (!is_logged_in()) {
    redirect('index.php');
}

$user_email = $_SESSION['user_email'];
$user_name = $_SESSION['user_name'];

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize_input($_POST['name']);
    $email = sanitize_input($_POST['email']);
    $phone = sanitize_input($_POST['phone']);
    $room_type = sanitize_input($_POST['room_type']);
    $bed_type = sanitize_input($_POST['bed_type']);
    $no_of_rooms = (int)$_POST['no_of_rooms'];
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];
    $meal = sanitize_input($_POST['meal']);
    
    // Tính toán giá
    $room_prices = [
        'Superior Room' => 1000,
        'Deluxe Room' => 1500,
        'Guest House' => 800
    ];
    
    $bed_prices = [
        'Single' => 10,
        'Double' => 20,
        'Triple' => 30,
        'Quad' => 40
    ];
    
    $meal_prices = [
        'Room only' => 0,
        'Breakfast' => 200,
        'Half Board' => 400,
        'Full Board' => 600
    ];
    
    $room_total = $room_prices[$room_type] * $no_of_rooms;
    $bed_total = $bed_prices[$bed_type] * $no_of_rooms;
    $meal_total = $meal_prices[$meal] * $no_of_rooms;
    
    $date1 = new DateTime($check_in);
    $date2 = new DateTime($check_out);
    $days = $date2->diff($date1)->days;
    
    $final_total = ($room_total + $bed_total + $meal_total) * $days;
    
    try {
        $stmt = $conn->prepare("INSERT INTO roombook (Name, Email, Phone, RoomType, Bed, NoofRoom, cin, cout, nodays, Meal, stat) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'NotConfirm')");
        $stmt->execute([$name, $email, $phone, $room_type, $bed_type, $no_of_rooms, $check_in, $check_out, $days, $meal]);
        
        $booking_id = $conn->lastInsertId();
        redirect("payment.php?booking_id=$booking_id");
    } catch(PDOException $e) {
        $message = 'Lỗi đặt phòng: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt phòng - BlueBird Hotel</title>
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
        
        .booking-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            overflow: hidden;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .booking-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .booking-form {
            padding: 40px;
        }
        
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 15px;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-custom {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            border-radius: 10px;
            color: white;
            padding: 15px 30px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        .price-preview {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 20px;
            margin-top: 20px;
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
                        <a class="nav-link active" href="booking.php">
                            <i class="fas fa-calendar-plus"></i> Đặt phòng
                        </a>
                        <a class="nav-link" href="my-bookings.php">
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
                    <div class="booking-container">
                        <div class="booking-header">
                            <h2><i class="fas fa-calendar-plus"></i> Đặt phòng khách sạn</h2>
                            <p class="mb-0">Chào mừng bạn đến với BlueBird Hotel</p>
                        </div>
                        
                        <div class="booking-form">
                            <?php if ($message): ?>
                                <div class="alert alert-danger" role="alert">
                                    <i class="fas fa-exclamation-triangle"></i> <?php echo $message; ?>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" action="" id="bookingForm">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">
                                                <i class="fas fa-user"></i> Họ và tên
                                            </label>
                                            <input type="text" class="form-control" id="name" name="name" value="<?php echo $user_name; ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="email" class="form-label">
                                                <i class="fas fa-envelope"></i> Email
                                            </label>
                                            <input type="email" class="form-control" id="email" name="email" value="<?php echo $user_email; ?>" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="phone" class="form-label">
                                                <i class="fas fa-phone"></i> Số điện thoại
                                            </label>
                                            <input type="tel" class="form-control" id="phone" name="phone" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="room_type" class="form-label">
                                                <i class="fas fa-bed"></i> Loại phòng
                                            </label>
                                            <select class="form-control" id="room_type" name="room_type" required>
                                                <option value="">Chọn loại phòng</option>
                                                <option value="Superior Room">Superior Room - 1.000 VNĐ</option>
                                                <option value="Deluxe Room">Deluxe Room - 1.500 VNĐ</option>
                                                <option value="Guest House">Guest House - 800 VNĐ</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="bed_type" class="form-label">
                                                <i class="fas fa-bed"></i> Loại giường
                                            </label>
                                            <select class="form-control" id="bed_type" name="bed_type" required>
                                                <option value="">Chọn loại giường</option>
                                                <option value="Single">Single - 10 VNĐ</option>
                                                <option value="Double">Double - 20 VNĐ</option>
                                                <option value="Triple">Triple - 30 VNĐ</option>
                                                <option value="Quad">Quad - 40 VNĐ</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="no_of_rooms" class="form-label">
                                                <i class="fas fa-door-open"></i> Số phòng
                                            </label>
                                            <input type="number" class="form-control" id="no_of_rooms" name="no_of_rooms" min="1" value="1" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="check_in" class="form-label">
                                                <i class="fas fa-calendar-check"></i> Ngày check-in
                                            </label>
                                            <input type="date" class="form-control" id="check_in" name="check_in" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="check_out" class="form-label">
                                                <i class="fas fa-calendar-times"></i> Ngày check-out
                                            </label>
                                            <input type="date" class="form-control" id="check_out" name="check_out" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="meal" class="form-label">
                                        <i class="fas fa-utensils"></i> Dịch vụ ăn uống
                                    </label>
                                    <select class="form-control" id="meal" name="meal" required>
                                        <option value="">Chọn dịch vụ</option>
                                        <option value="Room only">Chỉ phòng - 0 VNĐ</option>
                                        <option value="Breakfast">Bữa sáng - 200 VNĐ</option>
                                        <option value="Half Board">Nửa bảng - 400 VNĐ</option>
                                        <option value="Full Board">Toàn bảng - 600 VNĐ</option>
                                    </select>
                                </div>
                                
                                <div class="price-preview" id="pricePreview" style="display: none;">
                                    <h5><i class="fas fa-calculator"></i> Dự toán chi phí</h5>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Giá phòng:</strong> <span id="roomPrice">0</span> VNĐ</p>
                                            <p><strong>Phụ phí giường:</strong> <span id="bedPrice">0</span> VNĐ</p>
                                            <p><strong>Dịch vụ ăn uống:</strong> <span id="mealPrice">0</span> VNĐ</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Số ngày:</strong> <span id="days">0</span> ngày</p>
                                            <p><strong>Tổng cộng:</strong> <span id="totalPrice" class="text-primary fw-bold">0</span> VNĐ</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="text-center mt-4">
                                    <button type="submit" class="btn btn-custom btn-lg">
                                        <i class="fas fa-credit-card"></i> Tiến hành thanh toán
                                    </button>
                                    <a href="dashboard.php" class="btn btn-outline-secondary btn-lg ms-2">
                                        <i class="fas fa-arrow-left"></i> Quay lại
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Tính toán giá tự động
        function calculatePrice() {
            const roomType = document.getElementById('room_type').value;
            const bedType = document.getElementById('bed_type').value;
            const noOfRooms = parseInt(document.getElementById('no_of_rooms').value) || 0;
            const meal = document.getElementById('meal').value;
            const checkIn = new Date(document.getElementById('check_in').value);
            const checkOut = new Date(document.getElementById('check_out').value);
            
            const roomPrices = {
                'Superior Room': 1000,
                'Deluxe Room': 1500,
                'Guest House': 800
            };
            
            const bedPrices = {
                'Single': 10,
                'Double': 20,
                'Triple': 30,
                'Quad': 40
            };
            
            const mealPrices = {
                'Room only': 0,
                'Breakfast': 200,
                'Half Board': 400,
                'Full Board': 600
            };
            
            if (roomType && bedType && meal && checkIn && checkOut) {
                const roomTotal = roomPrices[roomType] * noOfRooms;
                const bedTotal = bedPrices[bedType] * noOfRooms;
                const mealTotal = mealPrices[meal] * noOfRooms;
                const days = Math.ceil((checkOut - checkIn) / (1000 * 60 * 60 * 24));
                const total = (roomTotal + bedTotal + mealTotal) * days;
                
                document.getElementById('roomPrice').textContent = roomTotal.toLocaleString();
                document.getElementById('bedPrice').textContent = bedTotal.toLocaleString();
                document.getElementById('mealPrice').textContent = mealTotal.toLocaleString();
                document.getElementById('days').textContent = days;
                document.getElementById('totalPrice').textContent = total.toLocaleString();
                document.getElementById('pricePreview').style.display = 'block';
            } else {
                document.getElementById('pricePreview').style.display = 'none';
            }
        }
        
        // Thêm event listeners
        document.getElementById('room_type').addEventListener('change', calculatePrice);
        document.getElementById('bed_type').addEventListener('change', calculatePrice);
        document.getElementById('no_of_rooms').addEventListener('input', calculatePrice);
        document.getElementById('meal').addEventListener('change', calculatePrice);
        document.getElementById('check_in').addEventListener('change', calculatePrice);
        document.getElementById('check_out').addEventListener('change', calculatePrice);
    </script>
</body>
</html> 