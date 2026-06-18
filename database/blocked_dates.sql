-- Blocked dates for admin calendar (resort-wide closures)
-- Required: no existing table covered date-level blocking in yp4k_resort_db.sql

CREATE TABLE IF NOT EXISTS `blocked_dates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `blocked_date` date NOT NULL,
  `reason` text DEFAULT NULL,
  `blocked_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_blocked_date` (`blocked_date`),
  KEY `idx_blocked_date` (`blocked_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
