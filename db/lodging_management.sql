-- Lodging Management System database schema and demo data
-- Import this file into MySQL or MariaDB.

CREATE DATABASE IF NOT EXISTS `lodging_management`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_general_ci;

USE `lodging_management`;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `bookings`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `tenants`;
DROP TABLE IF EXISTS `rooms`;

CREATE TABLE `users` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `full_name` VARCHAR(120) NOT NULL,
    `email` VARCHAR(120) NOT NULL,
    `role` VARCHAR(20) NOT NULL DEFAULT 'customer',
    `tenant_id` INT(11) UNSIGNED NULL DEFAULT NULL,
    `password_hash` TEXT NOT NULL,
    `created_at` DATETIME NULL DEFAULT NULL,
    `updated_at` DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `users_email_unique` (`email`),
    UNIQUE KEY `users_tenant_id_unique` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `rooms` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `room_number` VARCHAR(20) NOT NULL,
    `type` VARCHAR(30) NOT NULL,
    `capacity` INT(11) NOT NULL,
    `price_per_night` DECIMAL(10,2) NOT NULL,
    `status` VARCHAR(20) NOT NULL DEFAULT 'available',
    `description` TEXT NULL DEFAULT NULL,
    `created_at` DATETIME NULL DEFAULT NULL,
    `updated_at` DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `rooms_room_number_unique` (`room_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `tenants` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `full_name` VARCHAR(120) NOT NULL,
    `email` VARCHAR(120) NULL DEFAULT NULL,
    `phone` VARCHAR(30) NOT NULL,
    `id_type` VARCHAR(50) NULL DEFAULT NULL,
    `id_number` VARCHAR(50) NULL DEFAULT NULL,
    `address` VARCHAR(255) NULL DEFAULT NULL,
    `emergency_contact_name` VARCHAR(120) NULL DEFAULT NULL,
    `emergency_contact_phone` VARCHAR(30) NULL DEFAULT NULL,
    `created_at` DATETIME NULL DEFAULT NULL,
    `updated_at` DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `bookings` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `room_id` INT(11) UNSIGNED NOT NULL,
    `tenant_id` INT(11) UNSIGNED NOT NULL,
    `check_in` DATE NOT NULL,
    `check_out` DATE NOT NULL,
    `total_amount` DECIMAL(10,2) NOT NULL,
    `status` VARCHAR(20) NOT NULL DEFAULT 'pending',
    `notes` TEXT NULL DEFAULT NULL,
    `created_at` DATETIME NULL DEFAULT NULL,
    `updated_at` DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `bookings_room_id_tenant_id_index` (`room_id`, `tenant_id`),
    CONSTRAINT `bookings_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `bookings_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `users`
    ADD CONSTRAINT `users_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

INSERT INTO `rooms` (`id`, `room_number`, `type`, `capacity`, `price_per_night`, `status`, `description`, `created_at`, `updated_at`) VALUES
    (1, '101', 'standard', 2, 1800.00, 'available', 'Cozy room with twin beds and garden view.', '2026-04-19 00:00:00', '2026-04-19 00:00:00'),
    (2, '205', 'deluxe', 3, 2800.00, 'occupied', 'Deluxe room with balcony and breakfast inclusion.', '2026-04-19 00:00:00', '2026-04-19 00:00:00'),
    (3, '301', 'suite', 4, 4500.00, 'maintenance', 'Premium suite undergoing scheduled maintenance.', '2026-04-19 00:00:00', '2026-04-19 00:00:00');

INSERT INTO `tenants` (`id`, `full_name`, `email`, `phone`, `id_type`, `id_number`, `address`, `emergency_contact_name`, `emergency_contact_phone`, `created_at`, `updated_at`) VALUES
    (1, 'Maria Santos', 'maria@example.com', '09171234567', 'Passport', 'P1234567', 'Cebu City', 'Luis Santos', '09179876543', '2026-04-19 00:00:00', '2026-04-19 00:00:00'),
    (2, 'John Dela Cruz', 'john@example.com', '09170001111', 'National ID', 'NID-567890', 'Davao City', 'Anna Dela Cruz', '09175550000', '2026-04-19 00:00:00', '2026-04-19 00:00:00');

INSERT INTO `users` (`id`, `full_name`, `email`, `role`, `tenant_id`, `password_hash`, `created_at`, `updated_at`) VALUES
    (1, 'Demo Manager', 'admin@lodging.test', 'admin', NULL, '$2y$10$DFPFFGmAi6jxCh5jY67k/enp1iLyVCx4cnvj6zxQ5fnD/8end9sLC', '2026-04-19 00:00:00', '2026-04-19 00:00:00'),
    (2, 'Maria Santos', 'maria@example.com', 'customer', 1, '$2y$10$DFPFFGmAi6jxCh5jY67k/enp1iLyVCx4cnvj6zxQ5fnD/8end9sLC', '2026-04-19 00:00:00', '2026-04-19 00:00:00');

INSERT INTO `bookings` (`id`, `room_id`, `tenant_id`, `check_in`, `check_out`, `total_amount`, `status`, `notes`, `created_at`, `updated_at`) VALUES
    (1, 1, 1, '2026-04-21', '2026-04-24', 5400.00, 'pending', 'Arrival expected in the afternoon.', '2026-04-19 00:00:00', '2026-04-19 00:00:00'),
    (2, 2, 2, '2026-04-18', '2026-04-22', 11200.00, 'checked_in', 'Extended stay guest.', '2026-04-19 00:00:00', '2026-04-19 00:00:00');

SET FOREIGN_KEY_CHECKS = 1;
