<?php
require_once 'config.php';

class Auth {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    // Đăng nhập
    public function login($email, $password, $role) {
        try {
            if ($role == 'user') {
                return $this->loginUser($email, $password);
            } else {
                return $this->loginStaff($email, $password);
            }
        } catch(PDOException $e) {
            return ['success' => false, 'message' => 'Lỗi đăng nhập: ' . $e->getMessage()];
        }
    }
    
    // Đăng nhập cho user
    private function loginUser($email, $password) {
        $stmt = $this->conn->prepare("SELECT * FROM signup WHERE Email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && $user['Password'] === $password) {
            $this->setSession($user['UserID'], $user['Email'], $user['Username'], 'user', 5);
            return ['success' => true, 'redirect' => 'dashboard.php'];
        }
        
        return ['success' => false, 'message' => 'Email hoặc mật khẩu không đúng!'];
    }
    
    // Đăng nhập cho staff/admin
    private function loginStaff($email, $password) {
        $stmt = $this->conn->prepare("SELECT * FROM emp_login WHERE Emp_Email = ? AND Emp_Password = ?");
        $stmt->execute([$email, $password]);
        $staff = $stmt->fetch();
        
        if ($staff) {
            $this->setSession($staff['empid'], $staff['Emp_Email'], 'Admin', 'staff', 1);
            $_SESSION['role_name'] = 'admin';
            $_SESSION['permissions'] = [
                'dashboard_view', 'dashboard_edit', 'room_view', 'room_create', 
                'room_edit', 'room_delete', 'booking_view', 'booking_create', 
                'booking_edit', 'booking_delete', 'booking_confirm', 'booking_cancel',
                'payment_view', 'payment_create', 'payment_edit', 'payment_delete', 
                'payment_refund', 'staff_view', 'staff_create', 'staff_edit', 
                'staff_delete', 'user_view', 'user_create', 'user_edit', 'user_delete',
                'report_view', 'report_export'
            ];
            return ['success' => true, 'redirect' => 'admin/admin.php'];
        }
        
        return ['success' => false, 'message' => 'Email hoặc mật khẩu không đúng!'];
    }
    
    // Thiết lập session
    private function setSession($id, $email, $name, $type, $role_id) {
        $_SESSION['user_id'] = $id;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_name'] = $name;
        $_SESSION['user_type'] = $type;
        $_SESSION['role_id'] = $role_id;
        $_SESSION['login_time'] = time();
    }
    
    // Đăng xuất
    public function logout() {
        session_destroy();
        return ['success' => true, 'redirect' => 'index.php'];
    }
    
    // Kiểm tra đăng nhập
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    // Kiểm tra quyền
    public function hasPermission($permission) {
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        if ($_SESSION['user_type'] == 'user') {
            return $permission == 'user_access';
        }
        
        if (isset($_SESSION['permissions'])) {
            return in_array($permission, $_SESSION['permissions']);
        }
        
        return false;
    }
    
    // Kiểm tra role
    public function hasRole($role) {
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        if ($_SESSION['user_type'] == 'user') {
            return $role == 'user';
        }
        
        return $_SESSION['role_name'] == $role;
    }
    
    // Lấy thông tin user hiện tại
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['user_id'],
            'email' => $_SESSION['user_email'],
            'name' => $_SESSION['user_name'],
            'type' => $_SESSION['user_type'],
            'role_id' => $_SESSION['role_id']
        ];
    }
    
    // Tạo mật khẩu hash
    public function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    
    // Kiểm tra mật khẩu
    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
}

// Khởi tạo Auth instance
$auth = new Auth($conn);
?> 