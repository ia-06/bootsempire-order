<?php
/**
 * One-time installer — creates/upgrades all DB tables.
 * DELETE THIS FILE after running it once.
 */
require_once __DIR__ . '/db.php';
$pdo = getPDO();

$pdo->exec("
CREATE TABLE IF NOT EXISTS `orders` (
  `id`                    VARCHAR(36)  NOT NULL PRIMARY KEY,
  `order_id`              VARCHAR(32)  DEFAULT NULL UNIQUE,
  `created_at`            DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `boot_id`               VARCHAR(64)  NOT NULL DEFAULT 'custom',
  `boot_name`             VARCHAR(128) NOT NULL,
  `name`                  VARCHAR(128) NOT NULL,
  `whatsapp`              VARCHAR(20)  NOT NULL,
  `estimated_delivery`    VARCHAR(64)  NOT NULL DEFAULT '15-20 days',
  `advance_amount`        INT UNSIGNED NOT NULL DEFAULT 2700,
  `status`                ENUM('PENDING','PAYMENT_MARKED','PENDING_ACTION','CONFIRMED','REJECTED','ISSUE_RAISED') NOT NULL DEFAULT 'PENDING_ACTION',
  `confirmed_at`          DATETIME     DEFAULT NULL,
  `remarks`               TEXT         DEFAULT NULL,
  `link_slug`             VARCHAR(6)   DEFAULT NULL,
  `channel`               ENUM('instagram','whatsapp') DEFAULT NULL,
  `pay_mode`              ENUM('advance','full') NOT NULL DEFAULT 'advance',
  `screenshot`            VARCHAR(256) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `order_links` (
  `id`             VARCHAR(16)  NOT NULL PRIMARY KEY,
  `slug`           VARCHAR(6)   NOT NULL UNIQUE,
  `channel`        ENUM('instagram','whatsapp') NOT NULL,
  `qty`            TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `total_price`    INT UNSIGNED NOT NULL DEFAULT 6700,
  `advance_amount` INT UNSIGNED NOT NULL DEFAULT 2700,
  `created_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `config` (
  `id`             TINYINT UNSIGNED NOT NULL PRIMARY KEY DEFAULT 1,
  `upi_id`         VARCHAR(128)  NOT NULL DEFAULT '',
  `qr_image`       VARCHAR(256)  DEFAULT NULL,
  `total_price`    INT UNSIGNED  NOT NULL DEFAULT 6700,
  `advance_amount` INT UNSIGNED  NOT NULL DEFAULT 2700,
  `on_delivery`    INT UNSIGNED  NOT NULL DEFAULT 4000,
  `whatsapp_link`  VARCHAR(512)  NOT NULL DEFAULT '',
  `instagram_link` VARCHAR(512)  NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `config` (`id`) VALUES (1);
");

// Upgrade existing installs safely
$upgrades = [
    // Ensure core columns exist
    "ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `link_slug`    VARCHAR(6)   DEFAULT NULL",
    "ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `channel`      ENUM('instagram','whatsapp') DEFAULT NULL",
    "ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `pay_mode`     ENUM('advance','full') NOT NULL DEFAULT 'advance'",
    "ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `screenshot`   VARCHAR(256) DEFAULT NULL",
    "ALTER TABLE `config`  ADD COLUMN IF NOT EXISTS `whatsapp_link`  VARCHAR(512) NOT NULL DEFAULT ''",
    "ALTER TABLE `config`  ADD COLUMN IF NOT EXISTS `instagram_link` VARCHAR(512) NOT NULL DEFAULT ''",
    // Remove old columns no longer used
    "ALTER TABLE `orders` DROP COLUMN IF EXISTS `sizes_json`",
    "ALTER TABLE `orders` DROP COLUMN IF EXISTS `addons_json`",
    "ALTER TABLE `orders` DROP COLUMN IF EXISTS `images_json`",
    "ALTER TABLE `orders` DROP COLUMN IF EXISTS `utr`",
    "ALTER TABLE `orders` DROP COLUMN IF EXISTS `payment_screenshot`",
    "ALTER TABLE `orders` DROP COLUMN IF EXISTS `addons`",
    "ALTER TABLE `orders` DROP COLUMN IF EXISTS `size`",
    "ALTER TABLE `orders` DROP COLUMN IF EXISTS `boot_image`",
    "ALTER TABLE `order_links` DROP COLUMN IF EXISTS `addons_price`",
    "ALTER TABLE `config` DROP COLUMN IF EXISTS `addons_price`",
    // Ensure status ENUM is up to date
    "ALTER TABLE `orders` MODIFY COLUMN `status` ENUM('PENDING','PAYMENT_MARKED','PENDING_ACTION','CONFIRMED','REJECTED','ISSUE_RAISED') NOT NULL DEFAULT 'PENDING_ACTION'",
];
foreach ($upgrades as $sql) {
    try { $pdo->exec($sql); } catch (Exception $e) { /* safe to ignore */ }
}

echo '<pre style="font-family:monospace;font-size:14px;padding:24px">';
echo "\u2705 Tables created / upgraded successfully.\n";
echo "\uD83D\uDDD1  Delete this file (install.php) from your server now.\n";
echo '</pre>';