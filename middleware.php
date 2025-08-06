<?php
require_once 'auth.php';
require_once 'permissions.php';

class Middleware {
    private $auth;
    
    public function __construct($auth) {
        $this->auth = $auth;
    }
    
    // Kiểm tra đăng nhập
    public function requireLogin($redirect = 'index.php') {
        if (!$this->auth->isLoggedIn()) {
            header("Location: $redirect");
            exit();
        }
    }
    
    // Kiểm tra quyền
    public function requirePermission($permission, $redirect = 'index.php') {
        $this->requireLogin($redirect);
        
        if (!$this->auth->hasPermission($permission)) {
            $this->showAccessDenied();
        }
    }
    
    // Kiểm tra role
    public function requireRole($role, $redirect = 'index.php') {
        $this->requireLogin($redirect);
        
        if (!$this->auth->hasRole($role)) {
            $this->showAccessDenied();
        }
    }
    
    // Kiểm tra quyền cho admin
    public function requireAdmin($redirect = 'index.php') {
        $this->requireLogin($redirect);
        
        if (!$this->auth->hasRole('admin') && !$this->auth->hasRole('super_admin')) {
            $this->showAccessDenied();
        }
    }
    
    // Kiểm tra quyền cho staff
    public function requireStaff($redirect = 'index.php') {
        $this->requireLogin($redirect);
        
        if ($this->auth->getCurrentUser()['type'] == 'user') {
            $this->showAccessDenied();
        }
    }
    
    // Kiểm tra quyền cho user
    public function requireUser($redirect = 'index.php') {
        $this->requireLogin($redirect);
        
        if ($this->auth->getCurrentUser()['type'] != 'user') {
            $this->showAccessDenied();
        }
    }
    
    // Hiển thị trang từ chối truy cập
    private function showAccessDenied() {
        http_response_code(403);
        ?>
        <!DOCTYPE html>
        <html lang="vi">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Không có quyền truy cập</title>
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
                
                .error-container {
                    background: rgba(255, 255, 255, 0.95);
                    border-radius: 20px;
                    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
                    padding: 50px;
                    text-align: center;
                    max-width: 500px;
                }
                
                .error-icon {
                    font-size: 5rem;
                    color: #dc3545;
                    margin-bottom: 20px;
                }
                
                .error-title {
                    color: #333;
                    font-weight: 700;
                    margin-bottom: 15px;
                }
                
                .error-message {
                    color: #666;
                    margin-bottom: 30px;
                }
                
                .btn-back {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    border: none;
                    border-radius: 10px;
                    padding: 12px 30px;
                    color: white;
                    text-decoration: none;
                    font-weight: 600;
                    transition: all 0.3s;
                }
                
                .btn-back:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
                    color: white;
                }
            </style>
        </head>
        <body>
            <div class="error-container">
                <div class="error-icon">
                    <i class="fas fa-ban"></i>
                </div>
                <h1 class="error-title">Không có quyền truy cập</h1>
                <p class="error-message">
                    Bạn không có quyền truy cập vào trang này. 
                    Vui lòng liên hệ quản trị viên nếu bạn cần quyền truy cập.
                </p>
                <a href="javascript:history.back()" class="btn btn-back">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
            </div>
        </body>
        </html>
        <?php
        exit();
    }
    
    // Kiểm tra session timeout
    public function checkSessionTimeout($timeout = 3600) { // 1 giờ
        if ($this->auth->isLoggedIn()) {
            $loginTime = $_SESSION['login_time'] ?? 0;
            if (time() - $loginTime > $timeout) {
                $this->auth->logout();
                header("Location: index.php?timeout=1");
                exit();
            }
            // Cập nhật thời gian đăng nhập
            $_SESSION['login_time'] = time();
        }
    }
    
    // Lấy menu dựa trên quyền
    public function getMenuByPermissions() {
        $user = $this->auth->getCurrentUser();
        if (!$user) return [];
        
        $menu = [];
        
        if ($user['type'] == 'user') {
            $menu = [
                ['name' => 'Trang chủ', 'url' => 'dashboard.php', 'icon' => 'fas fa-home'],
                ['name' => 'Đặt phòng', 'url' => 'booking.php', 'icon' => 'fas fa-bed'],
                ['name' => 'Lịch sử đặt phòng', 'url' => 'my-bookings.php', 'icon' => 'fas fa-history'],
                ['name' => 'Thanh toán', 'url' => 'payment.php', 'icon' => 'fas fa-credit-card'],
                ['name' => 'Đăng xuất', 'url' => 'logout.php', 'icon' => 'fas fa-sign-out-alt']
            ];
        } else {
            // Menu cho staff/admin
            if ($this->auth->hasPermission(Permissions::DASHBOARD_VIEW)) {
                $menu[] = ['name' => 'Dashboard', 'url' => 'admin/dashboard.php', 'icon' => 'fas fa-tachometer-alt'];
            }
            
            if ($this->auth->hasPermission(Permissions::BOOKING_VIEW)) {
                $menu[] = ['name' => 'Quản lý đặt phòng', 'url' => 'admin/roombook.php', 'icon' => 'fas fa-bed'];
            }
            
            if ($this->auth->hasPermission(Permissions::PAYMENT_VIEW)) {
                $menu[] = ['name' => 'Quản lý thanh toán', 'url' => 'admin/payment.php', 'icon' => 'fas fa-credit-card'];
            }
            
            if ($this->auth->hasPermission(Permissions::ROOM_VIEW)) {
                $menu[] = ['name' => 'Quản lý phòng', 'url' => 'admin/room.php', 'icon' => 'fas fa-door-open'];
            }
            
            if ($this->auth->hasPermission(Permissions::STAFF_VIEW)) {
                $menu[] = ['name' => 'Quản lý nhân viên', 'url' => 'admin/staff.php', 'icon' => 'fas fa-users'];
            }
            
            if ($this->auth->hasPermission(Permissions::USER_VIEW)) {
                $menu[] = ['name' => 'Quản lý người dùng', 'url' => 'admin/users.php', 'icon' => 'fas fa-user-friends'];
            }
            
            if ($this->auth->hasPermission(Permissions::REPORT_VIEW)) {
                $menu[] = ['name' => 'Báo cáo', 'url' => 'admin/reports.php', 'icon' => 'fas fa-chart-bar'];
            }
            
            if ($this->auth->hasPermission(Permissions::SYSTEM_SETTINGS)) {
                $menu[] = ['name' => 'Cài đặt hệ thống', 'url' => 'admin/settings.php', 'icon' => 'fas fa-cog'];
            }
            
            $menu[] = ['name' => 'Đăng xuất', 'url' => 'logout.php', 'icon' => 'fas fa-sign-out-alt'];
        }
        
        return $menu;
    }
}

// Khởi tạo Middleware instance
$middleware = new Middleware($auth);
?> 