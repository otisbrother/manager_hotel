-- Tạo bảng payment_requests để lưu thông tin payment request từ PayOS
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
