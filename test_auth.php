<?php
require_once 'config.php';
require_once 'auth.php';
require_once 'middleware.php';

// Test đăng nhập
echo "<h2>Test hệ thống phân quyền</h2>";

// Test 1: Đăng nhập Admin
echo "<h3>Test 1: Đăng nhập Admin</h3>";
$result = $auth->login('Admin@gmail.com', '1234', 'staff');
echo "Kết quả: " . ($result['success'] ? 'Thành công' : 'Thất bại') . "<br>";
echo "Message: " . $result['message'] . "<br>";
if ($result['success']) {
    echo "Redirect: " . $result['redirect'] . "<br>";
    echo "User ID: " . $_SESSION['user_id'] . "<br>";
    echo "User Type: " . $_SESSION['user_type'] . "<br>";
    echo "Role Name: " . $_SESSION['role_name'] . "<br>";
    echo "Permissions: " . implode(', ', $_SESSION['permissions']) . "<br>";
}

// Test 2: Đăng nhập User
echo "<h3>Test 2: Đăng nhập User</h3>";
$result = $auth->login('tusharpankhaniya2202@gmail.com', '123', 'user');
echo "Kết quả: " . ($result['success'] ? 'Thành công' : 'Thất bại') . "<br>";
echo "Message: " . $result['message'] . "<br>";
if ($result['success']) {
    echo "Redirect: " . $result['redirect'] . "<br>";
    echo "User ID: " . $_SESSION['user_id'] . "<br>";
    echo "User Type: " . $_SESSION['user_type'] . "<br>";
}

// Test 3: Kiểm tra quyền
echo "<h3>Test 3: Kiểm tra quyền</h3>";
if ($auth->isLoggedIn()) {
    echo "User đã đăng nhập<br>";
    echo "Có quyền dashboard_view: " . ($auth->hasPermission('dashboard_view') ? 'Có' : 'Không') . "<br>";
    echo "Có quyền room_create: " . ($auth->hasPermission('room_create') ? 'Có' : 'Không') . "<br>";
    echo "Có quyền user_access: " . ($auth->hasPermission('user_access') ? 'Có' : 'Không') . "<br>";
    echo "Có role admin: " . ($auth->hasRole('admin') ? 'Có' : 'Không') . "<br>";
} else {
    echo "User chưa đăng nhập<br>";
}

// Test 4: Menu theo quyền
echo "<h3>Test 4: Menu theo quyền</h3>";
$menu = $middleware->getMenuByPermissions();
echo "Menu items:<br>";
foreach ($menu as $item) {
    echo "- " . $item['name'] . " (" . $item['url'] . ")<br>";
}

// Test 5: Session timeout
echo "<h3>Test 5: Session timeout</h3>";
$middleware->checkSessionTimeout();
echo "Session timeout checked<br>";

// Test 6: Đăng xuất
echo "<h3>Test 6: Đăng xuất</h3>";
$result = $auth->logout();
echo "Logout result: " . ($result['success'] ? 'Thành công' : 'Thất bại') . "<br>";
echo "Redirect: " . $result['redirect'] . "<br>";

echo "<h3>Test hoàn tất!</h3>";
?> 