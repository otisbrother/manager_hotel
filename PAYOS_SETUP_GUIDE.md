# Hướng dẫn Setup PayOS cho Thanh toán Tự động

## 1. Đăng ký tài khoản PayOS

1. Truy cập: https://payos.vn/
2. Đăng ký tài khoản merchant
3. Xác thực tài khoản ngân hàng
4. Lấy thông tin API credentials

## 2. Cập nhật thông tin PayOS

Thay đổi thông tin trong file `create_payos_qr.php`:

```php
$clientId = 'your_payos_client_id';     // Thay bằng Client ID từ PayOS
$apiKey = 'your_payos_api_key';         // Thay bằng API Key từ PayOS  
$checksum = 'your_payos_checksum';      // Thay bằng Checksum từ PayOS
```

## 3. Cập nhật URL

Thay đổi các URL trong file `create_payos_qr.php`:

```php
$callbackUrl = 'https://yourdomain.com/webhook_payment.php';
$returnUrl = 'https://yourdomain.com/payment_success.php?booking_id=' . $booking['id'];
$cancelUrl = 'https://yourdomain.com/payment_cancel.php';
```

## 4. Tạo bảng database

Chạy SQL trong file `create_payment_requests_table.sql`:

```sql
CREATE TABLE IF NOT EXISTS `payment_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_code` varchar(255) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('pending','paid','failed','cancelled') NOT NULL DEFAULT 'pending',
  `transaction_id` varchar(255),
  `payment_url` text,
  `qr_code` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_code` (`order_code`),
  KEY `booking_id` (`booking_id`),
  KEY `status` (`status`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

## 5. Cấu hình Webhook trong PayOS Dashboard

1. Đăng nhập PayOS Dashboard
2. Vào phần "Cài đặt" → "Webhook"
3. Thêm webhook URL: `https://yourdomain.com/webhook_payment.php`
4. Chọn các event: `PAYMENT_SUCCESS`, `PAYMENT_FAILED`

## 6. Quy trình hoạt động

### Khi user chọn thanh toán:

1. **User chọn VietQR** → Hệ thống gọi PayOS API
2. **PayOS tạo QR code** → Hiển thị cho user
3. **User quét QR** → Chuyển khoản qua app ngân hàng
4. **PayOS nhận tiền** → Gửi webhook về server
5. **Webhook xử lý** → Cập nhật trạng thái "Paid"
6. **User nhận thông báo** → "Đặt phòng thành công!"

### Các trạng thái:

- `pending`: Đang chờ thanh toán
- `paid`: Đã thanh toán thành công
- `failed`: Thanh toán thất bại
- `cancelled`: Đã hủy thanh toán

## 7. Files đã tạo:

- ✅ `webhook_payment.php` - Webhook nhận thông báo từ PayOS
- ✅ `create_payos_qr.php` - Tạo QR code qua PayOS API
- ✅ `check_payment_status.php` - Kiểm tra trạng thái thanh toán
- ✅ `create_payment_requests_table.sql` - SQL tạo bảng
- ✅ `payment.php` - Đã cập nhật để tích hợp PayOS

## 8. Test hệ thống:

1. **Setup PayOS** → Cập nhật credentials
2. **Tạo bảng** → Chạy SQL
3. **Test thanh toán** → Chọn VietQR → Quét QR → Thanh toán
4. **Kiểm tra webhook** → Xem log để đảm bảo nhận được thông báo
5. **Kiểm tra database** → Xem trạng thái đã cập nhật chưa

## 9. Lưu ý quan trọng:

1. **HTTPS bắt buộc** → PayOS chỉ gửi webhook qua HTTPS
2. **Domain thật** → Không dùng localhost cho production
3. **API credentials** → Bảo mật thông tin API
4. **Error handling** → Xử lý lỗi khi API không hoạt động
5. **Logging** → Ghi log để debug

## 10. Troubleshooting:

### Webhook không nhận được:
- Kiểm tra URL webhook trong PayOS Dashboard
- Kiểm tra HTTPS certificate
- Xem log error trong server

### QR code không hiển thị:
- Kiểm tra API credentials
- Kiểm tra network connection
- Xem response từ PayOS API

### Thanh toán không cập nhật:
- Kiểm tra webhook signature
- Kiểm tra database connection
- Xem log trong webhook_payment.php
