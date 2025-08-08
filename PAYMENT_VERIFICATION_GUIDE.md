# Hướng dẫn Xác minh Thanh toán Tự động

## Tổng quan
Để biết chính xác khi nào người dùng đã chuyển khoản thành công, chúng ta có 3 giải pháp chính:

## Giải pháp 1: Webhook từ Ngân hàng (Khuyến nghị)

### Ưu điểm:
- Tự động và real-time
- Độ tin cậy cao
- Không cần can thiệp từ người dùng

### Cách triển khai:
1. **Đăng ký webhook với ngân hàng:**
   - Liên hệ ngân hàng để đăng ký webhook
   - Cung cấp URL: `https://yourdomain.com/payment_webhook.php`
   - Nhận API key và secret

2. **Cấu hình webhook:**
   ```php
   // Trong payment_webhook.php
   function verifyWebhookSignature($data) {
       $secret = 'your_webhook_secret';
       $signature = $_SERVER['HTTP_X_SIGNATURE'] ?? '';
       $expectedSignature = hash_hmac('sha256', json_encode($data), $secret);
       return hash_equals($expectedSignature, $signature);
   }
   ```

3. **Xử lý thông báo thanh toán:**
   - Ngân hàng gửi POST request đến webhook
   - Hệ thống xác thực và xử lý
   - Tự động cập nhật trạng thái và gửi email

### Ngân hàng hỗ trợ:
- **MBBank:** Có API và webhook
- **Vietcombank:** Có API cho doanh nghiệp
- **BIDV:** Có API tích hợp
- **Techcombank:** Có API cho merchant

## Giải pháp 2: API Kiểm tra Định kỳ

### Ưu điểm:
- Không cần đăng ký với ngân hàng
- Có thể tích hợp với nhiều ngân hàng
- Kiểm soát được thời gian

### Cách triển khai:
1. **Tích hợp API ngân hàng:**
   ```php
   // Ví dụ với MBBank API
   $apiUrl = "https://api.mbbank.com.vn/transaction/check";
   $data = [
       'account_number' => '0395256163',
       'amount' => $amount,
       'content' => "Thanh toan booking $bookingId",
       'from_date' => date('Y-m-d', strtotime('-1 day')),
       'to_date' => date('Y-m-d')
   ];
   ```

2. **Kiểm tra định kỳ:**
   - Mỗi 30 giây kiểm tra một lần
   - So sánh nội dung và số tiền
   - Xử lý khi tìm thấy giao dịch khớp

3. **Xử lý kết quả:**
   - Cập nhật trạng thái đặt phòng
   - Ghi dữ liệu vào bảng payment
   - Gửi email thông báo

## Giải pháp 3: Kết hợp Webhook + API

### Ưu điểm:
- Độ tin cậy cao nhất
- Backup khi webhook không hoạt động
- Phù hợp với môi trường production

### Cách triển khai:
1. **Webhook làm phương thức chính**
2. **API kiểm tra làm backup**
3. **Cron job chạy mỗi 5 phút**

## Cấu hình cho từng ngân hàng

### MBBank:
```php
$config = [
    'api_url' => 'https://api.mbbank.com.vn',
    'account_number' => '0395256163',
    'api_key' => 'your_mbbank_api_key',
    'webhook_secret' => 'your_webhook_secret'
];
```

### Vietcombank:
```php
$config = [
    'api_url' => 'https://api.vietcombank.com.vn',
    'account_number' => '1234567890',
    'api_key' => 'your_vietcombank_api_key',
    'webhook_secret' => 'your_webhook_secret'
];
```

## Bảo mật

### 1. Xác thực Webhook:
```php
function verifyWebhookSignature($data, $secret) {
    $signature = $_SERVER['HTTP_X_SIGNATURE'] ?? '';
    $expectedSignature = hash_hmac('sha256', json_encode($data), $secret);
    return hash_equals($expectedSignature, $signature);
}
```

### 2. Mã hóa API Key:
```php
// Lưu trong file config riêng
define('MBBANK_API_KEY', 'your_encrypted_api_key');
define('VIETCOMBANK_API_KEY', 'your_encrypted_api_key');
```

### 3. Rate Limiting:
```php
// Giới hạn số lần gọi API
if (checkRateLimit($ip, 'api_call', 100, 3600)) {
    // Cho phép gọi API
} else {
    http_response_code(429);
    exit('Too many requests');
}
```

## Xử lý lỗi

### 1. Webhook không hoạt động:
- Fallback về API kiểm tra
- Gửi email cảnh báo cho admin
- Log lỗi để debug

### 2. API không trả về dữ liệu:
- Thử lại sau 1 phút
- Gửi thông báo cho người dùng
- Cho phép xác nhận thủ công

### 3. Giao dịch trùng lặp:
- Kiểm tra transaction_id
- Tránh xử lý nhiều lần
- Log để theo dõi

## Monitoring và Logging

### 1. Log các sự kiện:
```php
error_log("Payment webhook received: " . json_encode($data));
error_log("Payment confirmed for booking ID: $bookingId");
error_log("API check failed: " . $error);
```

### 2. Monitoring:
- Số lượng webhook nhận được
- Tỷ lệ thành công của API calls
- Thời gian phản hồi trung bình

### 3. Alerting:
- Email khi webhook không hoạt động
- SMS cho admin khi có lỗi nghiêm trọng
- Dashboard để theo dõi real-time

## Triển khai Production

### 1. SSL Certificate:
- Bắt buộc cho webhook
- HTTPS cho tất cả API calls

### 2. Database Indexing:
```sql
-- Index cho bảng payment
CREATE INDEX idx_booking_id ON payment(booking_id);
CREATE INDEX idx_transaction_id ON payment(transaction_id);
CREATE INDEX idx_payment_date ON payment(created_at);
```

### 3. Caching:
```php
// Cache kết quả API check
$cacheKey = "payment_check_{$bookingId}_{$paymentMethod}";
if ($cached = cache_get($cacheKey)) {
    return $cached;
}
```

## Kết luận

**Khuyến nghị sử dụng Giải pháp 1 (Webhook)** vì:
- Tự động và real-time
- Độ tin cậy cao
- Ít tốn tài nguyên server
- Trải nghiệm người dùng tốt nhất

**Backup với Giải pháp 2 (API)** để đảm bảo không bỏ sót giao dịch nào.


