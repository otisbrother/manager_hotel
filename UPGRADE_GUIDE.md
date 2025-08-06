# Hướng dẫn nâng cấp hệ thống phân quyền

## Trạng thái hiện tại
Hệ thống hiện tại đang sử dụng cấu trúc database cũ:
- `signup` - Bảng người dùng
- `emp_login` - Bảng nhân viên/admin

## Cách sử dụng hệ thống phân quyền mới

### 1. Đăng nhập hiện tại
- **Khách hàng**: Sử dụng bảng `signup`
- **Nhân viên/Admin**: Sử dụng bảng `emp_login`

### 2. Thông tin đăng nhập mặc định
- **Admin**: 
  - Email: `Admin@gmail.com`
  - Password: `1234`
- **User**: 
  - Email: `tusharpankhaniya2202@gmail.com`
  - Password: `123`

### 3. Quyền hạn hiện tại
- **Admin**: Toàn quyền quản lý hệ thống
- **User**: Chỉ có quyền truy cập cơ bản (đặt phòng, xem lịch sử)

## Nâng cấp lên hệ thống phân quyền đầy đủ

### Bước 1: Chạy script cập nhật database
```sql
-- Chạy file update_database.sql trong phpMyAdmin
```

### Bước 2: Cập nhật code
Sau khi chạy script, hệ thống sẽ tự động chuyển đổi sang cấu trúc mới.

### Bước 3: Tính năng mới
- **5 cấp độ phân quyền**: Super Admin, Admin, Manager, Staff, User
- **30+ quyền hạn chi tiết**: Xem, tạo, sửa, xóa cho từng module
- **Ghi log hoạt động**: Theo dõi mọi thay đổi trong hệ thống
- **Session timeout**: Tự động đăng xuất sau 1 giờ
- **Menu động**: Hiển thị menu dựa trên quyền hạn

## Cấu trúc phân quyền mới

### Super Admin
- Toàn quyền hệ thống
- Quản lý cài đặt hệ thống
- Sao lưu dữ liệu
- Xem log hoạt động

### Admin
- Quản lý toàn bộ khách sạn
- Quản lý nhân viên và người dùng
- Xem báo cáo và xuất dữ liệu

### Manager
- Quản lý hoạt động hàng ngày
- Xác nhận/hủy đặt phòng
- Chỉnh sửa thông tin phòng
- Xem báo cáo

### Staff
- Thực hiện các tác vụ cơ bản
- Xem và chỉnh sửa đặt phòng
- Xem và chỉnh sửa thanh toán

### User
- Đặt phòng
- Xem lịch sử đặt phòng
- Thanh toán

## Lưu ý quan trọng
1. **Backup database** trước khi nâng cấp
2. **Test trên môi trường dev** trước khi áp dụng production
3. **Cập nhật mật khẩu** sau khi nâng cấp để bảo mật hơn

## Hỗ trợ
Nếu gặp vấn đề trong quá trình nâng cấp, vui lòng:
1. Kiểm tra log lỗi
2. Đảm bảo database connection
3. Kiểm tra quyền truy cập database 