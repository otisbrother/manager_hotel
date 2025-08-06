<?php
require_once '../config.php';
require_once '../auth.php';
require_once '../middleware.php';

// Kiểm tra đăng nhập và quyền truy cập admin
$middleware->requireStaff();

// Kiểm tra session timeout
$middleware->checkSessionTimeout();

// Lấy thông tin user hiện tại
$currentUser = $auth->getCurrentUser();

// Kiểm tra nếu user không phải staff
if ($currentUser['type'] == 'user') {
    header("Location: ../index.php");
    exit();
}
?> 