-- Database creation script for Love Animation Website
CREATE DATABASE IF NOT EXISTS `love_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `love_db`;

-- Responses Table
CREATE TABLE IF NOT EXISTS `responses` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `choice` ENUM('yes', 'no') NOT NULL,
    `message` TEXT NULL,
    `visitor_ip` VARCHAR(45) NULL,
    `user_agent` TEXT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
