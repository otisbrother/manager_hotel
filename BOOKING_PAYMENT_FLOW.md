# Cập nhật Luồng Đặt phòng và Thanh toán

## Tổng quan
Đã cập nhật luồng hoạt động từ đặt phòng đến thanh toán để người dùng có thể chuyển hướng trực tiếp từ trang đặt phòng đến trang thanh toán.

## Thay đổi chính

### 1. File `booking.php`

#### A. Cập nhật chuyển hướng sau đặt phòng
- **Thay đổi từ**: `redirect("dashboard.php")`
- **Thay đổi thành**: `redirect("payment.php?booking_id=" . $booking_id)`
- **Mục đích**: Sau khi đặt phòng thành công, người dùng được chuyển hướng trực tiếp đến trang thanh toán

#### B. Luồng hoạt động mới
1. **Người dùng điền form đặt phòng**
2. **Nhấn "Tiến hành thanh toán"**
3. **Hệ thống lưu booking vào database** với trạng thái `'NotConfirm'`
4. **Chuyển hướng đến trang thanh toán** với booking_id
5. **Hiển thị thông báo** về việc chờ admin xác nhận

### 2. File `payment.php`

#### A. Cập nhật kiểm tra trạng thái booking
- **Thêm kiểm tra cho `'NotConfirm'`**: Hiển thị thông báo phù hợp khi booking chưa được xác nhận
- **Cải thiện thông báo lỗi**: Phân biệt rõ ràng giữa các trạng thái khác nhau

#### B. Thêm hiển thị thông báo thành công
- **Hiển thị `$_SESSION['success']`**: Thông báo từ booking.php
- **Alert có thể đóng**: Sử dụng Bootstrap alert với nút đóng

## Chi tiết kỹ thuật

### 1. Chuyển hướng trong booking.php:
```php
$booking_id = $conn->lastInsertId();
$_SESSION['success'] = 'Đặt phòng thành công! Vui lòng chờ admin xác nhận để thanh toán.';
redirect("payment.php?booking_id=" . $booking_id);
```

### 2. Kiểm tra trạng thái trong payment.php:
```php
if ($booking['stat'] === 'NotConfirm') {
    $_SESSION['error'] = 'Đặt phòng của bạn đang chờ admin xác nhận. Vui lòng quay lại sau!';
    redirect('dashboard.php');
} elseif ($booking['stat'] !== 'Confirm') {
    $_SESSION['error'] = 'Chỉ có thể thanh toán khi đặt phòng đã được admin xác nhận!';
    redirect('dashboard.php');
}
```

### 3. Hiển thị thông báo thành công:
```php
<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>
```

## Luồng hoạt động mới

### 1. Đặt phòng mới:
1. **Người dùng điền form đặt phòng**
2. **Nhấn "Tiến hành thanh toán"**
3. **Hệ thống lưu booking** với trạng thái `'NotConfirm'`
4. **Chuyển hướng đến payment.php** với thông báo thành công
5. **Hiển thị thông báo** "Đặt phòng của bạn đang chờ admin xác nhận"
6. **Chuyển hướng về dashboard** với thông báo lỗi

### 2. Thanh toán booking đã xác nhận:
1. **Người dùng vào payment.php** với booking_id đã được confirm
2. **Hiển thị form thanh toán** với tóm tắt booking
3. **Chọn phương thức thanh toán**
4. **Nhấn "Đặt phòng"** để xác nhận thanh toán
5. **Hiển thị popup thành công** và lưu vào database

## Lợi ích

1. **Trải nghiệm người dùng tốt hơn**: Chuyển hướng trực tiếp từ đặt phòng đến thanh toán
2. **Thông báo rõ ràng**: Người dùng biết chính xác trạng thái booking
3. **Luồng hoạt động logic**: Đặt phòng → Chờ xác nhận → Thanh toán
4. **Bảo mật**: Kiểm tra trạng thái trước khi cho phép thanh toán

## Kiểm tra

### Để kiểm tra chức năng:

#### 1. Đặt phòng mới:
1. **Vào trang đặt phòng** (`booking.php`)
2. **Điền form đầy đủ** và nhấn "Tiến hành thanh toán"
3. **Quan sát chuyển hướng** đến `payment.php?booking_id=XX`
4. **Kiểm tra thông báo** "Đặt phòng của bạn đang chờ admin xác nhận"
5. **Xác nhận chuyển hướng** về dashboard

#### 2. Thanh toán booking đã xác nhận:
1. **Admin xác nhận booking** trong admin panel
2. **Người dùng vào payment.php** với booking đã confirm
3. **Kiểm tra hiển thị form thanh toán** bình thường
4. **Thực hiện thanh toán** và kiểm tra popup thành công

## Lưu ý

- Booking mới có trạng thái `'NotConfirm'` sẽ không thể thanh toán
- Chỉ booking có trạng thái `'Confirm'` mới có thể thanh toán
- Thông báo thành công từ booking.php sẽ hiển thị trong payment.php
- Luồng hoạt động đảm bảo tính bảo mật và logic


