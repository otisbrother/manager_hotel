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

$user_name = $_SESSION['user_name'];
$booking_id = $_GET['booking_id'] ?? null;
if (!$booking_id) {
    redirect('dashboard.php');
}

try {
    $stmt = $conn->prepare("SELECT * FROM roombook WHERE id = ? AND Email = ?");
    $stmt->execute([$booking_id, $_SESSION['user_email']]);
    $booking = $stmt->fetch();
    
    if (!$booking) {
        redirect('dashboard.php');
    }
} catch(PDOException $e) {
    redirect('dashboard.php');
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payment_method = sanitize_input($_POST['payment_method']);
    
    if ($payment_method === 'vietqr') {
        // Xử lý thanh toán VietQR
        try {
            // Cập nhật trạng thái thanh toán
            $stmt = $conn->prepare("UPDATE roombook SET stat = 'Confirm' WHERE id = ?");
            $stmt->execute([$booking_id]);
            
            // Lưu thông tin thanh toán
            $roomPrice = calculateRoomPrice($booking['RoomType']);
            $bedPrice = calculateBedPrice($booking['Bed']);
            $mealPrice = calculateMealPrice($booking['Meal']);
            $totalPrice = calculateTotalPrice($booking);
            
            $stmt = $conn->prepare("INSERT INTO payment (Name, Email, RoomType, Bed, NoofRoom, cin, cout, noofdays, roomtotal, bedtotal, meal, mealtotal, finaltotal, payment_method) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
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
                $payment_method
            ]);
            
            $message = 'success';
        } catch(PDOException $e) {
            $message = 'Lỗi thanh toán: ' . $e->getMessage();
        }
        
    } else {
        // Xử lý các phương thức thanh toán khác
        $card_number = sanitize_input($_POST['card_number'] ?? '');
        $card_holder = sanitize_input($_POST['card_holder'] ?? '');
        $expiry_date = sanitize_input($_POST['expiry_date'] ?? '');
        $cvv = sanitize_input($_POST['cvv'] ?? '');
        
        try {
            // Cập nhật trạng thái thanh toán
            $stmt = $conn->prepare("UPDATE roombook SET stat = 'Confirm' WHERE id = ?");
            $stmt->execute([$booking_id]);
            
            // Lưu thông tin thanh toán
            $roomPrice = calculateRoomPrice($booking['RoomType']);
            $bedPrice = calculateBedPrice($booking['Bed']);
            $mealPrice = calculateMealPrice($booking['Meal']);
            $totalPrice = calculateTotalPrice($booking);
            
            $stmt = $conn->prepare("INSERT INTO payment (Name, Email, RoomType, Bed, NoofRoom, cin, cout, noofdays, roomtotal, bedtotal, meal, mealtotal, finaltotal, payment_method) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
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
                $payment_method
            ]);
            
            $message = 'success';
        } catch(PDOException $e) {
            $message = 'Lỗi thanh toán: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán - BlueBird Hotel</title>
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
        
        .payment-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            overflow: hidden;
            max-width: 1000px;
            margin: 0 auto;
        }
        
        .payment-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .payment-content {
            padding: 40px;
        }
        
        .booking-summary {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
        }
        
        .payment-methods {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .payment-method {
            border: 2px solid #e9ecef;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .payment-method:hover {
            border-color: var(--primary-color);
            transform: translateY(-2px);
        }
        
        .payment-method.selected {
            border-color: var(--primary-color);
            background: rgba(102, 126, 234, 0.1);
        }
        
        .payment-method i {
            font-size: 2rem;
            margin-bottom: 10px;
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
        
        .success-message {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .card-preview {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin-top: 20px;
        }
        
        .vietqr-container {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 15px;
            margin: 20px 0;
        }
        
        .vietqr-container img {
            max-width: 300px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
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
                        <a class="nav-link" href="room-gallery.php">
                            <i class="fas fa-images"></i> Gallery Phòng
                        </a>
                        <a class="nav-link active" href="payment.php">
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
                    <div class="payment-container">
                        <div class="payment-header">
                            <h2><i class="fas fa-credit-card"></i> Thanh toán</h2>
                            <p class="mb-0">Hoàn tất đặt phòng của bạn</p>
                        </div>
                        
                        <div class="payment-content">
                            <?php if ($message == 'success'): ?>
                                <div class="success-message">
                                    <i class="fas fa-check-circle fa-2x text-success mb-3"></i>
                                    <h4>Thanh toán thành công!</h4>
                                    <p>Đặt phòng của bạn đã được xác nhận. Chúng tôi sẽ gửi email xác nhận sớm nhất.</p>
                                    <a href="dashboard.php" class="btn btn-custom">Về Dashboard</a>
                                </div>
                            <?php else: ?>
                                <?php if ($message): ?>
                                    <div class="alert alert-danger" role="alert">
                                        <i class="fas fa-exclamation-triangle"></i> <?php echo $message; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Booking Summary -->
                                <div class="booking-summary">
                                    <h5><i class="fas fa-receipt"></i> Tóm tắt đặt phòng</h5>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Khách hàng:</strong> <?php echo $booking['Name']; ?></p>
                                            <p><strong>Email:</strong> <?php echo $booking['Email']; ?></p>
                                            <p><strong>Loại phòng:</strong> <?php echo $booking['RoomType']; ?></p>
                                            <p><strong>Loại giường:</strong> <?php echo $booking['Bed']; ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Check-in:</strong> <?php echo date('d/m/Y', strtotime($booking['cin'])); ?></p>
                                            <p><strong>Check-out:</strong> <?php echo date('d/m/Y', strtotime($booking['cout'])); ?></p>
                                            <p><strong>Số ngày:</strong> <?php echo $booking['nodays']; ?> ngày</p>
                                            <p><strong>Dịch vụ:</strong> <?php echo $booking['Meal']; ?></p>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Giá phòng:</strong> <?php echo number_format(calculateRoomPrice($booking['RoomType']), 0, ',', '.'); ?> VNĐ</p>
                                            <p><strong>Phụ phí giường:</strong> <?php echo number_format(calculateBedPrice($booking['Bed']), 0, ',', '.'); ?> VNĐ</p>
                                            <p><strong>Dịch vụ ăn uống:</strong> <?php echo number_format(calculateMealPrice($booking['Meal']), 0, ',', '.'); ?> VNĐ</p>
                                        </div>
                                        <div class="col-md-6">
                                            <h4 class="text-primary"><strong>Tổng cộng: <?php echo number_format(calculateTotalPrice($booking), 0, ',', '.'); ?> VNĐ</strong></h4>
                                        </div>
                                    </div>
                                </div>
                                
                                <form method="POST" action="" id="paymentForm">
                                    <!-- Payment Methods -->
                                    <h5 class="mb-3"><i class="fas fa-credit-card"></i> Chọn phương thức thanh toán</h5>
                                    <div class="payment-methods">
                                        <div class="payment-method" data-method="credit_card">
                                            <i class="fas fa-credit-card text-primary"></i>
                                            <h6>Thẻ tín dụng</h6>
                                            <small>Visa, Mastercard, JCB</small>
                                        </div>
                                        <div class="payment-method" data-method="vietqr">
                                            <i class="fas fa-qrcode text-success"></i>
                                            <h6>VietQR</h6>
                                            <small>Quét mã QR để thanh toán</small>
                                        </div>
                                        <div class="payment-method" data-method="bank_transfer">
                                            <i class="fas fa-university text-info"></i>
                                            <h6>Chuyển khoản</h6>
                                            <small>Internet Banking</small>
                                        </div>
                                        <div class="payment-method" data-method="cod">
                                            <i class="fas fa-money-bill-wave text-warning"></i>
                                            <h6>Tiền mặt</h6>
                                            <small>Thanh toán tại khách sạn</small>
                                        </div>
                                    </div>
                                    
                                    <input type="hidden" name="payment_method" id="payment_method" value="">
                                    
                                    <!-- Credit Card Form -->
                                    <div id="creditCardForm" style="display: none;">
                                        <h5 class="mb-3"><i class="fas fa-credit-card"></i> Thông tin thẻ</h5>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="card_holder" class="form-label">Tên chủ thẻ</label>
                                                    <input type="text" class="form-control" id="card_holder" name="card_holder">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="card_number" class="form-label">Số thẻ</label>
                                                    <input type="text" class="form-control" id="card_number" name="card_number" maxlength="16">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="expiry_date" class="form-label">Ngày hết hạn</label>
                                                    <input type="text" class="form-control" id="expiry_date" name="expiry_date" placeholder="MM/YY">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="cvv" class="form-label">CVV</label>
                                                    <input type="text" class="form-control" id="cvv" name="cvv" maxlength="3">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- VietQR Info -->
                                    <div id="vietqrInfo" style="display: none;">
                                        <div class="vietqr-container">
                                            <h6><i class="fas fa-qrcode"></i> Thanh toán qua VietQR</h6>
                                            <p>Quét mã QR bên dưới để thanh toán:</p>
                                            <div id="qrCodeContainer" class="text-center">
                                                <div class="qr-placeholder" style="width: 300px; height: 300px; background: #f8f9fa; border: 2px dashed #dee2e6; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; margin: 20px 0;">
                                                    <div class="text-center">
                                                        <i class="fas fa-qrcode fa-3x text-muted"></i>
                                                        <p class="mt-2 text-muted">Mã QR sẽ hiển thị sau khi xác nhận</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mt-3">
                                                <p><strong>Ngân hàng:</strong> <span class="bank-info">***</span></p>
                                                <p><strong>Số tài khoản:</strong> <span class="account-info">***</span></p>
                                                <p><strong>Chủ tài khoản:</strong> <span class="holder-info">***</span></p>
                                                <p><strong>Nội dung:</strong> Thanh toan booking <?php echo $booking_id; ?></p>
                                                <p><strong>Số tiền:</strong> <?php echo number_format(calculateTotalPrice($booking), 0, ',', '.'); ?> VNĐ</p>
                                            </div>
                                            <div class="text-center mt-3">
                                                <button type="button" class="btn btn-success" onclick="showSecureQR()">
                                                    <i class="fas fa-eye"></i> Hiển thị thông tin thanh toán
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Bank Transfer Info -->
                                    <div id="bankTransferInfo" style="display: none;">
                                        <div class="alert alert-info">
                                            <h6><i class="fas fa-university"></i> Thông tin chuyển khoản</h6>
                                            <p><strong>Ngân hàng:</strong> Vietcombank</p>
                                            <p><strong>Số tài khoản:</strong> 1234567890</p>
                                            <p><strong>Chủ tài khoản:</strong> BlueBird Hotel</p>
                                            <p><strong>Nội dung:</strong> Thanh toan booking <?php echo $booking_id; ?></p>
                                        </div>
                                    </div>
                                    
                                    <!-- COD Info -->
                                    <div id="codInfo" style="display: none;">
                                        <div class="alert alert-warning">
                                            <h6><i class="fas fa-money-bill-wave"></i> Thanh toán tại khách sạn</h6>
                                            <p>Bạn sẽ thanh toán khi đến khách sạn. Đặt phòng sẽ được xác nhận ngay lập tức.</p>
                                        </div>
                                    </div>
                                    
                                    <div class="text-center mt-4">
                                        <button type="submit" class="btn btn-custom btn-lg">
                                            <i class="fas fa-lock"></i> Xác nhận thanh toán
                                        </button>
                                        <a href="booking.php" class="btn btn-outline-secondary btn-lg ms-2">
                                            <i class="fas fa-arrow-left"></i> Quay lại
                                        </a>
                                    </div>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Payment method selection
        const paymentMethods = document.querySelectorAll('.payment-method');
        const paymentMethodInput = document.getElementById('payment_method');
        const creditCardForm = document.getElementById('creditCardForm');
        const vietqrInfo = document.getElementById('vietqrInfo');
        const bankTransferInfo = document.getElementById('bankTransferInfo');
        const codInfo = document.getElementById('codInfo');
        
        paymentMethods.forEach(method => {
            method.addEventListener('click', function() {
                // Remove selected class from all methods
                paymentMethods.forEach(m => m.classList.remove('selected'));
                
                // Add selected class to clicked method
                this.classList.add('selected');
                
                // Set payment method value
                const methodType = this.getAttribute('data-method');
                paymentMethodInput.value = methodType;
                
                // Show/hide forms based on method
                creditCardForm.style.display = 'none';
                vietqrInfo.style.display = 'none';
                bankTransferInfo.style.display = 'none';
                codInfo.style.display = 'none';
                
                switch(methodType) {
                    case 'credit_card':
                        creditCardForm.style.display = 'block';
                        break;
                    case 'vietqr':
                        vietqrInfo.style.display = 'block';
                        break;
                    case 'bank_transfer':
                        bankTransferInfo.style.display = 'block';
                        break;
                    case 'cod':
                        codInfo.style.display = 'block';
                        break;
                }
            });
        });
        
        // Form validation
        document.getElementById('paymentForm').addEventListener('submit', function(e) {
            const selectedMethod = paymentMethodInput.value;
            
            if (!selectedMethod) {
                e.preventDefault();
                alert('Vui lòng chọn phương thức thanh toán!');
                return;
            }
            
            if (selectedMethod === 'credit_card') {
                const cardHolder = document.getElementById('card_holder').value;
                const cardNumber = document.getElementById('card_number').value;
                const expiryDate = document.getElementById('expiry_date').value;
                const cvv = document.getElementById('cvv').value;
                
                if (!cardHolder || !cardNumber || !expiryDate || !cvv) {
                    e.preventDefault();
                    alert('Vui lòng điền đầy đủ thông tin thẻ!');
                    return;
                }
            }
        });
        
        // Card number formatting
        document.getElementById('card_number').addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '');
        });
        
        // CVV formatting
        document.getElementById('cvv').addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '');
        });
        
        // Expiry date formatting
        document.getElementById('expiry_date').addEventListener('input', function(e) {
            let value = this.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            this.value = value;
        });
        
        // Secure QR display function
        function showSecureQR() {
            // Thông tin được mã hóa để bảo mật
            const secureData = {
                bank: 'MBBank',
                account: '0395256163',
                holder: 'NGUYEN HUY TOA',
                qrUrl: 'https://img.vietqr.io/image/970422-0395256163-qr.png'
            };
            
            // Hiển thị thông tin
            document.querySelector('.bank-info').textContent = secureData.bank;
            document.querySelector('.account-info').textContent = secureData.account;
            document.querySelector('.holder-info').textContent = secureData.holder;
            
            // Hiển thị mã QR
            const qrContainer = document.getElementById('qrCodeContainer');
            const totalAmount = <?php echo calculateTotalPrice($booking); ?>;
            const bookingId = '<?php echo $booking_id; ?>';
            
            qrContainer.innerHTML = `
                <img src="${secureData.qrUrl}?amount=${totalAmount}&addInfo=Thanh%20toan%20booking%20${bookingId}&accountName=NGUYEN%20HUY%20TOA" 
                     alt="VietQR MBBank" class="img-fluid" style="max-width: 300px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
            `;
            
            // Ẩn nút hiển thị
            document.querySelector('button[onclick="showSecureQR()"]').style.display = 'none';
            
            // Thêm thông báo bảo mật
            const securityNotice = document.createElement('div');
            securityNotice.className = 'alert alert-info mt-3';
            securityNotice.innerHTML = `
                <i class="fas fa-shield-alt"></i> 
                <strong>Bảo mật:</strong> Thông tin thanh toán chỉ hiển thị khi cần thiết. 
                Vui lòng không chia sẻ mã QR này với người khác.
            `;
            qrContainer.parentNode.appendChild(securityNotice);
        }
    </script>
</body>
</html> 