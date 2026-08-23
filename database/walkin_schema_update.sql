-- ============================================================
--  YP4K Resort — Walk-In Reservation Schema Update
--  Run this ONCE in phpMyAdmin > SQL tab.
--  Database: yp4k_resort_db
-- ============================================================

-- 1. Make user_id nullable so walk-ins don't need a registered account.
--    Also drops the hard FK first, then re-adds it with SET NULL on delete.
ALTER TABLE `bookings`
    DROP FOREIGN KEY `fk_bookings_user`;

ALTER TABLE `bookings`
    MODIFY COLUMN `user_id` INT(11) DEFAULT NULL;

ALTER TABLE `bookings`
    ADD CONSTRAINT `fk_bookings_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE SET NULL;

-- 2. Add booking_source column (online = normal web booking, walk_in = admin-created).
ALTER TABLE `bookings`
    ADD COLUMN `booking_source` ENUM('online', 'walk_in') NOT NULL DEFAULT 'online'
    AFTER `payment_method`;

-- 3. Add walk-in guest fields (NULL for online bookings that already have full_name/phone).
ALTER TABLE `bookings`
    ADD COLUMN `guest_name`    VARCHAR(255) DEFAULT NULL AFTER `booking_source`,
    ADD COLUMN `guest_contact` VARCHAR(30)  DEFAULT NULL AFTER `guest_name`;

-- 4. Add index on booking_source for quick dashboard filtering.
ALTER TABLE `bookings`
    ADD INDEX `idx_booking_source` (`booking_source`);

-- 5. Insert a 'checked_in' status so walk-ins can bypass the pending_review flow.
--    IF it already exists, the INSERT IGNORE silently skips it.
INSERT IGNORE INTO `booking_status` (`name`, `description`, `color`, `is_active`)
VALUES ('checked_in', 'Guest has checked in (walk-in reservation)', '#6366f1', 1);

-- Done. Verify with:
-- DESCRIBE bookings;
-- SELECT * FROM booking_status;
