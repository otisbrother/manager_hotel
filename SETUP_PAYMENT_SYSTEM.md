# Hướng dẫn Setup Hệ thống Thanh toán Tự động

## 1. Tạo bảng payment_confirmations

Chạy lệnh SQL sau trong phpMyAdmin hoặc MySQL command line:

```sql
-- Tạo bảng payment_confirmations để lưu yêu cầu xác nhận thanh toán
CREATE TABLE IF NOT EXISTS `payment_confirmations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `status` enum('pending','confirmed','rejected') NOT NULL DEFAULT 'pending',
  `admin_notes` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `booking_id` (`booking_id`),
  KEY `status` (`status`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

## 2. Thêm link vào menu admin

Thêm link sau vào file `admin/menu.php` trong phần navigation:

```php
<a class="nav-link" href="payment_confirmations.php">
    <i class="fas fa-check-circle"></i>
    Xác nhận thanh toán
</a>
```

## 3. Cách hoạt động

### Quy trình thanh toán:

1. **User đặt phòng** → Trạng thái: "NotConfirm"
2. **Admin xác nhận** → Trạng thái: "Confirm" 
3. **User chọn thanh toán** → Hiển thị VietQR/Bank transfer
4. **User chuyển khoản** → Bấm nút "Đã thanh toán"
5. **Hệ thống gửi yêu cầu** → Trạng thái: "Pending Payment"
6. **Admin kiểm tra** → Vào trang "Xác nhận thanh toán"
7. **Admin xác nhận** → Trạng thái: "Paid" + Dữ liệu đổ về bảng payment

### Các trạng thái đặt phòng:

- `NotConfirm`: Chờ admin xác nhận
- `Confirm`: Đã xác nhận, có thể thanh toán
- `Pending Payment`: Đã gửi yêu cầu xác nhận thanh toán
- `Paid`: Đã thanh toán thành công

## 4. Tính năng chính

### Cho User:
- ✅ Chọn phương thức thanh toán (VietQR, Bank transfer, Credit card, Cash)
- ✅ Hiển thị mã QR VietQR với thông tin đầy đủ
- ✅ Bấm nút "Đã thanh toán" để gửi yêu cầu xác nhận
- ✅ Nhận thông báo khi admin xác nhận

### Cho Admin:
- ✅ Xem danh sách yêu cầu xác nhận thanh toán
- ✅ Xác nhận hoặc từ chối thanh toán
- ✅ Dữ liệu tự động đổ về bảng payment khi xác nhận
- ✅ Gửi email thông báo cho khách hàng

## 5. Files đã tạo/cập nhật:

- ✅ `payment.php` - Trang thanh toán cho user
- ✅ `payment_confirmation.php` - API xử lý yêu cầu xác nhận
- ✅ `admin/payment_confirmations.php` - Trang admin xác nhận thanh toán
- ✅ `create_payment_confirmations_table.sql` - SQL tạo bảng
- ✅ `payment_webhook.php` - Webhook cho thanh toán tự động (nếu có)

## 6. Lưu ý quan trọng:

1. **Email admin**: Thay đổi email admin trong file `payment_confirmation.php` dòng cuối
2. **Thông tin ngân hàng**: Cập nhật thông tin tài khoản trong `payment.php`
3. **Bảo mật**: Hệ thống này là bán tự động, admin vẫn cần kiểm tra thủ công
4. **Database**: Đảm bảo bảng `payment_confirmations` đã được tạo

## 7. Test hệ thống:

1. Đăng nhập user → Đặt phòng
2. Admin xác nhận đặt phòng
3. User vào trang thanh toán → Chọn VietQR
4. User bấm "Đã thanh toán"
5. Admin vào trang "Xác nhận thanh toán" → Xác nhận
6. Kiểm tra dữ liệu trong bảng `payment`
