<?php
require_once '../config.php';
require_once '../auth.php';
require_once '../middleware.php';

// Lấy menu dựa trên quyền
$menu = $middleware->getMenuByPermissions();
$currentUser = $auth->getCurrentUser();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Menu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 15px 20px;
            border-radius: 10px;
            margin: 5px 10px;
            transition: all 0.3s;
        }
        
        .sidebar .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateX(5px);
        }
        
        .sidebar .nav-link.active {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }
        
        .sidebar .nav-link i {
            margin-right: 10px;
            width: 20px;
        }
        
        .user-info {
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 20px;
        }
        
        .user-info .avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
        }
        
        .main-content {
            padding: 20px;
        }
        
        .welcome-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        
        .stats-card .icon {
            font-size: 2rem;
            color: #667eea;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar">
                <div class="user-info">
                    <div class="avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <h6><?php echo $currentUser['name']; ?></h6>
                    <small><?php echo $currentUser['email']; ?></small>
                    <br>
                    <small class="text-muted">Role: <?php echo $_SESSION['role_name'] ?? 'User'; ?></small>
                </div>
                
                <nav class="nav flex-column">
                    <?php foreach ($menu as $item): ?>
                        <?php if ($item['name'] !== 'Đăng xuất'): ?>
                            <a class="nav-link" href="<?php echo $item['url']; ?>">
                                <i class="<?php echo $item['icon']; ?>"></i>
                                <?php echo $item['name']; ?>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    
                    <hr style="border-color: rgba(255, 255, 255, 0.2);">
                    
                    <a class="nav-link text-danger" href="../logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                        Đăng xuất
                    </a>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="welcome-card">
                    <h2>Chào mừng, <?php echo $currentUser['name']; ?>!</h2>
                    <p>Bạn đang sử dụng hệ thống quản lý khách sạn BlueBird</p>
                </div>
                
                <div class="row">
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="d-flex align-items-center">
                                <div class="icon me-3">
                                    <i class="fas fa-bed"></i>
                                </div>
                                <div>
                                    <h4>150</h4>
                                    <small class="text-muted">Phòng</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="d-flex align-items-center">
                                <div class="icon me-3">
                                    <i class="fas fa-calendar-check"></i>
                                </div>
                                <div>
                                    <h4>45</h4>
                                    <small class="text-muted">Đặt phòng hôm nay</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="d-flex align-items-center">
                                <div class="icon me-3">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div>
                                    <h4>1,250</h4>
                                    <small class="text-muted">Khách hàng</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="d-flex align-items-center">
                                <div class="icon me-3">
                                    <i class="fas fa-dollar-sign"></i>
                                </div>
                                <div>
                                    <h4>$25,000</h4>
                                    <small class="text-muted">Doanh thu tháng</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="stats-card">
                            <h5>Quyền hạn hiện tại</h5>
                            <div class="mt-3">
                                <?php if (isset($_SESSION['permissions'])): ?>
                                    <?php foreach (array_slice($_SESSION['permissions'], 0, 10) as $permission): ?>
                                        <span class="badge bg-primary me-2 mb-2"><?php echo $permission; ?></span>
                                    <?php endforeach; ?>
                                    <?php if (count($_SESSION['permissions']) > 10): ?>
                                        <span class="badge bg-secondary">+<?php echo count($_SESSION['permissions']) - 10; ?> more</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">Không có quyền đặc biệt</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="stats-card">
                            <h5>Hoạt động gần đây</h5>
                            <div class="mt-3">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-clock text-muted me-2"></i>
                                    <small>Đăng nhập lúc <?php echo date('H:i'); ?></small>
                                </div>
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-user text-muted me-2"></i>
                                    <small>Phiên làm việc: <?php echo round((time() - $_SESSION['login_time']) / 60); ?> phút</small>
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