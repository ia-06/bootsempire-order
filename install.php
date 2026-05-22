<?php
/**
 * One-time installer — creates/upgrades all DB tables.
 * DELETE THIS FILE after running it once.
 */
require_once __DIR__ . '/db.php';
$pdo = getPDO();

// 1. Create orders table
$pdo->exec("
CREATE TABLE IF NOT EXISTS `orders` (
  `id`                    VARCHAR(36)  NOT NULL PRIMARY KEY,
  `order_id`              VARCHAR(32)  DEFAULT NULL UNIQUE,
  `created_at`            DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `name`                  VARCHAR(128) NOT NULL,
  `whatsapp`              VARCHAR(20)  NOT NULL,
  `advance_amount`        INT UNSIGNED NOT NULL DEFAULT 2700,
  `status`                ENUM('PENDING','PAYMENT_MARKED','PENDING_ACTION','CONFIRMED','REJECTED','ISSUE_RAISED') NOT NULL DEFAULT 'PENDING_ACTION',
  `confirmed_at`          DATETIME     DEFAULT NULL,
  `remarks`               TEXT         DEFAULT NULL,
  `link_slug`             VARCHAR(6)   DEFAULT NULL,
  `channel`               ENUM('instagram','whatsapp') DEFAULT NULL,
  `pay_mode`              ENUM('advance','full') NOT NULL DEFAULT 'advance',
  `screenshot`            VARCHAR(256) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

// 2. Create order_links table
$pdo->exec("
CREATE TABLE IF NOT EXISTS `order_links` (
  `id`             VARCHAR(16)  NOT NULL PRIMARY KEY,
  `slug`           VARCHAR(6)   NOT NULL UNIQUE,
  `channel`        ENUM('instagram','whatsapp') NOT NULL,
  `qty`            TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `total_price`    INT UNSIGNED NOT NULL DEFAULT 6700,
  `advance_amount` INT UNSIGNED NOT NULL DEFAULT 2700,
  `addons_price`   INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

// 3. Create config table
$pdo->exec("
CREATE TABLE IF NOT EXISTS `config` (
  `id`             VARCHAR(20) NOT NULL PRIMARY KEY,
  `upi_id`         VARCHAR(128)  NOT NULL DEFAULT '',
  `qr_image`       VARCHAR(256)  DEFAULT NULL,
  `total_price`    INT UNSIGNED  NOT NULL DEFAULT 6700,
  `advance_amount` INT UNSIGNED  NOT NULL DEFAULT 2700,
  `addons_price`   INT UNSIGNED NOT NULL DEFAULT 0,
  `whatsapp_link`  VARCHAR(512)  NOT NULL DEFAULT '',
  `instagram_link` VARCHAR(512)  NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

// 4. NEW: Create dynamic admins table for employees
$pdo->exec("
CREATE TABLE IF NOT EXISTS `admins` (
  `id` VARCHAR(50) NOT NULL PRIMARY KEY,
  `password` VARCHAR(255) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

// Seed default configurations and a starting employee account if empty
$pdo->exec("INSERT IGNORE INTO `config` (`id`) VALUES ('instagram');");
$pdo->exec("INSERT IGNORE INTO `config` (`id`) VALUES ('whatsapp');");
$pdo->exec("INSERT IGNORE INTO `admins` (`id`, `password`) VALUES ('employee1', 'password123');");

echo '<pre style="font-family:monospace;font-size:14px;padding:24px">';
echo "✅ Tables created / upgraded successfully.\n";
echo "🗑️ Delete this file (install.php) from your server now.\n";
echo '</pre>';