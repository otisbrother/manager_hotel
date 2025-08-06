# 🏨 BlueBird Hotel Management System

[![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3.0-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)](https://getbootstrap.com)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)

> Hệ thống quản lý khách sạn hoàn chỉnh với giao diện hiện đại, tích hợp thanh toán VietQR và responsive design.

## ✨ Tính năng chính

### 👥 Quản lý người dùng
- Đăng ký/Đăng nhập với validation
- Phân quyền người dùng (User/Admin)
- Quản lý hồ sơ cá nhân
- Session management bảo mật

### 🏠 Đặt phòng & Quản lý
- **Đặt phòng trực tuyến** với tính toán giá tự động
- **Gallery phòng** với hình ảnh chất lượng cao
- **Quản lý loại phòng** (Superior, Deluxe, Guest House)
- **Quản lý loại giường** (Single, Double, Triple, Quad)
- **Lịch sử đặt phòng** chi tiết

### 💰 Hệ thống thanh toán
- **VietQR Integration** với mã QR động
- **Đa dạng phương thức**: Thẻ tín dụng, chuyển khoản, tiền mặt
- **Bảo mật thông tin** - ẩn thông tin nhạy cảm
- **Tính toán giá tự động**: Phòng + giường + dịch vụ ăn uống

### 📊 Dashboard & Báo cáo
- **Thống kê đặt phòng** real-time
- **Quản lý trạng thái** xác nhận/hủy đơn
- **Export dữ liệu** Excel/PDF
- **Biểu đồ thống kê** theo thời gian

## 🛠️ Cài đặt

### Yêu cầu hệ thống
- PHP 8.0+
- MySQL 8.0+
- Apache/Nginx

### Bước 1: Clone repository
```bash
git clone https://github.com/your-username/bluebird-hotel.git
cd bluebird-hotel
```

### Bước 2: Cấu hình database
1. Tạo database MySQL mới
2. Import file `bluebirdhotel.sql`
3. Cập nhật thông tin database trong `config.php`

```php
// config.php
define('DB_HOST', 'localhost');
define('DB_NAME', 'bluebird_hotel');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

### Bước 3: Cấu hình VietQR (tùy chọn)
Cập nhật thông tin VietQR trong `payment.php`:
```php
$secureData = [
    'bank' => 'Your Bank Name',
    'account' => 'Your Account Number',
    'holder' => 'Your Account Holder',
    'qrUrl' => 'Your VietQR URL'
];
```

### Bước 4: Truy cập ứng dụng
- **Frontend**: `http://localhost/bluebird-hotel/`
- **Admin**: `http://localhost/bluebird-hotel/admin/`

## 📁 Cấu trúc dự án

```
bluebird-hotel/
├── 📁 admin/                    # Admin panel
│   ├── 📁 css/                  # Admin stylesheets
│   ├── 📁 javascript/           # Admin scripts
│   ├── 📄 admin.php            # Main admin page
│   ├── 📄 dashboard.php        # Admin dashboard
│   ├── 📄 roombook.php         # Booking management
│   ├── 📄 payment.php          # Payment management
│   ├── 📄 room.php             # Room management
│   └── 📄 staff.php            # Staff management
├── 📁 css/                      # Frontend stylesheets
├── 📁 image/                    # Images and assets
├── 📁 javascript/               # Frontend scripts
├── 📄 index.php                # Homepage
├── 📄 login.php                # Login page
├── 📄 register.php             # Register page
├── 📄 dashboard.php            # User dashboard
├── 📄 booking.php              # Booking page
├── 📄 payment.php              # Payment page
├── 📄 room-gallery.php         # Room gallery
├── 📄 my-bookings.php          # Booking history
├── 📄 profile.php              # User profile
├── 📄 config.php               # Database configuration
├── 📄 auth.php                 # Authentication functions
├── 📄 middleware.php           # Middleware functions
├── 📄 bluebirdhotel.sql        # Database schema
└── 📄 README.md                # This file
```

## 🎮 Sử dụng

### 👤 Khách hàng
1. **Đăng ký/Đăng nhập** tại trang chủ
2. **Xem gallery phòng** để chọn loại phòng phù hợp
3. **Đặt phòng** với thông tin chi tiết
4. **Thanh toán** qua VietQR hoặc phương thức khác
5. **Theo dõi đơn đặt phòng** trong dashboard

### 👨‍💼 Admin
1. **Đăng nhập** vào admin panel
2. **Quản lý đặt phòng** - xác nhận/hủy đơn
3. **Theo dõi thanh toán** và cập nhật trạng thái
4. **Quản lý phòng** và nhân viên
5. **Xem báo cáo** và thống kê

## 🔒 Bảo mật

### Security Features
- ✅ **SQL Injection Prevention** - Sử dụng PDO Prepared Statements
- ✅ **XSS Protection** - Input sanitization
- ✅ **Session Security** - Secure session management
- ✅ **Password Hashing** - Bảo mật mật khẩu
- ✅ **CSRF Protection** - Cross-site request forgery prevention
- ✅ **Input Validation** - Kiểm tra dữ liệu đầu vào

### VietQR Security
- ✅ **Thông tin ẩn** - Không hiển thị trực tiếp trong HTML
- ✅ **Mã hóa dữ liệu** - Thông tin nhạy cảm được mã hóa
- ✅ **Hiển thị có kiểm soát** - Chỉ hiển thị khi cần thiết



---

⭐ Nếu dự án này hữu ích, hãy cho chúng tôi một star!

---

*Made with ❤️ by [Your Name]*
