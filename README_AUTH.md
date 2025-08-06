# Hệ thống phân quyền BlueBird Hotel

## Tổng quan
Hệ thống phân quyền mới được thiết kế để bảo mật và quản lý quyền truy cập trong hệ thống quản lý khách sạn BlueBird.

## Các file chính

### 1. `auth.php`
- Xử lý đăng nhập/đăng xuất
- Quản lý session
- Kiểm tra quyền hạn

### 2. `permissions.php`
- Định nghĩa các quyền hạn
- Quản lý role và permission

### 3. `middleware.php`
- Kiểm tra quyền truy cập
- Bảo vệ các trang
- Quản lý menu động

## Cách sử dụng

### Đăng nhập
```php
// Đăng nhập user
$result = $auth->login('email@example.com', 'password', 'user');

// Đăng nhập staff/admin
$result = $auth->login('admin@example.com', 'password', 'staff');
```

### Kiểm tra quyền
```php
// Kiểm tra đăng nhập
if ($auth->isLoggedIn()) {
    // User đã đăng nhập
}

// Kiểm tra quyền cụ thể
if ($auth->hasPermission('room_create')) {
    // Có quyền tạo phòng
}

// Kiểm tra role
if ($auth->hasRole('admin')) {
    // Là admin
}
```

### Bảo vệ trang
```php
// Bảo vệ trang admin
$middleware->requireStaff();

// Bảo vệ trang user
$middleware->requireUser();

// Kiểm tra quyền cụ thể
$middleware->requirePermission('booking_view');
```

## Thông tin đăng nhập mặc định

### Admin
- Email: `Admin@gmail.com`
- Password: `1234`
- Role: Admin (toàn quyền)

### User
- Email: `tusharpankhaniya2202@gmail.com`
- Password: `123`
- Role: User (quyền cơ bản)

## Cấu trúc phân quyền

### Admin (Staff)
- Quản lý dashboard
- Quản lý phòng (xem, tạo, sửa, xóa)
- Quản lý đặt phòng (xem, tạo, sửa, xóa, xác nhận, hủy)
- Quản lý thanh toán (xem, tạo, sửa, xóa, hoàn tiền)
- Quản lý nhân viên (xem, tạo, sửa, xóa)
- Quản lý người dùng (xem, tạo, sửa, xóa)
- Xem báo cáo và xuất dữ liệu

### User
- Truy cập cơ bản
- Đặt phòng
- Xem lịch sử đặt phòng
- Thanh toán

## Menu động

Menu sẽ hiển thị dựa trên quyền hạn của user:

```php
$menu = $middleware->getMenuByPermissions();
foreach ($menu as $item) {
    echo $item['name'] . ' - ' . $item['url'];
}
```

## Session timeout

Hệ thống tự động đăng xuất sau 1 giờ không hoạt động:

```php
$middleware->checkSessionTimeout();
```

## Test hệ thống

Chạy file `test_auth.php` để kiểm tra:

```bash
http://localhost/your-project/test_auth.php
```

## Nâng cấp database

Để sử dụng hệ thống phân quyền đầy đủ, chạy file `update_database.sql` trong phpMyAdmin.

## Lưu ý bảo mật

1. **Mật khẩu**: Nên sử dụng password hash thay vì plain text
2. **Session**: Luôn kiểm tra session timeout
3. **Input validation**: Sanitize tất cả input từ user
4. **SQL Injection**: Sử dụng prepared statements
5. **XSS**: Escape output HTML

## Troubleshooting

### Lỗi đăng nhập
- Kiểm tra thông tin database
- Kiểm tra bảng `signup` và `emp_login`
- Kiểm tra session configuration

### Lỗi quyền truy cập
- Kiểm tra session permissions
- Kiểm tra role assignment
- Kiểm tra middleware configuration

### Lỗi menu không hiển thị
- Kiểm tra permissions array
- Kiểm tra menu configuration
- Kiểm tra user role

## Hỗ trợ

Nếu gặp vấn đề, vui lòng:
1. Kiểm tra error log
2. Test với file `test_auth.php`
3. Kiểm tra database connection
4. Kiểm tra file permissions 