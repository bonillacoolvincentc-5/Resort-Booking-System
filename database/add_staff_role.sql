-- ============================================================
--  YP4K Resort — Staff Role Support
--  Paste and run this in phpMyAdmin or mysql CLI.
--  Adds a 'role' column to the admins table so staff accounts
--  can be differentiated from full administrators.
-- ============================================================

-- Step 1: Add role column to admins table (if not already present)
ALTER TABLE `admins`
    ADD COLUMN IF NOT EXISTS `role` ENUM('admin','staff') NOT NULL DEFAULT 'admin';

-- Step 2 (optional): Create a sample staff account
-- Password hash below = bcrypt of "staff1234" — change after first login.
-- INSERT INTO `admins` (first_name, last_name, email, password, role)
-- VALUES ('Staff', 'Member', 'staff@yp4kresort.com',
--         '$2y$10$exampleHashReplaceWithRealBcryptHash', 'staff');
