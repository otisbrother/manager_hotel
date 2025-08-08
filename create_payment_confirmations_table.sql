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

-- Thêm cột mới vào bảng roombook để hỗ trợ trạng thái "Pending Payment"
-- Chạy lệnh này nếu chưa có cột stat trong bảng roombook
-- ALTER TABLE roombook ADD COLUMN IF NOT EXISTS stat VARCHAR(50) DEFAULT 'NotConfirm';
