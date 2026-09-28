<?php
/**
 * YP4K Resort - Blocked Email Helper
 * Creates the blocked_emails table and checks whether an address is blocked.
 */

function ensure_blocked_emails_table(?mysqli $conn): bool {
    if (!$conn) {
        return false;
    }

    $sql = "CREATE TABLE IF NOT EXISTS `blocked_emails` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `email` VARCHAR(255) NOT NULL,
        `user_id` INT DEFAULT NULL,
        `reason` VARCHAR(255) DEFAULT NULL,
        `blocked_by` INT DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uniq_blocked_email` (`email`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $created = (bool) $conn->query($sql);
    $conn->query("ALTER TABLE `blocked_emails` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

    return $created;
}

function normalize_blocked_email(mixed $email): string {
    return strtolower(trim((string) $email));
}

function is_email_blocked(?mysqli $conn, mixed $email): bool {
    if (!$conn) {
        return false;
    }

    ensure_blocked_emails_table($conn);

    $email = normalize_blocked_email($email);
    if ($email === '') {
        return false;
    }

    $stmt = $conn->prepare("SELECT id FROM blocked_emails WHERE email = ? LIMIT 1");
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $blocked = $result && $result->num_rows > 0;
    $stmt->close();

    return $blocked;
}

function blocked_email_message($context = 'login') {
    switch ($context) {
        case 'register':
            return 'This email address has been blocked and cannot be used to register.';
        case 'forgot':
            return 'This email address has been blocked. Password reset is not allowed.';
        case 'login':
        default:
            return 'This email address has been blocked and cannot log in.';
    }
}
