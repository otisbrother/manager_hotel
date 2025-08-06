<?php
require_once 'config.php';
require_once 'auth.php';
require_once 'middleware.php';

// Kiểm tra đăng nhập
$middleware->requireLogin();

// Lấy thông tin user hiện tại
$currentUser = $auth->getCurrentUser();

$error = '';
$success = '';

// Xử lý cập nhật thông tin
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_profile'])) {
        $name = sanitize_input($_POST['name']);
        $email = sanitize_input($_POST['email']);
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        try {
            // Kiểm tra mật khẩu hiện tại
            if ($currentUser['type'] == 'user') {
                $stmt = $conn->prepare("SELECT * FROM signup WHERE UserID = ? AND Password = ?");
                $stmt->execute([$currentUser['id'], $current_password]);
            } else {
                $stmt = $conn->prepare("SELECT * FROM emp_login WHERE empid = ? AND Emp_Password = ?");
                $stmt->execute([$currentUser['id'], $current_password]);
            }
            
            if ($stmt->fetch()) {
                // Cập nhật thông tin
                if ($currentUser['type'] == 'user') {
                    $stmt = $conn->prepare("UPDATE signup SET Username = ?, Email = ? WHERE UserID = ?");
                    $stmt->execute([$name, $email, $currentUser['id']]);
                } else {
                    $stmt = $conn->prepare("UPDATE emp_login SET Emp_Email = ? WHERE empid = ?");
                    $stmt->execute([$email, $currentUser['id']]);
                }
                
                // Cập nhật mật khẩu nếu có
                if (!empty($new_password)) {
                    if ($new_password === $confirm_password) {
                        if ($currentUser['type'] == 'user') {
                            $stmt = $conn->prepare("UPDATE signup SET Password = ? WHERE UserID = ?");
                            $stmt->execute([$new_password, $currentUser['id']]);
                        } else {
                            $stmt = $conn->prepare("UPDATE emp_login SET Emp_Password = ? WHERE empid = ?");
                            $stmt->execute([$new_password, $currentUser['id']]);
                        }
                        $success = 'Cập nhật thông tin và mật khẩu thành công!';
                    } else {
                        $error = 'Mật khẩu mới không khớp!';
                    }
                } else {
                    $success = 'Cập nhật thông tin thành công!';
                }
                
                // Cập nhật session
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                $currentUser = $auth->getCurrentUser();
                
            } else {
                $error = 'Mật khẩu hiện tại không đúng!';
            }
        } catch(PDOException $e) {
            $error = 'Lỗi cập nhật: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ sơ - BlueBird Hotel</title>
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
        
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 15px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            margin: 0 auto 20px;
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
                        <i class="fas fa-user-circle"></i> <?php echo $currentUser['name']; ?>
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
                        <a class="nav-link" href="payment.php">
                            <i class="fas fa-credit-card"></i> Thanh toán
                        </a>
                        <a class="nav-link active" href="profile.php">
                            <i class="fas fa-user"></i> Hồ sơ
                        </a>
                    </nav>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="row">
                    <div class="col-lg-8 mx-auto">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="mb-0">
                                    <i class="fas fa-user-circle"></i> Hồ sơ cá nhân
                                </h4>
                            </div>
                            <div class="card-body p-4">
                                <?php if ($error): ?>
                                    <div class="alert alert-danger" role="alert">
                                        <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($success): ?>
                                    <div class="alert alert-success" role="alert">
                                        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="text-center mb-4">
                                    <div class="profile-avatar">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <h5><?php echo $currentUser['name']; ?></h5>
                                    <p class="text-muted"><?php echo ucfirst($currentUser['type']); ?></p>
                                </div>
                                
                                <form method="POST" action="">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="name" class="form-label">
                                                    <i class="fas fa-user"></i> Họ và tên
                                                </label>
                                                <input type="text" class="form-control" id="name" name="name" 
                                                       value="<?php echo $currentUser['name']; ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="email" class="form-label">
                                                    <i class="fas fa-envelope"></i> Email
                                                </label>
                                                <input type="email" class="form-control" id="email" name="email" 
                                                       value="<?php echo $currentUser['email']; ?>" required>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <hr class="my-4">
                                    
                                    <h6 class="mb-3">
                                        <i class="fas fa-lock"></i> Thay đổi mật khẩu
                                    </h6>
                                    
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="current_password" class="form-label">
                                                    <i class="fas fa-key"></i> Mật khẩu hiện tại
                                                </label>
                                                <input type="password" class="form-control" id="current_password" 
                                                       name="current_password" placeholder="Nhập mật khẩu hiện tại">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="new_password" class="form-label">
                                                    <i class="fas fa-lock"></i> Mật khẩu mới
                                                </label>
                                                <input type="password" class="form-control" id="new_password" 
                                                       name="new_password" placeholder="Nhập mật khẩu mới">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="confirm_password" class="form-label">
                                                    <i class="fas fa-lock"></i> Xác nhận mật khẩu
                                                </label>
                                                <input type="password" class="form-control" id="confirm_password" 
                                                       name="confirm_password" placeholder="Xác nhận mật khẩu mới">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="text-center mt-4">
                                        <button type="submit" name="update_profile" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Cập nhật hồ sơ
                                        </button>
                                    </div>
                                </form>
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