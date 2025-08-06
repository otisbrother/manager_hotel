-- Cập nhật cấu trúc database cho hệ thống phân quyền mới
-- Chạy file này để cập nhật database hiện tại

-- Tạo bảng roles
CREATE TABLE IF NOT EXISTS `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` text,
  `permissions` json DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tạo bảng users (thay thế signup)
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text,
  `role_id` int(11) DEFAULT 4, -- Mặc định là user
  `status` enum('active','inactive','banned') DEFAULT 'active',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `role_id` (`role_id`),
  FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tạo bảng staff (thay thế emp_login)
CREATE TABLE IF NOT EXISTS `staff` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `position` varchar(50) DEFAULT NULL,
  `role_id` int(11) NOT NULL,
  `status` enum('active','inactive','banned') DEFAULT 'active',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `role_id` (`role_id`),
  FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tạo bảng activity_logs để ghi log hoạt động
CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `user_type` enum('user','staff') DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `description` text,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Thêm dữ liệu mặc định cho roles
INSERT INTO `roles` (`name`, `description`, `permissions`) VALUES
('super_admin', 'Quản trị viên cao cấp - Toàn quyền hệ thống', '["dashboard_view","dashboard_edit","room_view","room_create","room_edit","room_delete","booking_view","booking_create","booking_edit","booking_delete","booking_confirm","booking_cancel","payment_view","payment_create","payment_edit","payment_delete","payment_refund","staff_view","staff_create","staff_edit","staff_delete","user_view","user_create","user_edit","user_delete","system_settings","system_backup","system_log","report_view","report_export"]'),
('admin', 'Quản trị viên - Quản lý toàn bộ khách sạn', '["dashboard_view","dashboard_edit","room_view","room_create","room_edit","room_delete","booking_view","booking_create","booking_edit","booking_delete","booking_confirm","booking_cancel","payment_view","payment_create","payment_edit","payment_delete","payment_refund","staff_view","staff_create","staff_edit","staff_delete","user_view","user_create","user_edit","user_delete","report_view","report_export"]'),
('manager', 'Quản lý - Quản lý hoạt động hàng ngày', '["dashboard_view","room_view","room_edit","booking_view","booking_edit","booking_confirm","booking_cancel","payment_view","payment_edit","staff_view","staff_edit","report_view"]'),
('staff', 'Nhân viên - Thực hiện các tác vụ cơ bản', '["dashboard_view","room_view","booking_view","booking_edit","payment_view","payment_edit"]'),
('user', 'Khách hàng - Truy cập cơ bản', '["user_access"]');

-- Chuyển đổi dữ liệu từ bảng cũ sang bảng mới
-- Chuyển dữ liệu từ signup sang users
INSERT INTO `users` (`name`, `email`, `password`, `role_id`, `status`)
SELECT `Username`, `Email`, `Password`, 5, 'active' FROM `signup`;

-- Chuyển dữ liệu từ emp_login sang staff
INSERT INTO `staff` (`name`, `email`, `password`, `role_id`, `status`)
SELECT 'Admin', `Emp_Email`, `Emp_Password`, 1, 'active' FROM `emp_login`;

-- Cập nhật mật khẩu thành hash (mật khẩu mặc định: 1234)
UPDATE `users` SET `password` = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE `password` = '123';
UPDATE `staff` SET `password` = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE `password` = '1234';

-- Thêm các cột mới vào bảng roombook
ALTER TABLE `roombook` 
ADD COLUMN `user_id` int(11) DEFAULT NULL AFTER `id`,
ADD COLUMN `staff_id` int(11) DEFAULT NULL AFTER `user_id`,
ADD COLUMN `status` enum('pending','confirmed','cancelled','completed') DEFAULT 'pending' AFTER `staff_id`,
ADD COLUMN `created_at` timestamp DEFAULT CURRENT_TIMESTAMP AFTER `status`,
ADD COLUMN `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`,
ADD KEY `user_id` (`user_id`),
ADD KEY `staff_id` (`staff_id`),
ADD KEY `status` (`status`);

-- Thêm các cột mới vào bảng payment
ALTER TABLE `payment` 
ADD COLUMN `user_id` int(11) DEFAULT NULL AFTER `id`,
ADD COLUMN `staff_id` int(11) DEFAULT NULL AFTER `user_id`,
ADD COLUMN `booking_id` int(11) DEFAULT NULL AFTER `staff_id`,
ADD COLUMN `payment_method` varchar(50) DEFAULT 'cash' AFTER `booking_id`,
ADD COLUMN `payment_status` enum('pending','paid','failed','refunded') DEFAULT 'pending' AFTER `payment_method`,
ADD COLUMN `created_at` timestamp DEFAULT CURRENT_TIMESTAMP AFTER `payment_status`,
ADD COLUMN `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`,
ADD KEY `user_id` (`user_id`),
ADD KEY `staff_id` (`staff_id`),
ADD KEY `booking_id` (`booking_id`),
ADD KEY `payment_status` (`payment_status`);

-- Tạo bảng settings cho cài đặt hệ thống
CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(100) NOT NULL,
  `value` text,
  `description` text,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Thêm cài đặt mặc định
INSERT INTO `settings` (`key`, `value`, `description`) VALUES
('hotel_name', 'BlueBird Hotel', 'Tên khách sạn'),
('hotel_address', '123 Main Street, City', 'Địa chỉ khách sạn'),
('hotel_phone', '+84 123 456 789', 'Số điện thoại khách sạn'),
('hotel_email', 'info@bluebirdhotel.com', 'Email khách sạn'),
('session_timeout', '3600', 'Thời gian timeout session (giây)'),
('max_login_attempts', '5', 'Số lần đăng nhập tối đa'),
('maintenance_mode', '0', 'Chế độ bảo trì (0: tắt, 1: bật)');

-- Tạo index cho hiệu suất
CREATE INDEX idx_roombook_user_id ON roombook(user_id);
CREATE INDEX idx_roombook_staff_id ON roombook(staff_id);
CREATE INDEX idx_roombook_status ON roombook(status);
CREATE INDEX idx_payment_user_id ON payment(user_id);
CREATE INDEX idx_payment_staff_id ON payment(staff_id);
CREATE INDEX idx_payment_status ON payment(payment_status);
CREATE INDEX idx_activity_logs_user_id ON activity_logs(user_id);
CREATE INDEX idx_activity_logs_created_at ON activity_logs(created_at);

-- Thêm trigger để ghi log khi có thay đổi
DELIMITER $$

CREATE TRIGGER log_user_changes AFTER UPDATE ON users
FOR EACH ROW
BEGIN
    INSERT INTO activity_logs (user_id, user_type, action, description)
    VALUES (NEW.id, 'user', 'update', CONCAT('User updated: ', NEW.name));
END$$

CREATE TRIGGER log_staff_changes AFTER UPDATE ON staff
FOR EACH ROW
BEGIN
    INSERT INTO activity_logs (user_id, user_type, action, description)
    VALUES (NEW.id, 'staff', 'update', CONCAT('Staff updated: ', NEW.name));
END$$

DELIMITER ; 