<?php
require_once 'config.php';

// Kiểm tra đăng nhập
if (!is_logged_in()) {
    redirect('index.php');
}

$user_id = $_SESSION['user_id'];
$user_email = $_SESSION['user_email'];

// Lấy ID đặt phòng từ URL
$booking_id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$booking_id) {
    $_SESSION['error'] = 'ID đặt phòng không hợp lệ!';
    redirect('dashboard.php');
}

// Lấy thông tin đặt phòng
try {
    $stmt = $conn->prepare("SELECT * FROM roombook WHERE id = ? AND Email = ?");
    $stmt->execute([$booking_id, $user_email]);
    $booking = $stmt->fetch();

    if (!$booking) {
        $_SESSION['error'] = 'Không tìm thấy đặt phòng hoặc bạn không có quyền sửa!';
        redirect('dashboard.php');
    }

    // Kiểm tra trạng thái - chỉ cho phép sửa đặt phòng chưa xác nhận
    if ($booking['stat'] === 'Confirm') {
        $_SESSION['error'] = 'Không thể sửa đặt phòng đã được xác nhận!';
        redirect('dashboard.php');
    }

} catch(PDOException $e) {
    $_SESSION['error'] = 'Có lỗi xảy ra: ' . $e->getMessage();
    redirect('dashboard.php');
}

// Xử lý form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $roomType = $_POST['roomType'] ?? '';
    $bed = $_POST['bed'] ?? '';
    $meal = $_POST['meal'] ?? '';
    $cin = $_POST['cin'] ?? '';
    $cout = $_POST['cout'] ?? '';
    $nodays = $_POST['nodays'] ?? '';
    $stat = $booking['stat']; // Giữ nguyên trạng thái

    // Validation
    $errors = [];
    if (empty($roomType)) $errors[] = 'Vui lòng chọn loại phòng';
    if (empty($bed)) $errors[] = 'Vui lòng chọn loại giường';
    if (empty($meal)) $errors[] = 'Vui lòng chọn dịch vụ ăn uống';
    if (empty($cin)) $errors[] = 'Vui lòng chọn ngày check-in';
    if (empty($cout)) $errors[] = 'Vui lòng chọn ngày check-out';
    if (empty($nodays)) $errors[] = 'Vui lòng nhập số ngày';

    if (empty($errors)) {
        try {
            $update_stmt = $conn->prepare("UPDATE roombook SET 
                RoomType = ?, Bed = ?, Meal = ?, cin = ?, cout = ?, nodays = ? 
                WHERE id = ? AND Email = ?");
            
            $result = $update_stmt->execute([
                $roomType, $bed, $meal, $cin, $cout, $nodays, $booking_id, $user_email
            ]);

            if ($result) {
                $_SESSION['success'] = 'Cập nhật đặt phòng thành công!';
                redirect('dashboard.php');
            } else {
                $errors[] = 'Có lỗi xảy ra khi cập nhật!';
            }
        } catch(PDOException $e) {
            $errors[] = 'Có lỗi xảy ra: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa đặt phòng - BlueBird Hotel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="css/booking-actions.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
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
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
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
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .alert {
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <img src="./image/bluebirdlogo.png" alt="Logo" width="30" height="30" class="d-inline-block align-text-top me-2">
                BlueBird Hotel
            </a>
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle"></i> <?php echo $_SESSION['user_name']; ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user"></i> Hồ sơ</a></li>
                        <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-edit"></i> Sửa đặt phòng</h5>
                    </div>
                    <div class="card-body p-4">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="roomType" class="form-label">Loại phòng *</label>
                                    <select class="form-select" id="roomType" name="roomType" required>
                                        <option value="">Chọn loại phòng</option>
                                        <option value="Superior Room" <?php echo ($booking['RoomType'] == 'Superior Room') ? 'selected' : ''; ?>>Superior Room</option>
                                        <option value="Deluxe Room" <?php echo ($booking['RoomType'] == 'Deluxe Room') ? 'selected' : ''; ?>>Deluxe Room</option>
                                        <option value="Guest House" <?php echo ($booking['RoomType'] == 'Guest House') ? 'selected' : ''; ?>>Guest House</option>
                                        <option value="Single Room" <?php echo ($booking['RoomType'] == 'Single Room') ? 'selected' : ''; ?>>Single Room</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="bed" class="form-label">Loại giường *</label>
                                    <select class="form-select" id="bed" name="bed" required>
                                        <option value="">Chọn loại giường</option>
                                        <option value="Single" <?php echo ($booking['Bed'] == 'Single') ? 'selected' : ''; ?>>Single</option>
                                        <option value="Double" <?php echo ($booking['Bed'] == 'Double') ? 'selected' : ''; ?>>Double</option>
                                        <option value="Triple" <?php echo ($booking['Bed'] == 'Triple') ? 'selected' : ''; ?>>Triple</option>
                                        <option value="Quad" <?php echo ($booking['Bed'] == 'Quad') ? 'selected' : ''; ?>>Quad</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="meal" class="form-label">Dịch vụ ăn uống *</label>
                                    <select class="form-select" id="meal" name="meal" required>
                                        <option value="">Chọn dịch vụ</option>
                                        <option value="Room only" <?php echo ($booking['Meal'] == 'Room only') ? 'selected' : ''; ?>>Room only</option>
                                        <option value="Breakfast" <?php echo ($booking['Meal'] == 'Breakfast') ? 'selected' : ''; ?>>Breakfast</option>
                                        <option value="Half Board" <?php echo ($booking['Meal'] == 'Half Board') ? 'selected' : ''; ?>>Half Board</option>
                                        <option value="Full Board" <?php echo ($booking['Meal'] == 'Full Board') ? 'selected' : ''; ?>>Full Board</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="nodays" class="form-label">Số ngày *</label>
                                    <input type="number" class="form-control" id="nodays" name="nodays" value="<?php echo $booking['nodays']; ?>" min="1" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="cin" class="form-label">Ngày check-in *</label>
                                    <input type="date" class="form-control" id="cin" name="cin" value="<?php echo $booking['cin']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="cout" class="form-label">Ngày check-out *</label>
                                    <input type="date" class="form-control" id="cout" name="cout" value="<?php echo $booking['cout']; ?>" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Trạng thái</label>
                                <input type="text" class="form-control" value="<?php echo ($booking['stat'] == 'Confirm') ? 'Đã xác nhận' : 'Đang chờ'; ?>" readonly>
                                <small class="text-muted">Trạng thái không thể sửa</small>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="dashboard.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Quay lại
                                </a>
                                <button type="submit" class="btn btn-custom">
                                    <i class="fas fa-save"></i> Cập nhật
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validation cho ngày
        document.getElementById('cin').addEventListener('change', function() {
            const cin = this.value;
            const cout = document.getElementById('cout').value;
            
            if (cout && cin >= cout) {
                alert('Ngày check-in phải trước ngày check-out!');
                this.value = '';
            }
        });

        document.getElementById('cout').addEventListener('change', function() {
            const cin = document.getElementById('cin').value;
            const cout = this.value;
            
            if (cin && cout <= cin) {
                alert('Ngày check-out phải sau ngày check-in!');
                this.value = '';
            }
        });
    </script>
</body>
</html>
