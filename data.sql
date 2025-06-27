-- Create database
CREATE DATABASE IF NOT EXISTS stepacashier;
USE stepacashier;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    referral_code VARCHAR(10) NOT NULL UNIQUE,
    is_active BOOLEAN DEFAULT 0,
    is_admin BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Payments table
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    mpesa_code VARCHAR(50),
    status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Referrals table
CREATE TABLE referrals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    referrer_id INT NOT NULL,
    referred_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (referrer_id) REFERENCES users(id),
    FOREIGN KEY (referred_id) REFERENCES users(id),
    UNIQUE KEY unique_referral (referrer_id, referred_id)
);

-- Referral earnings table
CREATE TABLE referral_earnings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    referral_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (referral_id) REFERENCES referrals(id)
);

-- Add to existing users table
ALTER TABLE users ADD COLUMN profile_pic VARCHAR(255);
ALTER TABLE users ADD COLUMN last_login DATETIME;
ALTER TABLE users ADD COLUMN email_verified BOOLEAN DEFAULT 0;
ALTER TABLE users ADD COLUMN phone_verified BOOLEAN DEFAULT 0;

-- Notifications table
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- SMS logs table
CREATE TABLE sms_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recipient VARCHAR(20) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('sent', 'failed') NOT NULL,
    response TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Email logs table
CREATE TABLE email_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recipient VARCHAR(100) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    status ENUM('sent', 'failed') NOT NULL,
    response TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- System settings table
CREATE TABLE system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) NOT NULL UNIQUE,
    setting_value TEXT NOT NULL,
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);




-- Add these to your existing database schema

-- Withdrawals table
CREATE TABLE withdrawals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    status ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
    mpesa_code VARCHAR(50),
    failure_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    processed_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Transfers table
CREATE TABLE transfers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    reference VARCHAR(50) NOT NULL,
    status ENUM('completed', 'failed') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id),
    FOREIGN KEY (receiver_id) REFERENCES users(id)
);

-- Add balance column to users table if not exists
ALTER TABLE users ADD COLUMN IF NOT EXISTS balance DECIMAL(10,2) DEFAULT 0.00;

-- Add withdrawal_limit column
ALTER TABLE users ADD COLUMN IF NOT EXISTS withdrawal_limit DECIMAL(10,2) DEFAULT 50000.00;

-- Add referral_bonus_balance column
ALTER TABLE users ADD COLUMN IF NOT EXISTS referral_bonus_balance DECIMAL(10,2) DEFAULT 0.00;


-- --------------------------------------------------------
-- Table structure for table `chat_conversations`
-- --------------------------------------------------------

CREATE TABLE `chat_conversations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(100) DEFAULT NULL COMMENT 'Optional conversation title',
  `status` enum('active','archived','deleted') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `chat_conversations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Chat conversation threads';

-- --------------------------------------------------------
-- Table structure for table `chat_messages`
-- --------------------------------------------------------

CREATE TABLE `chat_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `conversation_id` int(11) NOT NULL,
  `sender_type` enum('user','ai','admin') NOT NULL COMMENT 'Who sent the message',
  `sender_id` int(11) DEFAULT NULL COMMENT 'ID of sender (user_id or admin_id)',
  `message` text NOT NULL,
  `message_type` enum('text','image','video','document','audio','location') NOT NULL DEFAULT 'text',
  `metadata` text DEFAULT NULL COMMENT 'JSON metadata for special messages',
  `is_read` tinyint(1) DEFAULT 0 COMMENT 'For user messages to AI/admin',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `conversation_id` (`conversation_id`),
  KEY `sender_type_sender_id` (`sender_type`, `sender_id`),
  CONSTRAINT `chat_messages_ibfk_1` FOREIGN KEY (`conversation_id`) REFERENCES `chat_conversations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Individual chat messages';

-- --------------------------------------------------------
-- Table structure for table `ai_chat_context`
-- --------------------------------------------------------

CREATE TABLE `ai_chat_context` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `conversation_id` int(11) NOT NULL,
  `context_data` text NOT NULL COMMENT 'JSON context for AI conversation history',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `conversation_id` (`conversation_id`),
  CONSTRAINT `ai_chat_context_ibfk_1` FOREIGN KEY (`conversation_id`) REFERENCES `chat_conversations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='AI conversation context storage';

-- --------------------------------------------------------
-- Table structure for table `chat_attachments`
-- --------------------------------------------------------

CREATE TABLE `chat_attachments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(50) NOT NULL,
  `file_size` int(11) NOT NULL COMMENT 'Size in bytes',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `message_id` (`message_id`),
  CONSTRAINT `chat_attachments_ibfk_1` FOREIGN KEY (`message_id`) REFERENCES `chat_messages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Chat message attachments';







-- Admin table for Stepacashier system
CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('super_admin','admin','moderator') NOT NULL DEFAULT 'admin',
  `permissions` text DEFAULT NULL COMMENT 'JSON array of permissions',
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` datetime DEFAULT NULL,
  `login_attempts` int(11) DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL COMMENT 'Admin ID who created this admin'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Indexes for admin table
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role` (`role`),
  ADD KEY `is_active` (`is_active`),
  ADD KEY `created_by` (`created_by`);

-- Auto increment
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

-- Foreign key constraint
ALTER TABLE `admins`
  ADD CONSTRAINT `admins_created_by_fk` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

-- Insert default super admin (password: admin123 - change this!)
INSERT INTO `admins` (`name`, `email`, `password`, `role`, `permissions`, `is_active`) VALUES
('Super Admin', 'admin@stepcashier.com', '$2y$10$AQksV.4rxV3i67CMQWvIwOBmZT.CaoYl8evRwZO7uLiMRefl06kce', 'super_admin', '["all"]', 1);

-- Admin activity logs table
CREATE TABLE `admin_logs` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `table_name` varchar(50) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_values` text DEFAULT NULL COMMENT 'JSON of old values',
  `new_values` text DEFAULT NULL COMMENT 'JSON of new values',
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Indexes for admin_logs
ALTER TABLE `admin_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `action` (`action`),
  ADD KEY `table_name` (`table_name`),
  ADD KEY `created_at` (`created_at`);

-- Auto increment for admin_logs
ALTER TABLE `admin_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

-- Foreign key for admin_logs
ALTER TABLE `admin_logs`
  ADD CONSTRAINT `admin_logs_admin_fk` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE;


  -- Admin password reset tokens table
CREATE TABLE `admin_password_resets` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `used_at` datetime DEFAULT NULL COMMENT 'When the token was used'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Indexes for admin_password_resets
ALTER TABLE `admin_password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `expires_at` (`expires_at`),
  ADD KEY `created_at` (`created_at`);

-- Auto increment
ALTER TABLE `admin_password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

-- Foreign key constraint
ALTER TABLE `admin_password_resets`
  ADD CONSTRAINT `admin_password_resets_admin_fk` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE;

-- Create index for cleanup operations
CREATE INDEX `idx_expired_tokens` ON `admin_password_resets` (`expires_at`);

-- Optional: Create a stored procedure for automatic cleanup
DELIMITER //
CREATE PROCEDURE CleanupExpiredAdminResetTokens()
BEGIN
    DELETE FROM admin_password_resets WHERE expires_at < NOW();
END //
DELIMITER ;

-- Optional: Create an event to run cleanup automatically (if event scheduler is enabled)
-- CREATE EVENT IF NOT EXISTS cleanup_admin_reset_tokens
-- ON SCHEDULE EVERY 1 HOUR
-- DO CALL CleanupExpiredAdminResetTokens();

-- Add missing columns to existing withdrawals table
ALTER TABLE `withdrawals` 
ADD COLUMN `transaction_fee` decimal(10,2) DEFAULT 0.00 AFTER `amount`,
ADD COLUMN `conversation_id` varchar(100) DEFAULT NULL AFTER `mpesa_code`,
ADD COLUMN `originator_conversation_id` varchar(100) DEFAULT NULL AFTER `conversation_id`,
ADD COLUMN `result_desc` text DEFAULT NULL AFTER `failure_reason`,
ADD COLUMN `receiver_phone` varchar(20) DEFAULT NULL AFTER `result_desc`,
ADD COLUMN `transaction_completed_at` datetime DEFAULT NULL AFTER `receiver_phone`;

-- Add indexes for better performance
ALTER TABLE `withdrawals`
ADD INDEX `idx_conversation_id` (`conversation_id`),
ADD INDEX `idx_originator_conversation_id` (`originator_conversation_id`),
ADD INDEX `idx_status` (`status`);

CREATE TABLE payment_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX (token),
    INDEX (expires_at)
);



CREATE TABLE `exchange_rates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `base_currency` varchar(3) NOT NULL DEFAULT 'USD',
  `target_currency` varchar(3) NOT NULL,
  `buy_rate` decimal(15,6) NOT NULL,
  `sell_rate` decimal(15,6) NOT NULL,
  `mid_rate` decimal(15,6) NOT NULL,
  `source` varchar(50) DEFAULT 'central_bank' COMMENT 'central_bank, market, manual',
  `is_active` tinyint(1) DEFAULT 1,
  `valid_from` datetime NOT NULL,
  `valid_to` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `currency_pair` (`base_currency`,`target_currency`,`valid_from`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `investment_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `currency` varchar(3) NOT NULL DEFAULT 'USD',
  `min_investment_amount` decimal(15,2) NOT NULL,
  `max_investment_amount` decimal(15,2) DEFAULT NULL,
  `expected_return_rate` decimal(5,2) NOT NULL COMMENT 'Annual percentage rate',
  `return_period_days` int(11) NOT NULL COMMENT 'Investment duration in days',
  `risk_level` enum('low','medium','high') NOT NULL DEFAULT 'medium',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `order_type` enum('buy','sell') NOT NULL,
  `currency_pair` varchar(7) NOT NULL COMMENT 'Format: USD/KES',
  `amount` decimal(15,2) NOT NULL COMMENT 'Amount in base currency',
  `rate` decimal(15,6) NOT NULL,
  `total_amount` decimal(15,2) NOT NULL COMMENT 'Amount in target currency',
  `status` enum('pending','processing','completed','failed','cancelled') NOT NULL DEFAULT 'pending',
  `payment_method` enum('mpesa','bank_transfer','crypto_wallet','card') NOT NULL,
  `payment_reference` varchar(100) DEFAULT NULL,
  `payment_confirmed` tinyint(1) DEFAULT 0,
  `wallet_address` varchar(255) DEFAULT NULL COMMENT 'For crypto transfers',
  `transaction_hash` varchar(255) DEFAULT NULL COMMENT 'For blockchain transactions',
  `platform` enum('crypto','deriv','local') NOT NULL DEFAULT 'local' COMMENT 'Where the funds will be sent',
  `platform_reference` varchar(255) DEFAULT NULL COMMENT 'Reference ID on the target platform',
  `notes` text DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `status` (`status`),
  KEY `order_type` (`order_type`),
  KEY `currency_pair` (`currency_pair`),
  CONSTRAINT `orders_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `investments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `currency` varchar(3) NOT NULL DEFAULT 'USD',
  `expected_return_amount` decimal(15,2) NOT NULL,
  `start_date` date NOT NULL,
  `maturity_date` date NOT NULL,
  `status` enum('active','matured','cancelled','withdrawn') NOT NULL DEFAULT 'active',
  `payout_method` enum('wallet','bank','crypto') NOT NULL DEFAULT 'wallet',
  `payout_reference` varchar(255) DEFAULT NULL COMMENT 'Transaction hash or bank reference',
  `payout_confirmed` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`),
  KEY `status` (`status`),
  CONSTRAINT `investments_product_fk` FOREIGN KEY (`product_id`) REFERENCES `investment_products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `investments_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `platform_accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `platform_type` enum('crypto','deriv','forex') NOT NULL,
  `platform_name` varchar(50) NOT NULL COMMENT 'Binance, Deriv, etc',
  `account_id` varchar(255) NOT NULL COMMENT 'Platform-specific account ID',
  `api_key` varchar(255) DEFAULT NULL COMMENT 'Encrypted API key',
  `api_secret` varchar(255) DEFAULT NULL COMMENT 'Encrypted API secret',
  `is_active` tinyint(1) DEFAULT 1,
  `last_sync` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_platform` (`user_id`,`platform_type`,`platform_name`),
  CONSTRAINT `platform_accounts_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `wallet_balances` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `currency` varchar(3) NOT NULL,
  `available_balance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `locked_balance` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'For pending transactions',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_currency` (`user_id`,`currency`),
  CONSTRAINT `wallet_balances_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;